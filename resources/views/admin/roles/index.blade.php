@extends('admin.layouts.app')

@section('title', 'Управление ролями и правами')

@section('header-actions')
<div class="flex items-center space-x-4">
    @can('users.manage-roles')
        <a href="{{ route('admin.roles.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i>Создать роль
        </a>
    @endcan
</div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Header info -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold">🛡️ Управление ролями и правами</h1>
        <p class="text-purple-100 mt-1">
            Система контроля доступа (RBAC) - управление ролями, разрешениями и назначениями
        </p>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-user-shield text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $roles->count() }}</p>
                    <p class="text-sm text-gray-600">Ролей в системе</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-key text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $permissions->count() }}</p>
                    <p class="text-sm text-gray-600">Разрешений</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $roles->sum(function($role) { return $role->users->count(); }) }}</p>
                    <p class="text-sm text-gray-600">Назначений ролей</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $permissions->groupBy('category')->count() }}</p>
                    <p class="text-sm text-gray-600">Категорий прав</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-shield text-purple-600 mr-2"></i>
                Роли в системе
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Роль
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Пользователи
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Разрешения
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Приоритет
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @php
                                        $roleColors = [
                                            'super-admin' => 'bg-red-100 text-red-800',
                                            'admin' => 'bg-blue-100 text-blue-800',
                                            'moderator' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <div class="w-10 h-10 {{ $color }} rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                        {{ strtoupper(substr($role->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $role->display_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $role->name }}</div>
                                        @if($role->description)
                                            <div class="text-xs text-gray-400 mt-1">{{ Str::limit($role->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $role->users->count() }} пользователей
                                    </span>
                                </div>
                                @if($role->users->count() > 0)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $role->users->take(3)->pluck('name')->implode(', ') }}
                                        @if($role->users->count() > 3)
                                            и ещё {{ $role->users->count() - 3 }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $role->permissions->count() }} разрешений
                                    </span>
                                </div>
                                @if($role->permissions->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($role->permissions->groupBy('category') as $category => $categoryPermissions)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $category }}: {{ $categoryPermissions->count() }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-800 text-sm font-bold mr-2">
                                            {{ $role->priority }}
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            @if($role->priority >= 80)
                                                Высший
                                            @elseif($role->priority >= 50)
                                                Высокий
                                            @elseif($role->priority >= 25)
                                                Средний
                                            @else
                                                Низкий
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($role->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Активна
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-pause-circle mr-1"></i> Неактивна
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-user-shield text-4xl mb-4 opacity-50"></i>
                                <p>Роли не найдены</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Permissions by category -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-key text-blue-600 mr-2"></i>
                Разрешения по категориям
            </h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($permissions->groupBy('category') as $category => $categoryPermissions)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-md font-medium text-gray-900 capitalize">
                                @php
                                    $categoryIcons = [
                                        'admin' => 'fa-shield-alt',
                                        'users' => 'fa-users',
                                        'situations' => 'fa-puzzle-piece', 
                                        'configs' => 'fa-cog',
                                        'analytics' => 'fa-chart-bar',
                                        'system' => 'fa-server'
                                    ];
                                    $icon = $categoryIcons[$category] ?? 'fa-key';
                                @endphp
                                <i class="fas {{ $icon }} mr-2"></i>
                                {{ ucfirst($category) }}
                            </h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $categoryPermissions->count() }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            @foreach($categoryPermissions as $permission)
                                <div class="text-xs text-gray-600 flex items-center">
                                    <i class="fas fa-dot-circle mr-2 text-gray-400"></i>
                                    {{ $permission->display_name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-4">
            <i class="fas fa-lightbulb mr-2"></i>
            Быстрые действия
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @can('users.manage-roles')
                <a href="{{ route('admin.roles.create') }}" 
                   class="flex items-center p-4 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                    <i class="fas fa-plus-circle text-blue-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-medium text-blue-900">Создать роль</p>
                        <p class="text-sm text-blue-700">Добавить новую роль в систему</p>
                    </div>
                </a>
            @endcan
            
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center p-4 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-users text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-blue-900">Управление пользователями</p>
                    <p class="text-sm text-blue-700">Назначить роли пользователям</p>
                </div>
            </a>
            
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center p-4 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-chart-bar text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-blue-900">Статистика ролей</p>
                    <p class="text-sm text-blue-700">Аналитика использования</p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
