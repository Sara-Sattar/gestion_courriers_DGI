<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_absent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_remplacant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unite_id')->constrained('unites')->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('motif');
            $table->string('statut')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agent_remplacant_id', 'statut']);
            $table->index(['agent_absent_id', 'statut']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegations');
    }
};
