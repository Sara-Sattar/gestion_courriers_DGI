<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contribuables', function (Blueprint $table) {
            $table->index('numero_tp');
            $table->index('numero_th');
        });
    }

    public function down(): void
    {
        Schema::table('contribuables', function (Blueprint $table) {
            $table->dropIndex(['numero_tp']);
            $table->dropIndex(['numero_th']);
        });
    }
};

