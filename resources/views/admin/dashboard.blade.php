@extends('admin.layouts.app')

@section('title', 'Дашборд')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold">Добро пожаловать, {{ auth()->user()->name }}! 👋</h1>
        <p class="text-blue-100 mt-1">Обзор системы на {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Всего пользователей</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chart-line text-green-500 mr-1"></i>
                    <span>Активных за неделю: {{ $stats['active_users'] }}</span>
                </div>
            </div>
        </div>

        <!-- Total Situations -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-puzzle-piece text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Всего ситуаций</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_situations']) }}</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-eye text-blue-500 mr-1"></i>
                    <span>Активных: {{ $stats['active_situations'] }}</span>
                </div>
            </div>
        </div>

        <!-- Game Sessions Today -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-gamepad text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Сессий сегодня</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['data']['sessions_today'] ?? 0 }}</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-clock text-orange-500 mr-1"></i>
                    <span>Средняя длительность: 15 мин</span>
                </div>
            </div>
        </div>

        <!-- Daily Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-bolt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Действий сегодня</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['data']['actions_today'] ?? 0 }}</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span>+12% от вчера</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                        Новые пользователи
                    </h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        Все пользователи <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($stats['recent_users'] as $user)
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                                @if($user->playerProfile)
                                    <p class="text-xs text-green-600">Level {{ $user->playerProfile->level }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Пока нет новых пользователей</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-rocket text-purple-600 mr-2"></i>
                    Быстрые действия
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Create Situation -->
                    <a href="{{ route('admin.situations.create') }}" 
                       class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                        <div class="text-center">
                            <i class="fas fa-plus-circle text-2xl text-gray-400 group-hover:text-blue-500"></i>
                            <p class="mt-2 text-sm font-medium text-gray-700 group-hover:text-blue-600">
                                Создать ситуацию
                            </p>
                        </div>
                    </a>

                    <!-- View Configs -->
                    <a href="{{ route('admin.configs.index') }}" 
                       class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors group">
                        <div class="text-center">
                            <i class="fas fa-cogs text-2xl text-gray-400 group-hover:text-green-500"></i>
                            <p class="mt-2 text-sm font-medium text-gray-700 group-hover:text-green-600">
                                Настройки игры
                            </p>
                        </div>
                    </a>

                    <!-- View API Docs -->
                    <a href="/docs" target="_blank"
                       class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors group">
                        <div class="text-center">
                            <i class="fas fa-book text-2xl text-gray-400 group-hover:text-purple-500"></i>
                            <p class="mt-2 text-sm font-medium text-gray-700 group-hover:text-purple-600">
                                API документация
                            </p>
                        </div>
                    </a>

                    <!-- View Analytics -->
                    <a href="#" 
                       class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors group">
                        <div class="text-center">
                            <i class="fas fa-chart-bar text-2xl text-gray-400 group-hover:text-orange-500"></i>
                            <p class="mt-2 text-sm font-medium text-gray-700 group-hover:text-orange-600">
                                Аналитика
                            </p>
                        </div>
                    </a>

                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-server text-green-600 mr-2"></i>
                Статус системы
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- API Status -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">API Сервер</p>
                        <p class="text-xs text-gray-500">Работает нормально</p>
                    </div>
                </div>

                <!-- Database -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">База данных</p>
                        <p class="text-xs text-gray-500">Подключена</p>
                    </div>
                </div>

                <!-- Redis -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Redis</p>
                        <p class="text-xs text-gray-500">Активен</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
