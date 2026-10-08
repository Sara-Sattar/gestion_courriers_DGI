<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('courriers', 'reference_envoi')) {
            Schema::table('courriers', function (Blueprint $table) {
                $table->string('reference_envoi', 100)->nullable()->after('postal_nom');
            });
        }

        // Compteur de départ unique par année (format AAAA-NNN-CodeUnité).
        $parAnnee = Schema::hasTable('depart_sequences')
            ? DB::table('depart_sequences')
                ->select('annee', DB::raw('MAX(dernier_numero) as dernier_numero'))
                ->groupBy('annee')
                ->get()
            : collect();

        Schema::dropIfExists('depart_sequences');

        Schema::create('depart_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee')->unique();
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();
        });

        foreach ($parAnnee as $ligne) {
            DB::table('depart_sequences')->insert([
                'annee' => $ligne->annee,
                'dernier_numero' => $ligne->dernier_numero,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('courriers', 'reference_envoi')) {
            Schema::table('courriers', function (Blueprint $table) {
                $table->dropColumn('reference_envoi');
            });
        }

        Schema::dropIfExists('depart_sequences');

        Schema::create('depart_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee');
            $table->foreignId('unite_id')->constrained('unites')->cascadeOnDelete();
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();
            $table->unique(['annee', 'unite_id']);
        });
    }
};
