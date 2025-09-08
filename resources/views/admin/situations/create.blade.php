@extends('admin.layouts.app')

@section('title', 'Создание ситуации')

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.situations.index') }}" 
       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
        <i class="fas fa-arrow-left mr-2"></i>Назад к списку
    </a>
</div>
@endsection

@section('content')
<div x-data="situationForm()" class="space-y-6">
    
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                Создание новой ситуации
            </h3>
            <p class="text-sm text-gray-600 mt-1">Заполните все поля для создания игровой ситуации</p>
        </div>
        
        <form method="POST" action="{{ route('admin.situations.store') }}" class="p-6 space-y-6">
            @csrf
            
            <!-- Basic info -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Title -->
                <div class="lg:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-1"></i>Название ситуации *
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                           placeholder="Например: Конфликт с коллегой на работе">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1"></i>Категория *
                    </label>
                    <select name="category" id="category" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror">
                        <option value="">Выберите категорию</option>
                        @foreach(\App\Enums\SituationCategory::cases() as $categoryOption)
                            <option value="{{ $categoryOption->value }}" 
                                    {{ old('category') == $categoryOption->value ? 'selected' : '' }}>
                                {{ $categoryOption->getIcon() }} {{ $categoryOption->getLabel() }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Difficulty Level -->
                <div>
                    <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-star mr-1"></i>Уровень сложности *
                    </label>
                    <select name="difficulty_level" id="difficulty_level" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('difficulty_level') border-red-500 @enderror">
                        <option value="">Выберите сложность</option>
                        @foreach(\App\Enums\DifficultyLevel::cases() as $difficultyOption)
                            <option value="{{ $difficultyOption->value }}" 
                                    {{ old('difficulty_level') == $difficultyOption->value ? 'selected' : '' }}>
                                {{ $difficultyOption->getIcon() }} {{ $difficultyOption->getLabel() }}
                            </option>
                        @endforeach
                    </select>
                    @error('difficulty_level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Min Level Required -->
                <div>
                    <label for="min_level_required" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-level-up-alt mr-1"></i>Минимальный уровень игрока
                    </label>
                    <input type="number" name="min_level_required" id="min_level_required" min="1" max="100"
                           value="{{ old('min_level_required', 1) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('min_level_required') border-red-500 @enderror">
                    @error('min_level_required')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Status -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700">
                            <i class="fas fa-eye mr-1"></i>Активировать сразу
                        </span>
                    </label>
                </div>
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-align-left mr-1"></i>Описание ситуации *
                </label>
                <textarea name="description" id="description" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="Подробно опишите ситуацию, с которой столкнется игрок...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Game Impact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Stress Impact -->
                <div>
                    <label for="stress_impact" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heartbeat mr-1"></i>Влияние на стресс *
                    </label>
                    <input type="number" name="stress_impact" id="stress_impact" min="-50" max="50" required
                           value="{{ old('stress_impact', 0) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('stress_impact') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">От -50 до +50. Отрицательные значения снижают стресс</p>
                    @error('stress_impact')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Experience Reward -->
                <div>
                    <label for="experience_reward" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-trophy mr-1"></i>Награда опытом *
                    </label>
                    <input type="number" name="experience_reward" id="experience_reward" min="1" max="100" required
                           value="{{ old('experience_reward', 10) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('experience_reward') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">От 1 до 100 очков опыта</p>
                    @error('experience_reward')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Options Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list-ul text-blue-600 mr-2"></i>
                        Варианты действий
                    </h4>
                    <button type="button" @click="addOption()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Добавить вариант
                    </button>
                </div>
                
                <div class="space-y-4">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="text-md font-medium text-gray-800">
                                    Вариант <span x-text="index + 1"></span>
                                </h5>
                                <button type="button" @click="removeOption(index)" x-show="options.length > 1"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Option Text -->
                                <div>
                                    <label :for="'option_text_' + index" class="block text-sm font-medium text-gray-700 mb-1">
                                        Текст варианта *
                                    </label>
                                    <textarea :name="'options[' + index + '][text]'" :id="'option_text_' + index" 
                                              x-model="option.text" rows="2" required
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Например: Открыто поговорить с коллегой о проблеме"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Stress Change -->
                                    <div>
                                        <label :for="'option_stress_' + index" class="block text-sm font-medium text-gray-700 mb-1">
                                            Изменение стресса *
                                        </label>
                                        <input type="number" :name="'options[' + index + '][stress_change]'" 
                                               :id="'option_stress_' + index" x-model="option.stress_change" 
                                               min="-50" max="50" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <!-- Experience Reward -->
                                    <div>
                                        <label :for="'option_exp_' + index" class="block text-sm font-medium text-gray-700 mb-1">
                                            Награда опытом *
                                        </label>
                                        <input type="number" :name="'options[' + index + '][experience_reward]'" 
                                               :id="'option_exp_' + index" x-model="option.experience_reward" 
                                               min="0" max="100" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <!-- Energy Cost -->
                                    <div>
                                        <label :for="'option_energy_' + index" class="block text-sm font-medium text-gray-700 mb-1">
                                            Затраты энергии
                                        </label>
                                        <input type="number" :name="'options[' + index + '][energy_cost]'" 
                                               :id="'option_energy_' + index" x-model="option.energy_cost" 
                                               min="0" max="50"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <!-- Min Level -->
                                    <div>
                                        <label :for="'option_level_' + index" class="block text-sm font-medium text-gray-700 mb-1">
                                            Мин. уровень
                                        </label>
                                        <input type="number" :name="'options[' + index + '][min_level_required]'" 
                                               :id="'option_level_' + index" x-model="option.min_level_required" 
                                               min="1" max="100"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Submit buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.situations.index') }}" 
                   class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i>Отмена
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Создать ситуацию
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function situationForm() {
    return {
        options: [
            {
                text: '',
                stress_change: 0,
                experience_reward: 10,
                energy_cost: 0,
                min_level_required: 1
            }
        ],
        
        addOption() {
            this.options.push({
                text: '',
                stress_change: 0,
                experience_reward: 10,
                energy_cost: 0,
                min_level_required: 1
            });
        },
        
        removeOption(index) {
            if (this.options.length > 1) {
                this.options.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
