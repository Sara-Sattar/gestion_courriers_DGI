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
            'Accueil' => Role::where('name', 'Accueil')->first(),
            'Traitement' => Role::where('name', 'Traitement')->first(),
            'Consultation' => Role::where('name', 'Consultation')->first(),
            'Service' => Role::where('name', 'Service')->first(),
            'Brigade' => Role::where('name', 'Brigade')->first(),
            'Bureau' => Role::where('name', 'Bureau')->first(),
            'Section' => Role::where('name', 'Section')->first(),
            'Secteur' => Role::where('name', 'Secteur')->first(),
        ];

        $unites = Unite::all()->keyBy('code');

        // Migration de l'ancien compte Directeur → Agent Accueil.
        User::query()
            ->where('email', 'dr@dgi.ma')
            ->update([
                'name' => 'Agent Accueil',
                'email' => 'accueil@dgi.ma',
            ]);

        $users = [
            [
                'name' => 'Admin System',
                'email' => 'admin@dgi.ma',
                'role' => 'Admin',
                'unite_code' => 'DR001',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Accueil',
                'email' => 'accueil@dgi.ma',
                'role' => 'Accueil',
                'unite_code' => 'BO001',
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
            [
                'name' => 'Chef de Secteur Guéliz',
                'email' => 'secteur.gueliz@dgi.ma',
                'role' => 'Secteur',
                'unite_code' => 'SECT001',
                'password' => 'password',
            ],
            [
                'name' => 'Chef Bureau Recouvrement',
                'email' => 'recouvrement@dgi.ma',
                'role' => 'Bureau',
                'unite_code' => 'BUR003',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Bureau 1',
                'email' => 'agent.bureau1@dgi.ma',
                'role' => 'Traitement',
                'unite_code' => 'BO001',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Bureau 2',
                'email' => 'agent.bureau2@dgi.ma',
                'role' => 'Traitement',
                'unite_code' => 'BO001',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Service 1',
                'email' => 'agent.service1@dgi.ma',
                'role' => 'Traitement',
                'unite_code' => 'SERV002',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Brigade 1',
                'email' => 'agent.brigade1@dgi.ma',
                'role' => 'Traitement',
                'unite_code' => 'BRIG001',
                'password' => 'password',
            ],
            [
                'name' => 'Agent Section 1',
                'email' => 'agent.section1@dgi.ma',
                'role' => 'Traitement',
                'unite_code' => 'SERV002',
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
