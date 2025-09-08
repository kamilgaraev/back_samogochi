<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Services\AnalyticsService;
use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AdminWebController extends Controller
{
    protected AdminService $adminService;
    protected AnalyticsService $analyticsService;

    public function __construct(AdminService $adminService, AnalyticsService $analyticsService)
    {
        $this->adminService = $adminService;
        $this->analyticsService = $analyticsService;
    }

    public function login()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            if (!$user->is_admin) {
                Auth::logout();
                return back()->withErrors(['email' => 'Недостаточно прав доступа']);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Неверные учетные данные']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        // Получаем статистику через AnalyticsService
        $analytics = $this->analyticsService->getDashboard();
        
        // Дополнительные данные для дашборда
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereHas('playerProfile', function($q) {
                $q->where('last_login', '>=', now()->subDays(7));
            })->count(),
            'total_situations' => \App\Models\Situation::count(),
            'active_situations' => \App\Models\Situation::where('is_active', true)->count(),
            'recent_users' => User::with('playerProfile')->latest()->limit(5)->get()
        ];

        return view('admin.dashboard', compact('analytics', 'stats'));
    }

    // === УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ ===
    public function users(Request $request)
    {
        Gate::authorize('view-users');
        $query = User::with(['playerProfile', 'roles']);

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        if ($request->is_admin !== null) {
            $query->where('is_admin', $request->is_admin);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function userShow($id)
    {
        Gate::authorize('view-users');
        
        $user = User::with(['playerProfile', 'roles', 'activityLogs' => function($q) {
            $q->latest()->limit(50);
        }])->findOrFail($id);

        $availableRoles = Role::active()->orderBy('priority', 'desc')->get();

        return view('admin.users.show', compact('user', 'availableRoles'));
    }

    public function userToggleAdmin(Request $request, $id)
    {
        Gate::authorize('manage-user-roles');
        
        $user = User::findOrFail($id);
        
        // Legacy support - toggle is_admin flag and assign role
        if ($user->is_admin) {
            $user->update(['is_admin' => false]);
            $user->roles()->detach(); // Remove all roles
            $message = 'Права администратора отозваны';
        } else {
            $user->update(['is_admin' => true]);
            // Assign admin role by default
            $adminRole = Role::where('name', Role::ADMIN)->first();
            if ($adminRole) {
                $user->assignRole($adminRole);
            }
            $message = 'Пользователь получил права администратора';
        }

        return back()->with('success', $message);
    }

    // === ROLE MANAGEMENT ===

    /**
     * Assign role to user
     */
    public function userAssignRole(Request $request, $id)
    {
        Gate::authorize('manage-user-roles');
        
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);
        
        $user = User::findOrFail($id);
        $role = Role::findOrFail($request->role_id);
        
        if ($user->hasRole($role)) {
            return back()->with('error', "Пользователь уже имеет роль '{$role->display_name}'");
        }
        
        $user->assignRole($role, auth()->id());
        
        return back()->with('success', "Роль '{$role->display_name}' назначена пользователю");
    }

    /**
     * Remove role from user
     */
    public function userRemoveRole(Request $request, $userId, $roleId)
    {
        Gate::authorize('manage-user-roles');
        
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);
        
        if (!$user->hasRole($role)) {
            return back()->with('error', "Пользователь не имеет роль '{$role->display_name}'");
        }
        
        // Prevent removing super-admin role from self
        if ($role->name === Role::SUPER_ADMIN && $user->id === auth()->id()) {
            return back()->with('error', "Нельзя отозвать роль Super Admin у самого себя");
        }
        
        $user->removeRole($role);
        
        return back()->with('success', "Роль '{$role->display_name}' отозвана у пользователя");
    }

    /**
     * Roles management page
     */
    public function roles()
    {
        Gate::authorize('manage-user-roles');
        
        $roles = Role::with(['permissions', 'users'])->orderBy('priority', 'desc')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get();
        
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Create role page
     */
    public function roleCreate()
    {
        Gate::authorize('manage-user-roles');
        
        $permissions = Permission::active()->orderBy('category')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('category');
        
        return view('admin.roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store new role
     */
    public function roleStore(Request $request)
    {
        Gate::authorize('manage-user-roles');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:0|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'priority' => $request->priority,
            'is_active' => true,
        ]);
        
        if ($request->permissions) {
            $role->permissions()->sync($request->permissions);
        }
        
        return redirect()->route('admin.roles.index')->with('success', "Роль '{$role->display_name}' создана");
    }

    // === УПРАВЛЕНИЕ СИТУАЦИЯМИ ===
    public function situations(Request $request)
    {
        Gate::authorize('view-situations');
        
        $filters = $request->only(['category', 'difficulty_level', 'is_active']);
        $result = $this->adminService->getSituations($filters);

        return view('admin.situations.index', [
            'situations' => $result['data']['situations'],
            'pagination' => $result['data']['pagination'],
            'filters' => $filters
        ]);
    }

    public function situationCreate()
    {
        Gate::authorize('create-situations');
        return view('admin.situations.create');
    }

    public function situationStore(Request $request)
    {
        Gate::authorize('create-situations');
        
        $result = $this->adminService->createSituation($request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    public function situationEdit($id)
    {
        Gate::authorize('edit-situations');
        
        $situation = \App\Models\Situation::with('options')->findOrFail($id);
        return view('admin.situations.edit', compact('situation'));
    }

    public function situationUpdate(Request $request, $id)
    {
        Gate::authorize('edit-situations');
        
        $result = $this->adminService->updateSituation($id, $request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    public function situationDestroy($id)
    {
        Gate::authorize('delete-situations');
        
        $result = $this->adminService->deleteSituation($id, auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    // === КОНФИГУРАЦИИ ИГРЫ ===
    public function configs()
    {
        Gate::authorize('view-configs');
        
        $result = $this->adminService->getConfigs();
        return view('admin.configs.index', ['configs' => $result['data']['configs']]);
    }

    public function configUpdate(Request $request, $key)
    {
        Gate::authorize('edit-configs');
        
        $result = $this->adminService->updateConfig($key, $request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }
}
