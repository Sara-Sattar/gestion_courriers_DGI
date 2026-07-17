<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')
                ->constrained('courriers')
                ->cascadeOnDelete();
            $table->string('nom_original');
            $table->string('nom_stockage');
            $table->string('chemin');
            $table->string('extension', 10);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('taille');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_jointes');
    }
};
