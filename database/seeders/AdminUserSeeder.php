<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gscr.io'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@gscr.io',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('Admin user seeded: admin@gscr.io / Admin@1234');
    }
}
