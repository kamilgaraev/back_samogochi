<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        try {
            $this->command->info('📋 Creating permissions...');
            
            // Create permissions
            foreach (Permission::getDefaultPermissions() as $name => $attrs) {
                Permission::firstOrCreate(
                    ['name' => $name],
                    array_merge($attrs, ['is_active' => true])
                );
            }

            $this->command->info('👑 Creating roles...');
            
            // Create roles
            foreach (Role::getDefaultRoles() as $name => $attrs) {
                Role::firstOrCreate(
                    ['name' => $name],
                    array_merge($attrs, ['is_active' => true])
                );
            }

            $this->command->info('🔄 Assigning permissions to roles...');
            
            // Assign permissions to roles
            foreach (Permission::getRolePermissions() as $roleName => $permissions) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->syncPermissions($permissions);
                }
            }

            $this->command->info('👤 Assigning Super Admin role to first user...');
            
            // Assign Super Admin role to first user
            $firstUser = User::orderBy('id')->first();
            if ($firstUser) {
                $superAdminRole = Role::where('name', Role::SUPER_ADMIN)->first();
                if ($superAdminRole) {
                    $firstUser->roles()->syncWithoutDetaching([$superAdminRole->id]);
                    $this->command->info("✅ Super Admin role assigned to user: {$firstUser->email}");
                }
            }

            $this->command->info('✨ Roles and permissions seeded successfully!');

        } catch (\Exception $e) {
            $this->command->error('❌ Error seeding roles and permissions: ' . $e->getMessage());
            Log::error('Error in RolePermissionSeeder: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}