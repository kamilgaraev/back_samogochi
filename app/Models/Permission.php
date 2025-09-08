<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'json',
        ];
    }

    // === RELATIONSHIPS ===

    /**
     * Roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps()
                    ->withPivot('conditions');
    }

    // === SCOPES ===

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // === METHODS ===

    /**
     * Check if permission is assigned to any role
     */
    public function isAssigned(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return static::distinct()->pluck('category')->sort()->values()->toArray();
    }

    // === CONSTANTS ===

    // Admin Access
    const ADMIN_ACCESS = 'admin.access';
    
    // User Management
    const USERS_VIEW = 'users.view';
    const USERS_CREATE = 'users.create';
    const USERS_EDIT = 'users.edit';
    const USERS_DELETE = 'users.delete';
    const USERS_MANAGE_ROLES = 'users.manage-roles';
    
    // Situation Management
    const SITUATIONS_VIEW = 'situations.view';
    const SITUATIONS_CREATE = 'situations.create';
    const SITUATIONS_EDIT = 'situations.edit';
    const SITUATIONS_DELETE = 'situations.delete';
    const SITUATIONS_TOGGLE = 'situations.toggle';
    
    // Config Management
    const CONFIGS_VIEW = 'configs.view';
    const CONFIGS_EDIT = 'configs.edit';
    
    // Analytics
    const ANALYTICS_VIEW = 'analytics.view';
    const ANALYTICS_ADVANCED = 'analytics.advanced';
    
    // System Management (Super Admin only)
    const SYSTEM_SETTINGS = 'system.settings';
    const SYSTEM_LOGS = 'system.logs';
    const SYSTEM_MAINTENANCE = 'system.maintenance';

    public static function getDefaultPermissions(): array
    {
        return [
            // === ADMIN ACCESS ===
            self::ADMIN_ACCESS => [
                'display_name' => 'Access Admin Panel',
                'description' => 'Basic access to admin panel',
                'category' => 'admin',
            ],

            // === USER MANAGEMENT ===
            self::USERS_VIEW => [
                'display_name' => 'View Users',
                'description' => 'View user list and profiles',
                'category' => 'users',
            ],
            self::USERS_CREATE => [
                'display_name' => 'Create Users',
                'description' => 'Create new user accounts',
                'category' => 'users',
            ],
            self::USERS_EDIT => [
                'display_name' => 'Edit Users',
                'description' => 'Edit user profiles and settings',
                'category' => 'users',
            ],
            self::USERS_DELETE => [
                'display_name' => 'Delete Users',
                'description' => 'Delete user accounts',
                'category' => 'users',
            ],
            self::USERS_MANAGE_ROLES => [
                'display_name' => 'Manage User Roles',
                'description' => 'Assign and remove user roles',
                'category' => 'users',
            ],

            // === SITUATION MANAGEMENT ===
            self::SITUATIONS_VIEW => [
                'display_name' => 'View Situations',
                'description' => 'View game situations list',
                'category' => 'situations',
            ],
            self::SITUATIONS_CREATE => [
                'display_name' => 'Create Situations',
                'description' => 'Create new game situations',
                'category' => 'situations',
            ],
            self::SITUATIONS_EDIT => [
                'display_name' => 'Edit Situations',
                'description' => 'Edit existing game situations',
                'category' => 'situations',
            ],
            self::SITUATIONS_DELETE => [
                'display_name' => 'Delete Situations',
                'description' => 'Delete game situations',
                'category' => 'situations',
            ],
            self::SITUATIONS_TOGGLE => [
                'display_name' => 'Toggle Situation Status',
                'description' => 'Activate/deactivate situations',
                'category' => 'situations',
            ],

            // === CONFIG MANAGEMENT ===
            self::CONFIGS_VIEW => [
                'display_name' => 'View Configurations',
                'description' => 'View game configurations',
                'category' => 'configs',
            ],
            self::CONFIGS_EDIT => [
                'display_name' => 'Edit Configurations',
                'description' => 'Edit game balance and settings',
                'category' => 'configs',
            ],

            // === ANALYTICS ===
            self::ANALYTICS_VIEW => [
                'display_name' => 'View Analytics',
                'description' => 'View basic analytics and statistics',
                'category' => 'analytics',
            ],
            self::ANALYTICS_ADVANCED => [
                'display_name' => 'Advanced Analytics',
                'description' => 'View detailed analytics and reports',
                'category' => 'analytics',
            ],

            // === SYSTEM MANAGEMENT ===
            self::SYSTEM_SETTINGS => [
                'display_name' => 'System Settings',
                'description' => 'Manage system-wide settings',
                'category' => 'system',
            ],
            self::SYSTEM_LOGS => [
                'display_name' => 'System Logs',
                'description' => 'View system logs and audit trails',
                'category' => 'system',
            ],
            self::SYSTEM_MAINTENANCE => [
                'display_name' => 'System Maintenance',
                'description' => 'Perform system maintenance tasks',
                'category' => 'system',
            ],
        ];
    }

    /**
     * Get role permissions mapping
     */
    public static function getRolePermissions(): array
    {
        return [
            Role::SUPER_ADMIN => [
                // Full access to everything
                self::ADMIN_ACCESS,
                self::USERS_VIEW, self::USERS_CREATE, self::USERS_EDIT, self::USERS_DELETE, self::USERS_MANAGE_ROLES,
                self::SITUATIONS_VIEW, self::SITUATIONS_CREATE, self::SITUATIONS_EDIT, self::SITUATIONS_DELETE, self::SITUATIONS_TOGGLE,
                self::CONFIGS_VIEW, self::CONFIGS_EDIT,
                self::ANALYTICS_VIEW, self::ANALYTICS_ADVANCED,
                self::SYSTEM_SETTINGS, self::SYSTEM_LOGS, self::SYSTEM_MAINTENANCE,
            ],
            Role::ADMIN => [
                // Content and user management
                self::ADMIN_ACCESS,
                self::USERS_VIEW, self::USERS_EDIT,
                self::SITUATIONS_VIEW, self::SITUATIONS_CREATE, self::SITUATIONS_EDIT, self::SITUATIONS_DELETE, self::SITUATIONS_TOGGLE,
                self::CONFIGS_VIEW, self::CONFIGS_EDIT,
                self::ANALYTICS_VIEW, self::ANALYTICS_ADVANCED,
            ],
            Role::MODERATOR => [
                // Limited content management
                self::ADMIN_ACCESS,
                self::USERS_VIEW,
                self::SITUATIONS_VIEW, self::SITUATIONS_EDIT, self::SITUATIONS_TOGGLE,
                self::CONFIGS_VIEW,
                self::ANALYTICS_VIEW,
            ],
        ];
    }
}
