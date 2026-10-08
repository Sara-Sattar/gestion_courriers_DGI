<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le numéro AAAA-NNNNNN-CODE repose sur l'unité d'enregistrement (code_unite, figé),
     * et non sur l'unité destinataire qui change à chaque réaffectation : l'ancienne
     * contrainte bloquait la réaffectation vers une unité ayant déjà la même séquence.
     */
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->unique(
                ['annee_enregistrement', 'code_unite', 'sequence'],
                'courriers_annee_code_unite_sequence_unique'
            );
        });

        Schema::table('courriers', function (Blueprint $table) {
            $table->dropUnique('courriers_annee_unite_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->unique(
                ['annee_enregistrement', 'unite_destinataire_id', 'sequence'],
                'courriers_annee_unite_sequence_unique'
            );
        });

        Schema::table('courriers', function (Blueprint $table) {
            $table->dropUnique('courriers_annee_code_unite_sequence_unique');
        });
    }
};
