<?php

use App\Enums\ActionType;
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
        Schema::table('historique_affectations', function (Blueprint $table) {
            $table->string('action_type')->nullable()->default(ActionType::Reaffectation->value)->after('commentaire');
            $table->text('motif')->nullable()->after('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_affectations', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'motif']);
        });
    }
};
