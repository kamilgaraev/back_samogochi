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
    
    <!-- Flash messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                <p class="text-red-800 font-medium">Ошибка валидации:</p>
            </div>
            <ul class="ml-8 list-disc text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
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

                <!-- Edit Player Metrics -->
                @can('users.manage-roles')
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-sliders-h text-indigo-600 mr-2"></i>
                            Редактирование показателей игрока
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Измените метрики для тестирования или коррекции</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.users.update-metrics', $user->id) }}" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Total Experience -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-star text-green-500 mr-1"></i>
                                        Общий опыт (0-100000)
                                    </label>
                                    <input type="number" name="total_experience" 
                                           id="total_experience"
                                           value="{{ $user->playerProfile->total_experience }}"
                                           min="0" max="100000" step="1"
                                           onchange="updateCalculatedLevel()"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="text-xs text-gray-500 mt-1">Уровень рассчитается автоматически: каждые 100 опыта = 1 уровень</p>
                                </div>

                                <!-- Level (calculated) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-level-up-alt text-blue-500 mr-1"></i>
                                        Уровень (рассчитывается автоматически)
                                    </label>
                                    <input type="number" name="level" 
                                           id="calculated_level"
                                           value="{{ $user->playerProfile->level }}"
                                           readonly
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed">
                                    <p class="text-xs text-indigo-600 mt-1">
                                        <i class="fas fa-calculator mr-1"></i>
                                        Будет: <span id="level_preview">{{ \App\Services\GameConfigService::calculateLevelFromExperience($user->playerProfile->total_experience) }}</span>
                                    </p>
                                </div>

                                <!-- Energy -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                                        Энергия (0-500)
                                    </label>
                                    <input type="number" name="energy" 
                                           value="{{ $user->playerProfile->energy }}"
                                           min="0" max="500" step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="text-xs text-gray-500 mt-1">Текущий: {{ $user->playerProfile->energy }}</p>
                                </div>

                                <!-- Stress -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-heartbeat text-red-500 mr-1"></i>
                                        Стресс (0-100)
                                    </label>
                                    <input type="number" name="stress" 
                                           value="{{ $user->playerProfile->stress }}"
                                           min="0" max="100" step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-red-500 h-2 rounded-full transition-all" 
                                             style="width: {{ $user->playerProfile->stress }}%"></div>
                                    </div>
                                </div>

                                <!-- Anxiety -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-brain text-orange-500 mr-1"></i>
                                        Тревожность (0-100)
                                    </label>
                                    <input type="number" name="anxiety" 
                                           value="{{ $user->playerProfile->anxiety }}"
                                           min="0" max="100" step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-orange-500 h-2 rounded-full transition-all" 
                                             style="width: {{ $user->playerProfile->anxiety }}%"></div>
                                    </div>
                                </div>

                                <!-- Consecutive Days -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-check text-purple-500 mr-1"></i>
                                        Дней подряд (0-365)
                                    </label>
                                    <input type="number" name="consecutive_days" 
                                           value="{{ $user->playerProfile->consecutive_days }}"
                                           min="0" max="365" step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Изменения будут применены немедленно
                                </div>
                                <button type="submit" 
                                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Сохранить изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan
            @else
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <i class="fas fa-user-slash text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">У пользователя нет игрового профиля</p>
                </div>
            @endif
            
            <!-- Player Statistics -->
            @if($user->playerProfile)
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-chart-bar text-purple-600 mr-2"></i>
                            Детальная статистика
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-blue-700 font-medium">Ситуаций</span>
                                    <i class="fas fa-puzzle-piece text-blue-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-blue-900">
                                    {{ \DB::table('player_situations')->where('player_id', $user->playerProfile->id)->count() }}
                                </p>
                                <p class="text-xs text-blue-600 mt-1">всего завершено</p>
                            </div>

                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-green-700 font-medium">Микро-действий</span>
                                    <i class="fas fa-bolt text-green-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-green-900">
                                    {{ \DB::table('player_micro_actions')->where('player_id', $user->playerProfile->id)->count() }}
                                </p>
                                <p class="text-xs text-green-600 mt-1">всего выполнено</p>
                            </div>

                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-purple-700 font-medium">Время в игре</span>
                                    <i class="fas fa-clock text-purple-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-purple-900">
                                    {{ $user->playerProfile->created_at->diffInDays(now()) }}
                                </p>
                                <p class="text-xs text-purple-600 mt-1">дней с регистрации</p>
                            </div>

                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-yellow-700 font-medium">Среднее за день</span>
                                    <i class="fas fa-calendar-day text-yellow-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-yellow-900">
                                    @php
                                        $days = max(1, $user->playerProfile->created_at->diffInDays(now()));
                                        $total = \DB::table('player_situations')->where('player_id', $user->playerProfile->id)->count();
                                        $avg = round($total / $days, 1);
                                    @endphp
                                    {{ $avg }}
                                </p>
                                <p class="text-xs text-yellow-600 mt-1">ситуаций в день</p>
                            </div>

                            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-red-700 font-medium">Статус стресса</span>
                                    <i class="fas fa-heart-pulse text-red-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-red-900">
                                    @if($user->playerProfile->stress < 30)
                                        Низкий
                                    @elseif($user->playerProfile->stress < 60)
                                        Средний
                                    @else
                                        Высокий
                                    @endif
                                </p>
                                <p class="text-xs text-red-600 mt-1">{{ $user->playerProfile->stress }}% стресса</p>
                            </div>

                            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-indigo-700 font-medium">Активность</span>
                                    <i class="fas fa-fire text-indigo-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-indigo-900">
                                    @php
                                        $lastWeek = \DB::table('player_situations')
                                            ->where('player_id', $user->playerProfile->id)
                                            ->where('created_at', '>=', now()->subWeek())
                                            ->count();
                                    @endphp
                                    {{ $lastWeek }}
                                </p>
                                <p class="text-xs text-indigo-600 mt-1">за последнюю неделю</p>
                            </div>
                        </div>
                    </div>
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
            
            <!-- User Roles -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user-shield text-purple-600 mr-2"></i>
                        Роли пользователя
                    </h3>
                    @can('users.manage-roles')
                        @if($user->id !== auth()->id())
                            <button x-data @click="$refs.roleModal.showModal()" 
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit mr-1"></i>Управлять ролями
                            </button>
                        @endif
                    @endcan
                </div>
                
                @if($user->roles->count() > 0)
                    <div class="space-y-3">
                        @foreach($user->roles->sortByDesc('priority') as $role)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    @php
                                        $roleColors = [
                                            'super-admin' => 'bg-red-100 text-red-800',
                                            'admin' => 'bg-blue-100 text-blue-800',
                                            'moderator' => 'bg-green-100 text-green-800'
                                        ];
                                        $color = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <div class="w-8 h-8 {{ $color }} rounded-full flex items-center justify-center text-xs font-bold mr-3">
                                        {{ strtoupper(substr($role->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $role->display_name }}</p>
                                        <p class="text-xs text-gray-500">Приоритет: {{ $role->priority }}</p>
                                    </div>
                                </div>
                                @can('users.manage-roles')
                                    @if($user->id !== auth()->id() || $role->name !== 'super-admin')
                                        <form method="POST" action="{{ route('admin.users.remove-role', [$user->id, $role->id]) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    onclick="return confirm('Отозвать роль {{ $role->display_name }}?')"
                                                    class="text-red-600 hover:text-red-800 text-xs">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-slash text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm">Роли не назначены</p>
                        @if($user->is_admin)
                            <p class="text-blue-600 text-xs mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Используется legacy флаг is_admin
                            </p>
                        @endif
                    </div>
                @endif
                
                <!-- User permissions summary -->
                @if($user->roles->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-medium text-gray-700 mb-2">Права доступа:</p>
                        <div class="flex flex-wrap gap-1">
                            @php
                                $permissions = $user->getAllPermissions()->groupBy('category');
                            @endphp
                            @foreach($permissions as $category => $categoryPermissions)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $category }}: {{ $categoryPermissions->count() }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
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

<!-- Role Management Modal -->
@can('users.manage-roles')
<dialog x-ref="roleModal" class="backdrop:bg-black backdrop:bg-opacity-50 rounded-lg p-0 border-0 shadow-xl">
    <div class="bg-white rounded-lg w-96 max-w-full">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-shield text-purple-600 mr-2"></i>
                Управление ролями
            </h3>
            <button @click="$refs.roleModal.close()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">
                Назначить роль пользователю: <strong>{{ $user->name }}</strong>
            </p>
            
            <form method="POST" action="{{ route('admin.users.assign-role', $user->id) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Выберите роль для назначения:
                        </label>
                        <select name="role_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Выберите роль --</option>
                            @foreach($availableRoles as $role)
                                @if(!$user->hasRole($role))
                                    <option value="{{ $role->id }}">
                                        {{ $role->display_name }} (Приоритет: {{ $role->priority }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    @if($availableRoles->filter(fn($role) => !$user->hasRole($role))->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-600">Все доступные роли уже назначены</p>
                        </div>
                    @endif
                </div>
                
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" @click="$refs.roleModal.close()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Отмена
                    </button>
                    @if(!$availableRoles->filter(fn($role) => !$user->hasRole($role))->isEmpty())
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Назначить роль
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</dialog>
@endcan

@endsection

@push('scripts')
<script>
const LEVEL_REQUIREMENTS = @json(\App\Services\GameConfigService::getLevelRequirements());

function calculateLevelFromExperience(exp) {
    if (!LEVEL_REQUIREMENTS || LEVEL_REQUIREMENTS.length === 0) {
        return Math.floor(exp / 100) + 1;
    }
    
    let level = 1;
    for (const req of LEVEL_REQUIREMENTS) {
        if (exp >= req.experience) {
            level = req.level;
        } else {
            break;
        }
    }
    return level;
}

function updateCalculatedLevel() {
    const experience = parseInt(document.getElementById('total_experience').value) || 0;
    const calculatedLevel = calculateLevelFromExperience(experience);
    
    document.getElementById('calculated_level').value = calculatedLevel;
    document.getElementById('level_preview').textContent = calculatedLevel;
}

document.addEventListener('DOMContentLoaded', function() {
    const experienceInput = document.getElementById('total_experience');
    if (experienceInput) {
        experienceInput.addEventListener('input', updateCalculatedLevel);
        experienceInput.addEventListener('change', updateCalculatedLevel);
    }
});
</script>
@endpush
