<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\PlayerCustomization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmptyUsersSeeder extends Seeder
{
    public function run(): void
    {
        $credentials = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $email = "empty{$i}@example.com";
            $password = 'password123';
            
            $user = User::create([
                'name' => "Empty User {$i}",
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]);
            
            PlayerProfile::create([
                'user_id' => $user->id,
                'level' => 1,
                'total_experience' => 0,
                'energy' => 100,
                'stress' => 50,
                'anxiety' => 30,
                'consecutive_days' => 0,
                'completed_situations_since_sleep' => 0,
            ]);
            
            $this->createPlayerCustomization($user->playerProfile->id);
            
            $credentials[] = "{$email}:{$password}";
        }
        
        $this->command->newLine();
        $this->command->info('=== Созданы пустые пользователи ===');
        $this->command->newLine();
        foreach ($credentials as $cred) {
            $this->command->line($cred);
        }
        $this->command->newLine();
    }
    
    private function createPlayerCustomization(int $playerId): void
    {
        $categories = ['background', 'character', 'accessory', 'theme'];
        
        foreach ($categories as $category) {
            PlayerCustomization::create([
                'player_id' => $playerId,
                'category_key' => $category,
                'unlocked_items' => [],
                'new_unlocked_items' => []
            ]);
        }
    }
}

