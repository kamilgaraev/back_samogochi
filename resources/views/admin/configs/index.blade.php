@extends('admin.layouts.app')

@section('title', 'Настройки игры')

@section('content')
<div class="space-y-6">
    
    <!-- Header info -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold">⚙️ Настройки игры</h1>
        <p class="text-purple-100 mt-1">
            Управление глобальными параметрами игрового баланса
        </p>
    </div>

    <!-- Config cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        @foreach($configs as $config)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            @php
                                $icons = [
                                    'game_balance' => 'fas fa-balance-scale text-green-600',
                                    'level_requirements' => 'fas fa-star text-yellow-600',
                                    'notification_settings' => 'fas fa-bell text-blue-600',
                                    'energy_settings' => 'fas fa-battery-three-quarters text-orange-600',
                                    'stress_settings' => 'fas fa-heartbeat text-red-600'
                                ];
                                $icon = $icons[$config->key] ?? 'fas fa-cog text-gray-600';
                            @endphp
                            <i class="{{ $icon }} mr-2"></i>
                            {{ ucwords(str_replace('_', ' ', $config->key)) }}
                        </h3>
                        
                        @if($config->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Активно
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-pause-circle mr-1"></i> Неактивно
                            </span>
                        @endif
                    </div>
                    
                    @if($config->description)
                        <p class="text-sm text-gray-600 mt-2">{{ $config->description }}</p>
                    @endif
                </div>
                
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.configs.update', $config->key) }}" 
                          x-data="configForm('{{ $config->key }}', {{ json_encode($config->value) }})" 
                          @submit="updateConfig">
                        @csrf
                        @method('PATCH')
                        
                        <!-- JSON Value Display/Edit -->
                        <div class="space-y-4">
                            
                            @if($config->key == 'game_balance')
                                <!-- Game Balance specific fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Ежедневный опыт за вход
                                        </label>
                                        <input type="number" x-model="config.daily_login_experience" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Максимальная энергия
                                        </label>
                                        <input type="number" x-model="config.max_energy" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Восстановление энергии/час
                                        </label>
                                        <input type="number" x-model="config.energy_regen_per_hour" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Высокий уровень стресса
                                        </label>
                                        <input type="number" x-model="config.stress_threshold_high" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Низкий уровень стресса
                                        </label>
                                        <input type="number" x-model="config.stress_threshold_low" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Откат ситуаций (часы)
                                        </label>
                                        <input type="number" x-model="config.situation_cooldown_hours" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            
                            @elseif($config->key == 'level_requirements')
                                <!-- Level Requirements -->
                                <div class="space-y-3">
                                    <template x-for="(level, index) in config" :key="index">
                                        <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Уровень <span x-text="level.level"></span>
                                                </label>
                                                <input type="number" x-model="level.level" readonly
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Требуемый опыт
                                                </label>
                                                <input type="number" x-model="level.experience"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <button type="button" @click="addLevel()" 
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Добавить уровень
                                    </button>
                                </div>
                            
                            @else
                                <!-- Raw JSON editor for other configs -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Значение конфигурации (JSON)
                                    </label>
                                    <textarea name="value" rows="6" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                              x-text="JSON.stringify(config, null, 2)"
                                              @input="config = JSON.parse($event.target.value)"></textarea>
                                </div>
                            @endif
                            
                            <!-- Hidden input for actual value -->
                            <input type="hidden" name="value" :value="JSON.stringify(config)">
                            
                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Описание
                                </label>
                                <textarea name="description" rows="2" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Краткое описание настройки...">{{ $config->description }}</textarea>
                            </div>
                            
                            <!-- Active status -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        Активировать конфигурацию
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="mt-6 flex items-center justify-end">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Config meta info -->
                <div class="px-6 pb-6">
                    <div class="text-xs text-gray-500 space-y-1">
                        <p>Создано: {{ $config->created_at->format('d.m.Y H:i') }}</p>
                        <p>Обновлено: {{ $config->updated_at->format('d.m.Y H:i') }}</p>
                        @if($config->creator)
                            <p>Автор: {{ $config->creator->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($configs->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-cogs text-4xl text-gray-300 mb-4"></i>
            <p class="text-lg font-medium text-gray-900 mb-2">Конфигурации не найдены</p>
            <p class="text-gray-500">
                Конфигурации создаются автоматически через сидеры базы данных
            </p>
        </div>
    @endif
</div>

<script>
function configForm(key, initialValue) {
    return {
        config: initialValue,
        
        addLevel() {
            if (key === 'level_requirements') {
                const maxLevel = Math.max(...this.config.map(l => l.level));
                const lastExp = this.config.find(l => l.level === maxLevel).experience;
                
                this.config.push({
                    level: maxLevel + 1,
                    experience: lastExp + 200
                });
            }
        },
        
        updateConfig(event) {
            // Validation can be added here
            return true;
        }
    }
}
</script>

@endsection
