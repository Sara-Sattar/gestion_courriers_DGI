<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courrier_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee');
            $table->foreignId('unite_id')
                ->constrained('unites')
                ->cascadeOnDelete();
            $table->unsignedInteger('dernier_numero')->default(0);
            $table->timestamps();

            $table->unique(['annee', 'unite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_sequences');
    }
};
