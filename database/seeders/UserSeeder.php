<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return; // Never create known demo credentials in production.
        }
        foreach ([
            ['name' => 'Guru TKJ', 'email' => 'guru@tkj.com', 'role' => 'guru'],
            ['name' => 'Siswa TKJ', 'email' => 'siswa@tkj.com', 'role' => 'siswa'],
            ['name' => 'fikri', 'email' => 'fikri@gmail.com', 'role' => 'siswa'],
        ] as $account) {
            User::firstOrCreate(['email' => $account['email']],
                $account + ['password' => bcrypt('password')]);
        }
    }
}
