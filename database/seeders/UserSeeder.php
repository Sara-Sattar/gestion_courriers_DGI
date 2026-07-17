<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin' => Role::where('name', 'Admin')->first(),
            'Direction Régionale' => Role::where('name', 'Direction Régionale')->first(),
            'Service' => Role::where('name', 'Service')->first(),
            'Brigade' => Role::where('name', 'Brigade')->first(),
            'Bureau' => Role::where('name', 'Bureau')->first(),
            'Section' => Role::where('name', 'Section')->first(),
            'Secteur' => Role::where('name', 'Secteur')->first(),
        ];

        $unites = Unite::all()->keyBy('code');

        $users = [
            [
                'name' => 'Admin System',
                'email' => 'admin@dgi.ma',
                'role' => 'Admin',
                'unite_code' => 'DR001',
                'password' => 'password',
            ],
            [
                'name' => 'Directeur Régional',
                'email' => 'dr@dgi.ma',
                'role' => 'Direction Régionale',
                'unite_code' => 'DR001',
                'password' => 'password',
            ],
            [
                'name' => 'Chef de Service IT',
                'email' => 'service.it@dgi.ma',
                'role' => 'Service',
                'unite_code' => 'SERV002',
                'password' => 'password',
            ],
            [
                'name' => 'Chef de Brigade',
                'email' => 'brigade@dgi.ma',
                'role' => 'Brigade',
                'unite_code' => 'BRIG001',
                'password' => 'password',
            ],
            [
                'name' => 'Chef de Bureau',
                'email' => 'bureau@dgi.ma',
                'role' => 'Bureau',
                'unite_code' => 'BO001',
                'password' => 'password',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                    'role_id' => $roles[$userData['role']]?->id,
                    'unite_id' => $unites[$userData['unite_code']]?->id,
                ]
            );
        }
    }
}
