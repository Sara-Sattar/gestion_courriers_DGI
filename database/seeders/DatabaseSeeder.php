<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UniteSeeder::class,
            CategorieCourrierSeeder::class,
            UserSeeder::class,
            ContribuableSeeder::class,
            CourrierSeeder::class,
        ]);
    }
}
