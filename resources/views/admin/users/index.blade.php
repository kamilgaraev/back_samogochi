@extends('admin.layouts.app')

@section('title', 'Управление пользователями')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Search form -->
    <form method="GET" class="flex items-center space-x-2">
        <input type="text" name="search" value="{{ request('search') }}" 
               placeholder="Поиск по имени или email..."
               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <select name="is_admin" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Все пользователи</option>
            <option value="1" {{ request('is_admin') == '1' ? 'selected' : '' }}>Только админы</option>
            <option value="0" {{ request('is_admin') == '0' ? 'selected' : '' }}>Только игроки</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-search"></i>
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $users->total() }}</p>
                    <p class="text-sm text-gray-600">Всего пользователей</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $users->where('playerProfile.last_login', '>=', now()->subDays(7))->count() }}</p>
                    <p class="text-sm text-gray-600">Активных за неделю</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-crown text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $users->where('is_admin', true)->count() }}</p>
                    <p class="text-sm text-gray-600">Администраторов</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-users text-blue-600 mr-2"></i>
                Список пользователей
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Пользователь
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Игровой профиль
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Активность
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Роль
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->playerProfile)
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Level {{ $user->playerProfile->level }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Энергия: {{ $user->playerProfile->energy }}/200
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Опыт: {{ number_format($user->playerProfile->total_experience) }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Нет профиля</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->playerProfile && $user->playerProfile->last_login)
                                    <div class="text-sm text-gray-900">{{ $user->playerProfile->last_login->diffForHumans() }}</div>
                                    @if($user->playerProfile->last_login >= now()->subDays(1))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-green-400 mr-1"></i> Активен
                                        </span>
                                    @elseif($user->playerProfile->last_login >= now()->subDays(7))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-circle text-yellow-400 mr-1"></i> Недавно
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-circle text-gray-400 mr-1"></i> Неактивен
                                        </span>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-400">Не заходил</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_admin)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-crown mr-1"></i> Админ
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-user mr-1"></i> Игрок
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                </a>
                                
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-{{ $user->is_admin ? 'red' : 'green' }}-600 hover:text-{{ $user->is_admin ? 'red' : 'green' }}-900"
                                                onclick="return confirm('Изменить права пользователя?')">
                                            <i class="fas fa-{{ $user->is_admin ? 'user-minus' : 'user-plus' }} mr-1"></i>
                                            {{ $user->is_admin ? 'Убрать админа' : 'Сделать админом' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                                <p>Пользователи не найдены</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
