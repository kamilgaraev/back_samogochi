<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Role system should be seeded first
            RolePermissionSeeder::class,
            
            // Game content seeders
            GameConfigSeeder::class,
            SituationSeeder::class,
            MicroActionSeeder::class,
        ]);
    }
}