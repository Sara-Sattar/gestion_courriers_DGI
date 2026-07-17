<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contribuables', function (Blueprint $table) {
            $table->id();
            $table->string('nom_raison_sociale');
            $table->string('ifu', 20)->nullable()->unique();
            $table->string('cin', 20)->nullable()->index();
            $table->string('ice', 20)->nullable()->index();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->index('nom_raison_sociale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribuables');
    }
};
