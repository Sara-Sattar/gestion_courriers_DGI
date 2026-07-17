<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contribuables', function (Blueprint $table) {
            $table->string('numero_tp', 30)->nullable()->after('ice');
            $table->string('numero_th', 30)->nullable()->after('numero_tp');
        });
    }

    public function down(): void
    {
        Schema::table('contribuables', function (Blueprint $table) {
            $table->dropColumn(['numero_tp', 'numero_th']);
        });
    }
};
