<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('contribuable_id')
                ->constrained('contribuables')
                ->restrictOnDelete();
            $table->foreignId('categorie_courrier_id')
                ->constrained('categories_courrier')
                ->restrictOnDelete();
            $table->foreignId('type_courrier_id')
                ->constrained('types_courrier')
                ->restrictOnDelete();
            $table->string('objet');
            $table->foreignId('unite_destinataire_id')
                ->constrained('unites')
                ->restrictOnDelete();
            $table->string('code_unite', 50);
            $table->unsignedSmallInteger('annee_enregistrement');
            $table->unsignedInteger('sequence');
            $table->text('remarque')->nullable();
            $table->string('statut', 30)->default('depose');
            $table->timestamp('date_depot');
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index(['annee_enregistrement', 'code_unite']);
            $table->index('statut');
            $table->index('date_depot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courriers');
    }
};
