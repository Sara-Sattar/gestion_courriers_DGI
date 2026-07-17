<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_courrier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_courrier_id')
                ->constrained('categories_courrier')
                ->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['categorie_courrier_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_courrier');
    }
};
