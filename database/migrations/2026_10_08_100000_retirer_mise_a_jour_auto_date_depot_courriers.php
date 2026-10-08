<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL / MariaDB ajoutent implicitement « ON UPDATE CURRENT_TIMESTAMP » à la
     * première colonne TIMESTAMP NOT NULL : date_depot était alors écrasée (avec le
     * fuseau du serveur) à chaque modification du courrier (réception, traitement…).
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE courriers MODIFY date_depot TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE courriers MODIFY date_depot TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
