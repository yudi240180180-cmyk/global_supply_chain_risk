<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Jin-Woo (Samsung)',
                'email' => 'manager.samsung@gscr.io',
                'company' => 'Samsung Electronics Co., Ltd.',
                'avatar' => '📱',
            ],
            [
                'name' => 'Terry (Foxconn)',
                'email' => 'manager.foxconn@gscr.io',
                'company' => 'Foxconn Technology Group',
                'avatar' => '🏭',
            ],
            [
                'name' => 'C.C. Wei (TSMC)',
                'email' => 'manager.tsmc@gscr.io',
                'company' => 'TSMC Limited',
                'avatar' => '⚡',
            ],
            [
                'name' => 'Kawai (Tokyo Electron)',
                'email' => 'manager.tel@gscr.io',
                'company' => 'Tokyo Electron Ltd.',
                'avatar' => '🗼',
            ],
            [
                'name' => 'DHL Logistics Team',
                'email' => 'manager.dhl@gscr.io',
                'company' => 'DHL Global Forwarding',
                'avatar' => '📦',
            ],
        ];

        foreach ($companies as $c) {
            User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name'         => $c['name'],
                    'email'        => $c['email'],
                    'password'     => Hash::make('Manager@1234'),
                    'role'         => 'import_manager',
                    'company_name' => $c['company'],
                    'avatar_icon'  => $c['avatar'],
                ]
            );
        }
    }
}
