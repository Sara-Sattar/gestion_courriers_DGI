<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courrier_impots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')
                ->constrained('courriers')
                ->cascadeOnDelete();
            $table->string('impot', 10);
            $table->timestamps();

            $table->unique(['courrier_id', 'impot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_impots');
    }
};
