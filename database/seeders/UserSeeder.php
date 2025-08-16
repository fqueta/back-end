<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Fernando Queta',
                'email' => 'fernando@maisaqui.com.br',
                'password' => Hash::make('ferqueta'),
                'status' => 'actived',
                'verificado' => 'n',
                'permission_id' => 1, // Grupo Master
            ],
            [
                'name' => 'Test User',
                'email' => 'ger.maisaqui1@gmail.com',
                'password' => Hash::make('mudar123'),
                'status' => 'actived',
                'verificado' => 'n',
                'permission_id' => 2, // Grupo Administrador
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], // evita duplicados
                $userData
            );
        }
    }
}
