<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@catbox.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Crear usuario normal de prueba
        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@catbox.com',
            'password' => Hash::make('usuario123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Crear más usuarios de prueba
        User::factory()->count(5)->create();
    }
}