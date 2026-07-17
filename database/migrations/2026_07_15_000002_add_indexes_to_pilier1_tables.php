<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courrier_impots', function (Blueprint $table) {
            $table->index('courrier_id');
        });

        Schema::table('courrier_annees', function (Blueprint $table) {
            $table->index('courrier_id');
        });

        Schema::table('pieces_jointes', function (Blueprint $table) {
            $table->index(['courrier_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::table('courrier_impots', function (Blueprint $table) {
            $table->dropIndex(['courrier_id']);
        });

        Schema::table('courrier_annees', function (Blueprint $table) {
            $table->dropIndex(['courrier_id']);
        });

        Schema::table('pieces_jointes', function (Blueprint $table) {
            $table->dropIndex(['courrier_id', 'ordre']);
        });
    }
};

