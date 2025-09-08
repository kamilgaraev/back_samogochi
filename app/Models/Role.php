<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'priority',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'metadata' => 'json',
        ];
    }

    // === RELATIONSHIPS ===

    /**
     * Users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps()
                    ->withPivot('assigned_at', 'assigned_by');
    }

    /**
     * Permissions assigned to this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
                    ->withTimestamps()
                    ->withPivot('conditions');
    }

    // === SCOPES ===

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    // === METHODS ===

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
                    ->where('permissions.name', $permissionName)
                    ->where('permissions.is_active', true)
                    ->exists();
    }

    /**
     * Check if role has any permission from array
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()
                    ->whereIn('permissions.name', $permissions)
                    ->where('permissions.is_active', true)
                    ->exists();
    }

    /**
     * Check if role has all permissions from array
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $rolePermissions = $this->permissions()
                               ->where('permissions.is_active', true)
                               ->pluck('permissions.name')
                               ->toArray();

        return empty(array_diff($permissions, $rolePermissions));
    }

    /**
     * Assign permission to role
     */
    public function givePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        if (!$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    /**
     * Remove permission from role
     */
    public function revokePermission(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);

        return $this;
    }

    /**
     * Sync permissions for role
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
        $this->permissions()->sync($permissionIds);

        return $this;
    }

    // === STATIC METHODS ===

    /**
     * Get role by name
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->active()->first();
    }

    /**
     * Get highest priority role from collection
     */
    public static function getHighestPriority($roles): ?self
    {
        if ($roles->isEmpty()) {
            return null;
        }

        return $roles->sortByDesc('priority')->first();
    }

    // === CONSTANTS ===
    const SUPER_ADMIN = 'super-admin';
    const ADMIN = 'admin';
    const MODERATOR = 'moderator';

    public static function getDefaultRoles(): array
    {
        return [
            self::SUPER_ADMIN => [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with ability to manage administrators',
                'priority' => 100,
            ],
            self::ADMIN => [
                'display_name' => 'Administrator',
                'description' => 'Manage users, content, and system settings',
                'priority' => 50,
            ],
            self::MODERATOR => [
                'display_name' => 'Moderator',
                'description' => 'View and moderate content, limited user management',
                'priority' => 25,
            ],
        ];
    }
}
