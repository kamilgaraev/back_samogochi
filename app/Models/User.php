<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function playerProfile()
    {
        return $this->hasOne(PlayerProfile::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // === ROLE SYSTEM ===

    /**
     * Roles assigned to user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps()
                    ->withPivot('assigned_at', 'assigned_by');
    }

    /**
     * Get user's highest priority role
     */
    public function getHighestRoleAttribute(): ?Role
    {
        return Role::getHighestPriority($this->roles);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string|Role $role): bool
    {
        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        return $this->roles->contains('name', $role);
    }

    /**
     * Check if user has any role from array
     */
    public function hasAnyRole(array $roles): bool
    {
        $roleNames = $this->roles->pluck('name')->toArray();
        return !empty(array_intersect($roles, $roleNames));
    }

    /**
     * Check if user has all roles from array
     */
    public function hasAllRoles(array $roles): bool
    {
        $roleNames = $this->roles->pluck('name')->toArray();
        return empty(array_diff($roles, $roleNames));
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Super admins have all permissions
        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        // Check through roles
        return $this->roles->filter(function ($role) use ($permission) {
            return $role->hasPermission($permission);
        })->isNotEmpty();
    }

    /**
     * Check if user has any permission from array
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all permissions from array
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign role to user
     */
    public function assignRole(string|Role $role, ?int $assignedBy = null): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        if (!$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy ?? auth()->id(),
            ]);
        }

        return $this;
    }

    /**
     * Remove role from user
     */
    public function removeRole(string|Role $role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);

        return $this;
    }

    /**
     * Sync roles for user
     */
    public function syncRoles(array $roles, ?int $assignedBy = null): self
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id')->toArray();
        
        $syncData = [];
        foreach ($roleIds as $roleId) {
            $syncData[$roleId] = [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy ?? auth()->id(),
            ];
        }

        $this->roles()->sync($syncData);

        return $this;
    }

    /**
     * Check if user is admin (has any admin role or legacy is_admin flag)
     */
    public function isAdmin(): bool
    {
        // Legacy support for is_admin flag
        if ($this->is_admin) {
            return true;
        }

        // Check if has admin access permission
        return $this->hasPermission(Permission::ADMIN_ACCESS);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Get all user permissions (through roles)
     */
    public function getAllPermissions()
    {
        if ($this->hasRole(Role::SUPER_ADMIN)) {
            return Permission::active()->get();
        }

        return Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles->pluck('id'));
        })->active()->get();
    }

    /**
     * Scope for users with specific role
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope for admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where(function ($q) {
            $q->where('is_admin', true)
              ->orWhereHas('roles', function ($roleQuery) {
                  $roleQuery->whereIn('name', [Role::SUPER_ADMIN, Role::ADMIN, Role::MODERATOR]);
              });
        });
    }
}
