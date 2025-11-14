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
        // Создаем админа
        User::create([
            'name' => 'Admin',
            'email' => 'admin@app.me',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        // Создаем обычного пользователя
        User::create([
            'name' => 'User',
            'email' => 'user@app.me',
            'password' => Hash::make('user'),
            'role' => 'user',
        ]);
    }
}
