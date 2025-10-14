<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\PlayerCustomization;
use App\Models\CustomizationItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userCredentials = [];
        $emails = [
            'user1@example.com',
            'user2@example.com', 
            'user3@example.com',
            'user4@example.com',
            'user5@example.com',
            'user6@example.com',
            'user7@example.com',
            'user8@example.com',
            'user9@example.com',
            'user10@example.com'
        ];
        
        foreach ($emails as $email) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => fake('ru_RU')->name(),
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'is_admin' => false,
                ]
            );
            
            $userCredentials[] = $email . ':password123';
            
            $profile = PlayerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'level' => fake()->numberBetween(1, 10),
                    'total_experience' => fake()->numberBetween(0, 1000),
                    'energy' => fake()->numberBetween(50, 200),
                    'stress' => fake()->numberBetween(0, 50),
                    'anxiety' => fake()->numberBetween(0, 30),
                    'last_login' => fake()->dateTimeBetween('-7 days', 'now'),
                    'last_daily_reward' => fake()->dateTimeBetween('-2 days', 'now'),
                    'consecutive_days' => fake()->numberBetween(1, 30),
                    'completed_situations_since_sleep' => fake()->numberBetween(0, 10),
                    'favorite_song' => fake('ru_RU')->sentence(3),
                    'favorite_movie' => fake('ru_RU')->sentence(2),
                    'favorite_book' => fake('ru_RU')->sentence(2),
                    'favorite_dish' => fake('ru_RU')->sentence(2),
                    'best_friend_name' => fake('ru_RU')->firstName(),
                ]
            );
            
            $this->createPlayerCustomization($profile->id);
        }
        
        $this->command->info('Обновлено/создано 10 пользователей с профилями');
        $this->command->line('');
        $this->command->info('Список пользователей:');
        foreach ($userCredentials as $credentials) {
            $this->command->line($credentials);
        }
    }
    
    private function createPlayerCustomization(int $playerId): void
    {
        $categories = ['background', 'character', 'accessory', 'theme'];
        
        foreach ($categories as $category) {
            PlayerCustomization::updateOrCreate(
                [
                    'player_id' => $playerId,
                    'category_key' => $category
                ],
                [
                    'unlocked_items' => [],
                    'new_unlocked_items' => []
                ]
            );
        }
    }
}
