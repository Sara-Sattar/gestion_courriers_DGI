<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courrier_annees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')
                ->constrained('courriers')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->timestamps();

            $table->unique(['courrier_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_annees');
    }
};
