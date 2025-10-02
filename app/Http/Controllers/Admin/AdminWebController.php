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
        Gate::authorize('users.view');
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
        Gate::authorize('users.view');
        
        $user = User::with(['playerProfile', 'roles', 'activityLogs' => function($q) {
            $q->latest()->limit(50);
        }])->findOrFail($id);

        $availableRoles = Role::active()->orderBy('priority', 'desc')->get();

        return view('admin.users.show', compact('user', 'availableRoles'));
    }

    public function userToggleAdmin(Request $request, $id)
    {
        Gate::authorize('users.manage-roles');
        
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
        Gate::authorize('users.manage-roles');
        
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
        Gate::authorize('users.manage-roles');
        
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
        Gate::authorize('users.manage-roles');
        
        $roles = Role::with(['permissions', 'users'])->orderBy('priority', 'desc')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get();
        
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Create role page
     */
    public function roleCreate()
    {
        Gate::authorize('users.manage-roles');
        
        $permissions = Permission::active()->orderBy('category')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy('category');
        
        return view('admin.roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store new role
     */
    public function roleStore(Request $request)
    {
        Gate::authorize('users.manage-roles');
        
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
        Gate::authorize('situations.view');
        
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
        Gate::authorize('situations.create');
        
        $enumData = $this->prepareSituationEnumData();
        
        return view('admin.situations.create', $enumData);
    }

    public function situationStore(Request $request)
    {
        Gate::authorize('situations.create');
        
        $result = $this->adminService->createSituation($request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    public function situationEdit($id)
    {
        Gate::authorize('situations.edit');
        
        $situation = \App\Models\Situation::with('options')->findOrFail($id);
        $enumData = $this->prepareSituationEnumData();
        
        return view('admin.situations.edit', array_merge(compact('situation'), $enumData));
    }

    public function situationUpdate(Request $request, $id)
    {
        Gate::authorize('situations.edit');
        
        $result = $this->adminService->updateSituation($id, $request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    public function situationDestroy($id)
    {
        Gate::authorize('situations.delete');
        
        $result = $this->adminService->deleteSituation($id, auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('admin.situations.index')->with('success', $result['message']);
    }

    // === МИКРО-ДЕЙСТВИЯ ===
    public function microActions()
    {
        Gate::authorize('situations.view');
        
        $filters = request()->only(['category', 'is_active', 'unlock_level']);
        $result = $this->adminService->getMicroActions($filters);
        
        $categories = collect(\App\Enums\MicroActionCategory::cases())
            ->mapWithKeys(fn($category) => [$category->value => $category->getLabel()]);
        
        return view('admin.micro-actions.index', [
            'microActions' => $result['data']['micro_actions'],
            'pagination' => $result['data']['pagination'],
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    public function microActionCreate()
    {
        Gate::authorize('situations.create');
        
        $categories = \App\Enums\MicroActionCategory::cases();
        
        return view('admin.micro-actions.create', compact('categories'));
    }

    public function microActionStore(Request $request)
    {
        Gate::authorize('situations.create');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'energy_reward' => 'required|integer|min:0|max:100',
            'experience_reward' => 'required|integer|min:0|max:100',
            'cooldown_minutes' => 'required|integer|min:1|max:1440',
            'unlock_level' => 'required|integer|min:1|max:100',
            'category' => 'required|in:' . \App\Enums\MicroActionCategory::getForValidation(),
            'position' => 'required|in:desktop,phone,tablet',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $result = $this->adminService->createMicroAction($request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('admin.micro-actions.index')->with('success', $result['message']);
    }

    public function microActionEdit($id)
    {
        Gate::authorize('situations.edit');
        
        $microAction = \App\Models\MicroAction::findOrFail($id);
        $categories = \App\Enums\MicroActionCategory::cases();
        
        return view('admin.micro-actions.edit', compact('microAction', 'categories'));
    }

    public function microActionUpdate(Request $request, $id)
    {
        Gate::authorize('situations.edit');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'energy_reward' => 'required|integer|min:0|max:100',
            'experience_reward' => 'required|integer|min:0|max:100',
            'cooldown_minutes' => 'required|integer|min:1|max:1440',
            'unlock_level' => 'required|integer|min:1|max:100',
            'category' => 'required|in:' . \App\Enums\MicroActionCategory::getForValidation(),
            'position' => 'required|in:desktop,phone,tablet',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $result = $this->adminService->updateMicroAction($id, $request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return back()->with('success', $result['message']);
    }

    public function microActionDestroy($id)
    {
        Gate::authorize('situations.delete');
        
        $result = $this->adminService->deleteMicroAction($id, auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('admin.micro-actions.index')->with('success', $result['message']);
    }

    // === КОНФИГУРАЦИИ ИГРЫ ===
    public function configs()
    {
        Gate::authorize('configs.view');
        
        $result = $this->adminService->getConfigs();
        return view('admin.configs.index', ['configs' => $result['data']['configs']]);
    }

    public function configUpdate(Request $request, $key)
    {
        Gate::authorize('configs.edit');
        
        // Логирование убрано после успешного тестирования
        
        $result = $this->adminService->updateConfig($key, $request->all(), auth()->id());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    // === PRIVATE HELPERS ===

    /**
     * Prepare enum data for situation forms
     */
    private function prepareSituationEnumData(): array
    {
        $categories = collect(\App\Enums\SituationCategory::cases())->map(function ($category) {
            return [
                'value' => $category->value,
                'label' => $category->getLabel(),
                'icon' => $category->getIcon(),
                'description' => $category->getDescription()
            ];
        });
        
        $difficulties = collect(\App\Enums\DifficultyLevel::cases())->map(function ($difficulty) {
            return [
                'value' => $difficulty->value,
                'label' => $difficulty->getLabel(),
                'icon' => $difficulty->getIcon(),
                'description' => $difficulty->getDescription(),
                'experience' => $difficulty->getTypicalExperienceReward(),
                'stress' => $difficulty->getTypicalStressImpact()
            ];
        });
        
        $positions = [
            ['value' => 'phone', 'label' => 'Телефон', 'icon' => '📱', 'description' => 'Отображение на мобильном устройстве'],
            ['value' => 'tv', 'label' => 'Телевизор', 'icon' => '📺', 'description' => 'Отображение на большом экране'],
            ['value' => 'desktop', 'label' => 'Рабочий стол', 'icon' => '💻', 'description' => 'Отображение на компьютере'],
            ['value' => 'tablet', 'label' => 'Планшет', 'icon' => '📋', 'description' => 'Отображение на планшете'],
            ['value' => 'smartwatch', 'label' => 'Умные часы', 'icon' => '⌚', 'description' => 'Краткое уведомление на часах'],
            ['value' => 'notification', 'label' => 'Уведомление', 'icon' => '🔔', 'description' => 'Push-уведомление']
        ];
        
        return compact('categories', 'difficulties', 'positions');
    }
}
