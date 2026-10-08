<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'objet du courrier devient facultatif à l'enregistrement.
     */
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('objet')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('courriers')->whereNull('objet')->update(['objet' => '']);

        Schema::table('courriers', function (Blueprint $table) {
            $table->string('objet')->nullable(false)->change();
        });
    }
};
