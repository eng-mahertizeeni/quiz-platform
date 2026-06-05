<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@quizplatform.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $users = [
            ['name' => 'أحمد العمري', 'username' => 'ahmed_omari', 'email' => 'ahmed@example.com'],
            ['name' => 'سارة الشمري', 'username' => 'sara_shamri', 'email' => 'sara@example.com'],
            ['name' => 'محمد الغامدي', 'username' => 'mohammed_g', 'email' => 'mohammed@example.com'],
            ['name' => 'فاطمة العتيبي', 'username' => 'fatima_o', 'email' => 'fatima@example.com'],
            ['name' => 'خالد الزهراني', 'username' => 'khalid_z', 'email' => 'khalid@example.com'],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['username' => $userData['username']],
                [
                    ...$userData,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'games_played' => rand(0, 20),
                    'games_won' => rand(0, 10),
                    'total_score' => rand(0, 50000),
                ]
            );
        }
    }
}