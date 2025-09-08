@extends('admin.layouts.app')

@section('title', 'Профиль пользователя: ' . $user->name)

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.users.index') }}" 
       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
        <i class="fas fa-arrow-left mr-2"></i>Назад к списку
    </a>
    @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('admin.users.toggle-admin', $user->id) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="px-4 py-2 {{ $user->is_admin ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg"
                    onclick="return confirm('Изменить права пользователя?')">
                <i class="fas fa-{{ $user->is_admin ? 'user-minus' : 'user-plus' }} mr-2"></i>
                {{ $user->is_admin ? 'Убрать админа' : 'Сделать админом' }}
            </button>
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- User profile header -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-500 to-purple-600 text-white">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-2xl font-bold">
                    {{ substr($user->name, 0, 2) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <p class="text-blue-100">{{ $user->email }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        @if($user->is_admin)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-200 text-purple-800">
                                <i class="fas fa-crown mr-1"></i> Администратор
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white">
                                <i class="fas fa-user mr-1"></i> Игрок
                            </span>
                        @endif
                        <span class="text-sm text-blue-100">
                            Регистрация: {{ $user->created_at->format('d.m.Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Game profile -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Game stats -->
            @if($user->playerProfile)
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-gamepad text-green-600 mr-2"></i>
                            Игровой профиль
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            
                            <!-- Level -->
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto bg-blue-500 rounded-full flex items-center justify-center text-white text-xl font-bold mb-2">
                                    {{ $user->playerProfile->level }}
                                </div>
                                <p class="text-sm font-medium text-gray-900">Уровень</p>
                            </div>
                            
                            <!-- Experience -->
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2">
                                    {{ number_format($user->playerProfile->total_experience) }}
                                </div>
                                <p class="text-sm font-medium text-gray-900">Опыт</p>
                            </div>
                            
                            <!-- Energy -->
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2">
                                    {{ $user->playerProfile->energy }}
                                </div>
                                <p class="text-sm font-medium text-gray-900">Энергия</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($user->playerProfile->energy / 200) * 100 }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Consecutive days -->
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto bg-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2">
                                    {{ $user->playerProfile->consecutive_days }}
                                </div>
                                <p class="text-sm font-medium text-gray-900">Дней подряд</p>
                            </div>
                        </div>
                        
                        <!-- Stats bars -->
                        <div class="mt-6 space-y-4">
                            <!-- Stress -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">Стресс</span>
                                    <span class="text-gray-500">{{ $user->playerProfile->stress }}/100</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-red-500 h-3 rounded-full" style="width: {{ $user->playerProfile->stress }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Anxiety -->
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">Тревожность</span>
                                    <span class="text-gray-500">{{ $user->playerProfile->anxiety }}/100</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-orange-500 h-3 rounded-full" style="width: {{ $user->playerProfile->anxiety }}%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last login -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Последний вход:</span>
                                <span class="text-sm text-gray-900">
                                    @if($user->playerProfile->last_login)
                                        {{ $user->playerProfile->last_login->format('d.m.Y H:i') }}
                                        <span class="text-gray-500">({{ $user->playerProfile->last_login->diffForHumans() }})</span>
                                    @else
                                        Никогда
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <i class="fas fa-user-slash text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">У пользователя нет игрового профиля</p>
                </div>
            @endif
            
            <!-- Activity log -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-history text-blue-600 mr-2"></i>
                        История активности (последние 50 событий)
                    </h3>
                </div>
                <div class="p-6">
                    @if($user->activityLogs->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->activityLogs as $log)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        @php
                                            $iconMap = [
                                                'user_login' => ['icon' => 'sign-in-alt', 'color' => 'green'],
                                                'user_logout' => ['icon' => 'sign-out-alt', 'color' => 'red'],
                                                'user_registration' => ['icon' => 'user-plus', 'color' => 'blue'],
                                                'situation_completed' => ['icon' => 'check-circle', 'color' => 'purple'],
                                                'micro_action_performed' => ['icon' => 'bolt', 'color' => 'yellow'],
                                                'level_up' => ['icon' => 'arrow-up', 'color' => 'green'],
                                            ];
                                            $icon = $iconMap[$log->event_type] ?? ['icon' => 'info-circle', 'color' => 'gray'];
                                        @endphp
                                        <div class="w-8 h-8 bg-{{ $icon['color'] }}-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-{{ $icon['icon'] }} text-{{ $icon['color'] }}-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ ucfirst(str_replace('_', ' ', $log->event_type)) }}
                                        </p>
                                        @if($log->event_data)
                                            <p class="text-sm text-gray-500">
                                                {{ is_array($log->event_data) ? json_encode($log->event_data) : $log->event_data }}
                                            </p>
                                        @endif
                                        <p class="text-xs text-gray-400">
                                            {{ $log->created_at->format('d.m.Y H:i:s') }} • IP: {{ $log->ip_address }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Нет записей активности</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Right sidebar -->
        <div class="space-y-6">
            
            <!-- Quick stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Быстрая статистика
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Всего событий:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->activityLogs->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Последнее событие:</span>
                        <span class="text-sm font-medium text-gray-900">
                            @if($user->activityLogs->first())
                                {{ $user->activityLogs->first()->created_at->diffForHumans() }}
                            @else
                                Нет
                            @endif
                        </span>
                    </div>
                    @if($user->playerProfile)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Создан профиль:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->playerProfile->created_at->format('d.m.Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Account info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-user-cog text-gray-600 mr-2"></i>
                    Информация об аккаунте
                </h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">ID:</span>
                        <span class="text-sm font-medium text-gray-900 ml-2">{{ $user->id }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Email подтвержден:</span>
                        @if($user->email_verified_at)
                            <span class="text-sm text-green-600 ml-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ $user->email_verified_at->format('d.m.Y H:i') }}
                            </span>
                        @else
                            <span class="text-sm text-red-600 ml-2">
                                <i class="fas fa-times-circle mr-1"></i>
                                Не подтвержден
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Аватар:</span>
                        <span class="text-sm text-gray-500 ml-2">{{ $user->avatar ? 'Загружен' : 'Не установлен' }}</span>
                    </div>
                    @if($user->deleted_at)
                        <div>
                            <span class="text-sm text-gray-600">Удален:</span>
                            <span class="text-sm text-red-600 ml-2">{{ $user->deleted_at->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
