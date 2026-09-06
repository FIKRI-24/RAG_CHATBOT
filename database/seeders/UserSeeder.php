<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Guru TKJ',
            'email' => 'guru@tkj.com',
            'password' => bcrypt('password'),
            'role' => 'guru'
        ]);

        \App\Models\User::create([
            'name' => 'Siswa TKJ',
            'email' => 'siswa@tkj.com',
            'password' => bcrypt('password'),
            'role' => 'siswa'
        ]);

        \App\Models\User::create([
            'name' => 'fikri',
            'email' => 'fikri@gmail.com',
            'password' => bcrypt ('password'),
            'role' => 'siswa'
        ]);
        
    }
}
