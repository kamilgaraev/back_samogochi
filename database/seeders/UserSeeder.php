<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];
        $userCredentials = [];
        
        for ($i = 0; $i < 10; $i++) {
            $email = fake()->unique()->safeEmail();
            $password = 'password123';
            
            $users[] = [
                'name' => fake('ru_RU')->name(),
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $userCredentials[] = $email . ':' . $password;
        }
        
        User::insert($users);
        
        $this->command->info('Создано 10 пользователей с подтвержденной почтой');
        $this->command->line('');
        $this->command->info('Список пользователей:');
        foreach ($userCredentials as $credentials) {
            $this->command->line($credentials);
        }
    }
}
