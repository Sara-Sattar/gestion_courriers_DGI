<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courrier_historiques', function (Blueprint $table) {
            $table->foreignId('pour_compte_de_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('historique_affectations', function (Blueprint $table) {
            $table->foreignId('pour_compte_de_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courrier_historiques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pour_compte_de_id');
        });

        Schema::table('historique_affectations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pour_compte_de_id');
        });
    }
};
