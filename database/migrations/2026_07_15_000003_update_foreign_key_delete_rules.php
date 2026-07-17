<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_courrier', function (Blueprint $table) {
            $table->dropForeign(['categorie_courrier_id']);
            $table->foreign('categorie_courrier_id')
                ->references('id')
                ->on('categories_courrier')
                ->restrictOnDelete();
        });

        Schema::table('courrier_sequences', function (Blueprint $table) {
            $table->dropForeign(['unite_id']);
            $table->foreign('unite_id')
                ->references('id')
                ->on('unites')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('types_courrier', function (Blueprint $table) {
            $table->dropForeign(['categorie_courrier_id']);
            $table->foreign('categorie_courrier_id')
                ->references('id')
                ->on('categories_courrier')
                ->cascadeOnDelete();
        });

        Schema::table('courrier_sequences', function (Blueprint $table) {
            $table->dropForeign(['unite_id']);
            $table->foreign('unite_id')
                ->references('id')
                ->on('unites')
                ->cascadeOnDelete();
        });
    }
};

