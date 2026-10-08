<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * « Nécessitant une réponse » est désormais porté par la catégorie :
     * le courrier hérite de la valeur de sa catégorie à l'enregistrement.
     */
    public function up(): void
    {
        Schema::table('categories_courrier', function (Blueprint $table) {
            $table->boolean('necessite_reponse')->default(true)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('categories_courrier', function (Blueprint $table) {
            $table->dropColumn('necessite_reponse');
        });
    }
};
