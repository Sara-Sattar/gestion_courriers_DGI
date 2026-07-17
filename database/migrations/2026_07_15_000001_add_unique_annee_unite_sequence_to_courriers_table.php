<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->unique(
                ['annee_enregistrement', 'unite_destinataire_id', 'sequence'],
                'courriers_annee_unite_sequence_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropUnique('courriers_annee_unite_sequence_unique');
        });
    }
};

