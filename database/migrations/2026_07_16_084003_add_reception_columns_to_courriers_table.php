<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('priorite')->default('Moyenne')->after('date_depot');
            $table->date('date_reception')->nullable()->after('priorite');
            $table->time('heure_reception')->nullable()->after('date_reception');
            $table->foreignId('responsable_reception_id')->nullable()->constrained('users')->after('heure_reception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsable_reception_id');
            $table->dropColumn(['priorite', 'date_reception', 'heure_reception']);
        });
    }
};
