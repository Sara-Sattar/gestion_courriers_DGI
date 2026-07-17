<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 50)->unique();
            $table->string('type_unite', 50);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('unites')
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('type_unite');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unites');
    }
};
