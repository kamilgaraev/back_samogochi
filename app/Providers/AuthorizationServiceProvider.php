<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Define your model policies here
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerRoleGates();
        $this->registerPermissionGates();
        $this->registerDynamicPermissionGates();
    }

    /**
     * Register role-based gates
     */
    protected function registerRoleGates(): void
    {
        // Super Admin gate - can do everything
        Gate::define('is-super-admin', function (User $user) {
            return $user->hasRole(Role::SUPER_ADMIN);
        });

        // Admin gate - general admin access
        Gate::define('is-admin', function (User $user) {
            return $user->hasAnyRole([Role::SUPER_ADMIN, Role::ADMIN]);
        });

        // Moderator gate - moderator and above
        Gate::define('is-moderator', function (User $user) {
            return $user->hasAnyRole([Role::SUPER_ADMIN, Role::ADMIN, Role::MODERATOR]);
        });

        // Check specific role
        Gate::define('has-role', function (User $user, string $roleName) {
            return $user->hasRole($roleName);
        });

        // Check any of multiple roles
        Gate::define('has-any-role', function (User $user, array $roles) {
            return $user->hasAnyRole($roles);
        });

        // Check all roles
        Gate::define('has-all-roles', function (User $user, array $roles) {
            return $user->hasAllRoles($roles);
        });
    }

    /**
     * Register permission-based gates
     */
    protected function registerPermissionGates(): void
    {
        // Check specific permission
        Gate::define('has-permission', function (User $user, string $permission) {
            return $user->hasPermission($permission);
        });

        // Check any of multiple permissions
        Gate::define('has-any-permission', function (User $user, array $permissions) {
            return $user->hasAnyPermission($permissions);
        });

        // Check all permissions
        Gate::define('has-all-permissions', function (User $user, array $permissions) {
            return $user->hasAllPermissions($permissions);
        });

        // Admin panel access
        Gate::define('access-admin', function (User $user) {
            return $user->hasPermission(Permission::ADMIN_ACCESS);
        });

        // === USER MANAGEMENT ===
        Gate::define('view-users', function (User $user) {
            return $user->hasPermission(Permission::USERS_VIEW);
        });

        Gate::define('create-users', function (User $user) {
            return $user->hasPermission(Permission::USERS_CREATE);
        });

        Gate::define('edit-users', function (User $user) {
            return $user->hasPermission(Permission::USERS_EDIT);
        });

        Gate::define('delete-users', function (User $user) {
            return $user->hasPermission(Permission::USERS_DELETE);
        });

        Gate::define('manage-user-roles', function (User $user) {
            return $user->hasPermission(Permission::USERS_MANAGE_ROLES);
        });

        // === SITUATION MANAGEMENT ===
        Gate::define('view-situations', function (User $user) {
            return $user->hasPermission(Permission::SITUATIONS_VIEW);
        });

        Gate::define('create-situations', function (User $user) {
            return $user->hasPermission(Permission::SITUATIONS_CREATE);
        });

        Gate::define('edit-situations', function (User $user) {
            return $user->hasPermission(Permission::SITUATIONS_EDIT);
        });

        Gate::define('delete-situations', function (User $user) {
            return $user->hasPermission(Permission::SITUATIONS_DELETE);
        });

        Gate::define('toggle-situations', function (User $user) {
            return $user->hasPermission(Permission::SITUATIONS_TOGGLE);
        });

        // === CONFIG MANAGEMENT ===
        Gate::define('view-configs', function (User $user) {
            return $user->hasPermission(Permission::CONFIGS_VIEW);
        });

        Gate::define('edit-configs', function (User $user) {
            return $user->hasPermission(Permission::CONFIGS_EDIT);
        });

        // === ANALYTICS ===
        Gate::define('view-analytics', function (User $user) {
            return $user->hasPermission(Permission::ANALYTICS_VIEW);
        });

        Gate::define('view-advanced-analytics', function (User $user) {
            return $user->hasPermission(Permission::ANALYTICS_ADVANCED);
        });

        // === SYSTEM MANAGEMENT ===
        Gate::define('manage-system-settings', function (User $user) {
            return $user->hasPermission(Permission::SYSTEM_SETTINGS);
        });

        Gate::define('view-system-logs', function (User $user) {
            return $user->hasPermission(Permission::SYSTEM_LOGS);
        });

        Gate::define('system-maintenance', function (User $user) {
            return $user->hasPermission(Permission::SYSTEM_MAINTENANCE);
        });
    }

    /**
     * Register dynamic permission gates (loaded from database)
     */
    protected function registerDynamicPermissionGates(): void
    {
        try {
            // Only register if database tables exist (avoid errors during migrations)
            if (schema()->hasTable('permissions')) {
                $permissions = Permission::active()->get();

                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function (User $user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            // Silently fail during migrations or when tables don't exist yet
            \Log::debug('Could not register dynamic permission gates: ' . $e->getMessage());
        }
    }
}

/**
 * Helper function to check if schema builder is available
 */
if (!function_exists('schema')) {
    function schema() {
        return app('db')->connection()->getSchemaBuilder();
    }
}
