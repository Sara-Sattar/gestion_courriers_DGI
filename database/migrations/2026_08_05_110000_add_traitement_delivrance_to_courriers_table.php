<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depart_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee');
            $table->foreignId('unite_id')->constrained('unites')->cascadeOnDelete();
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();

            $table->unique(['annee', 'unite_id']);
        });

        Schema::table('courriers', function (Blueprint $table) {
            $table->date('date_traitement')->nullable()->after('responsable_reception_id');
            $table->foreignId('traite_par_id')
                ->nullable()
                ->after('date_traitement')
                ->constrained('users')
                ->nullOnDelete();
            $table->boolean('document_genere')->nullable()->after('traite_par_id');
            $table->text('motif_rejet')->nullable()->after('document_genere');
            $table->text('motif_suspension')->nullable()->after('motif_rejet');
            $table->foreignId('unite_retour_id')
                ->nullable()
                ->after('motif_suspension')
                ->constrained('unites')
                ->nullOnDelete();

            $table->date('date_delivrance')->nullable()->after('unite_retour_id');
            $table->foreignId('delivre_par_id')
                ->nullable()
                ->after('date_delivrance')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('mode_livraison', 30)->nullable()->after('delivre_par_id');
            $table->string('mandataire_piece', 100)->nullable()->after('mode_livraison');
            $table->string('postal_cin', 50)->nullable()->after('mandataire_piece');
            $table->string('postal_nom', 255)->nullable()->after('postal_cin');
            $table->string('numero_depart', 40)->nullable()->unique()->after('postal_nom');
            $table->unsignedSmallInteger('annee_depart')->nullable()->after('numero_depart');
            $table->unsignedInteger('sequence_depart')->nullable()->after('annee_depart');
            $table->string('code_unite_depart', 50)->nullable()->after('sequence_depart');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('traite_par_id');
            $table->dropConstrainedForeignId('unite_retour_id');
            $table->dropConstrainedForeignId('delivre_par_id');
            $table->dropColumn([
                'date_traitement',
                'document_genere',
                'motif_rejet',
                'motif_suspension',
                'date_delivrance',
                'mode_livraison',
                'mandataire_piece',
                'postal_cin',
                'postal_nom',
                'numero_depart',
                'annee_depart',
                'sequence_depart',
                'code_unite_depart',
            ]);
        });

        Schema::dropIfExists('depart_sequences');
    }
};
