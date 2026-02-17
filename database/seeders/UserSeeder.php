<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- ЭТО ВАЖНО!
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Диспетчер',
                'email' => 'dispatcher@example.com',
                'password' => Hash::make('password'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Иван (мастер)',
                'email' => 'ivan@example.com',
                'password' => Hash::make('password'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Петр (мастер)',
                'email' => 'petr@example.com',
                'password' => Hash::make('password'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
