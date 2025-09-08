<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role 
                            {email : User email address}
                            {role : Role name (super-admin, admin, moderator)}
                            {--remove : Remove role instead of assigning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign or remove role from user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');
        $remove = $this->option('remove');
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ User with email '{$email}' not found");
            return 1;
        }
        
        // Find role
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("❌ Role '{$roleName}' not found");
            $this->info("Available roles: " . implode(', ', array_keys(Role::getDefaultRoles())));
            return 1;
        }
        
        try {
            if ($remove) {
                // Remove role
                if (!$user->hasRole($role)) {
                    $this->warn("⚠️  User '{$email}' doesn't have role '{$role->display_name}'");
                    return 1;
                }
                
                $user->removeRole($role);
                $this->info("✅ Removed role '{$role->display_name}' from user '{$email}'");
                
            } else {
                // Assign role
                if ($user->hasRole($role)) {
                    $this->warn("⚠️  User '{$email}' already has role '{$role->display_name}'");
                    return 1;
                }
                
                $user->assignRole($role, 1); // System assignment
                $this->info("✅ Assigned role '{$role->display_name}' to user '{$email}'");
            }
            
            // Show user's current roles
            $currentRoles = $user->fresh()->roles->pluck('display_name')->toArray();
            $this->info("Current roles: " . (empty($currentRoles) ? 'None' : implode(', ', $currentRoles)));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
