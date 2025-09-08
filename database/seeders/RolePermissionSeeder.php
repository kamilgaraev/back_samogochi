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
            $this->createTestUsers();
            
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
     * Create test users with different roles
     */
    protected function createTestUsers(): void
    {
        $this->command->info('👥 Creating test users with different roles...');
        
        $testUsers = [
            [
                'name' => 'Super Admin Test',
                'email' => 'superadmin@test.com',
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'role' => Role::SUPER_ADMIN,
                'profile' => [
                    'level' => 10,
                    'total_experience' => 50000,
                    'energy' => 200,
                    'stress' => 20,
                    'anxiety' => 10,
                ]
            ],
            [
                'name' => 'Admin Test',
                'email' => 'admin@test.com', 
                'password' => bcrypt('password123'),
                'is_admin' => true,
                'role' => Role::ADMIN,
                'profile' => [
                    'level' => 7,
                    'total_experience' => 25000,
                    'energy' => 150,
                    'stress' => 30,
                    'anxiety' => 25,
                ]
            ],
            [
                'name' => 'Moderator Test',
                'email' => 'moderator@test.com',
                'password' => bcrypt('password123'),
                'is_admin' => false,
                'role' => Role::MODERATOR,
                'profile' => [
                    'level' => 5,
                    'total_experience' => 12000,
                    'energy' => 120,
                    'stress' => 40,
                    'anxiety' => 35,
                ]
            ],
        ];

        foreach ($testUsers as $userData) {
            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();
            
            if ($existingUser) {
                $this->command->info("   ⚠️  User {$userData['email']} already exists, updating role...");
                $user = $existingUser;
            } else {
                // Create new user
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'is_admin' => $userData['is_admin'],
                    'email_verified_at' => now(),
                ]);
                
                // Create player profile
                $user->playerProfile()->create($userData['profile']);
                $this->command->info("   ✓ Created user: {$userData['email']}");
            }
            
            // Assign role
            $role = Role::where('name', $userData['role'])->first();
            if ($role && !$user->hasRole($userData['role'])) {
                $user->assignRole($role);
                $this->command->info("   ✓ Assigned {$role->display_name} role to {$user->email}");
            }
        }
        
        $this->command->info("   ✓ Test users setup completed");
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
        
        // Test users summary
        $this->command->info("\n👥 Test Users Created:");
        $testEmails = ['superadmin@test.com', 'admin@test.com', 'moderator@test.com'];
        foreach ($testEmails as $email) {
            $user = User::where('email', $email)->with('roles')->first();
            if ($user) {
                $roleName = $user->roles->first() ? $user->roles->first()->display_name : 'No Role';
                $this->command->info("   {$user->name} ({$email}) - {$roleName}");
            }
        }
        
        $this->command->info("\n🔐 Login Credentials:");
        $this->command->info("   All test users password: password123");
        $this->command->info("   Admin panel: /admin/login");
        
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
