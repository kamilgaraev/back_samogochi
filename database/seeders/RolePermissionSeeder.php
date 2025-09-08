<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $this->seedPermissions();
            $this->seedRoles();
            $this->assignPermissionsToRoles();
            $this->migrateExistingAdmins();
            
            DB::commit();
            
            $this->command->info('✅ Roles and permissions seeded successfully!');
            $this->printSummary();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Error seeding roles and permissions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed all permissions
     */
    protected function seedPermissions(): void
    {
        $this->command->info('📋 Creating permissions...');
        
        $permissions = Permission::getDefaultPermissions();
        
        foreach ($permissions as $name => $data) {
            Permission::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'is_active' => true,
                ]
            );
        }
        
        $this->command->info("   ✓ Created " . count($permissions) . " permissions");
    }

    /**
     * Seed all roles
     */
    protected function seedRoles(): void
    {
        $this->command->info('👑 Creating roles...');
        
        $roles = Role::getDefaultRoles();
        
        foreach ($roles as $name => $data) {
            Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'priority' => $data['priority'],
                    'is_active' => true,
                ]
            );
        }
        
        $this->command->info("   ✓ Created " . count($roles) . " roles");
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        $this->command->info('🔗 Assigning permissions to roles...');
        
        $rolePermissions = Permission::getRolePermissions();
        
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
                $role->permissions()->sync($permissionIds);
                
                $this->command->info("   ✓ {$role->display_name}: " . count($permissions) . " permissions");
            }
        }
    }

    /**
     * Migrate existing admins to new role system
     */
    protected function migrateExistingAdmins(): void
    {
        $this->command->info('🔄 Migrating existing admins...');
        
        $existingAdmins = User::where('is_admin', true)->get();
        
        if ($existingAdmins->isEmpty()) {
            $this->command->info("   ✓ No existing admins to migrate");
            return;
        }
        
        $superAdminRole = Role::where('name', Role::SUPER_ADMIN)->first();
        
        foreach ($existingAdmins as $admin) {
            if (!$admin->hasRole(Role::SUPER_ADMIN)) {
                $admin->assignRole($superAdminRole, 1); // System assignment
                $this->command->info("   ✓ Assigned Super Admin role to: {$admin->email}");
            }
        }
        
        $this->command->info("   ✓ Migrated " . $existingAdmins->count() . " existing admins");
    }

    /**
     * Print summary of created roles and permissions
     */
    protected function printSummary(): void
    {
        $this->command->info("\n🎯 RBAC System Summary:");
        $this->command->info("=====================================");
        
        // Roles summary
        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $this->command->info("👑 {$role->display_name} ({$role->name})");
            $this->command->info("   Priority: {$role->priority}");
            $this->command->info("   Permissions: " . $role->permissions->count());
            $this->command->info("   Description: {$role->description}");
            $this->command->info("");
        }
        
        // Permissions by category
        $this->command->info("📋 Permissions by Category:");
        $categories = Permission::getCategories();
        foreach ($categories as $category) {
            $count = Permission::byCategory($category)->count();
            $this->command->info("   {$category}: {$count} permissions");
        }
        
        $this->command->info("\n🚀 Next Steps:");
        $this->command->info("1. Run migrations: php artisan migrate");
        $this->command->info("2. Test role assignments in admin panel");
        $this->command->info("3. Update routes with new middleware");
        $this->command->info("");
        
        // Show usage examples
        $this->command->info("📖 Usage Examples:");
        $this->command->info("Route::middleware(['permission:users.view'])->get('/admin/users')");
        $this->command->info("Route::middleware(['role:admin,super-admin'])->group()");
        $this->command->info("Gate::allows('edit-users') or @can('edit-users')");
        $this->command->info("");
    }
}
