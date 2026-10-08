<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'role_id']);
        });

        // Compatibilité : chaque utilisateur conserve son rôle principal dans role_id
        // et le retrouve aussi dans le pivot multi-profils CDC.
        $now = now();
        DB::table('users')
            ->whereNotNull('role_id')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($now) {
                $rows = [];
                foreach ($users as $user) {
                    $rows[] = [
                        'user_id' => $user->id,
                        'role_id' => $user->role_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($rows !== []) {
                    DB::table('role_user')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
