@extends('admin.layouts.app')

@section('title', 'Редактировать ситуацию')

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.situations.index') }}" 
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
        <i class="fas fa-arrow-left mr-2"></i>Назад к ситуациям
    </a>
    @can('situations.delete')
        <form method="POST" action="{{ route('admin.situations.destroy', $situation->id) }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    onclick="return confirm('Вы уверены, что хотите удалить эту ситуацию?')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Удалить
            </button>
        </form>
    @endcan
</div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Header info -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold">✏️ Редактирование ситуации</h1>
        <p class="text-orange-100 mt-1">
            ID: {{ $situation->id }} | Создана: {{ $situation->created_at->format('d.m.Y H:i') }}
        </p>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-puzzle-piece text-orange-600 mr-2"></i>
                Информация о ситуации
            </h3>
        </div>
        
        <form method="POST" action="{{ route('admin.situations.update', $situation->id) }}" class="p-6">
            @csrf
            @method('PATCH')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 border-b pb-2">Основная информация</h4>
                    
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Название ситуации*
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $situation->title) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('title') border-red-500 @enderror"
                               required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Описание ситуации*
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('description') border-red-500 @enderror"
                                  required>{{ old('description', $situation->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                            Категория*
                        </label>
                        <select id="category" 
                                name="category" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('category') border-red-500 @enderror"
                                required>
                            <option value="">-- Выберите категорию --</option>
                        @foreach(\App\Enums\SituationCategory::cases() as $categoryOption)
                            <option value="{{ $categoryOption->value }}" 
                                    {{ old('category', $situation->category) == $categoryOption->value ? 'selected' : '' }}>
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
                        <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-1">
                            Уровень сложности*
                        </label>
                        <select id="difficulty_level" 
                                name="difficulty_level" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('difficulty_level') border-red-500 @enderror"
                                required>
                            <option value="">-- Выберите сложность --</option>
                        @foreach(\App\Enums\DifficultyLevel::cases() as $difficultyOption)
                            <option value="{{ $difficultyOption->value }}" 
                                    {{ old('difficulty_level', $situation->difficulty_level) == $difficultyOption->value ? 'selected' : '' }}>
                                {{ $difficultyOption->getIcon() }} {{ $difficultyOption->getLabel() }}
                            </option>
                        @endforeach
                        </select>
                        @error('difficulty_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Game Parameters -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 border-b pb-2">Игровые параметры</h4>
                    
                    <!-- Experience Reward -->
                    <div>
                        <label for="experience_reward" class="block text-sm font-medium text-gray-700 mb-1">
                            Награда за опыт*
                        </label>
                        <input type="number" 
                               id="experience_reward" 
                               name="experience_reward" 
                               value="{{ old('experience_reward', $situation->experience_reward) }}"
                               min="0" 
                               max="10000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('experience_reward') border-red-500 @enderror"
                               required>
                        @error('experience_reward')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Stress Impact -->
                    <div>
                        <label for="stress_impact" class="block text-sm font-medium text-gray-700 mb-1">
                            Влияние на стресс*
                        </label>
                        <input type="number" 
                               id="stress_impact" 
                               name="stress_impact" 
                               value="{{ old('stress_impact', $situation->stress_impact) }}"
                               min="-50" 
                               max="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('stress_impact') border-red-500 @enderror"
                               required>
                        @error('stress_impact')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Отрицательные значения уменьшают стресс</p>
                    </div>
                    
                    <!-- Energy Cost -->
                    <div>
                        <label for="energy_cost" class="block text-sm font-medium text-gray-700 mb-1">
                            Стоимость энергии*
                        </label>
                        <input type="number" 
                               id="energy_cost" 
                               name="energy_cost" 
                               value="{{ old('energy_cost', $situation->energy_cost) }}"
                               min="0" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('energy_cost') border-red-500 @enderror"
                               required>
                        @error('energy_cost')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $situation->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Ситуация активна</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_random" 
                                   value="1"
                                   {{ old('is_random', $situation->is_random) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Случайная ситуация</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.situations.index') }}" 
                   class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Отмена
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-orange-600 text-white hover:bg-orange-700 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Сохранить изменения
                </button>
            </div>
        </form>
    </div>

    <!-- Situation Options -->
    @if($situation->options->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Варианты ответов ({{ $situation->options->count() }})
                </h3>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($situation->options as $option)
                        <div class="flex items-start p-4 border border-gray-200 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-800 font-bold text-sm mr-4 flex-shrink-0">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $option->text }}</p>
                                @if($option->result_text)
                                    <p class="text-xs text-gray-600 mt-1">Результат: {{ $option->result_text }}</p>
                                @endif
                                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                    <span>Опыт: +{{ $option->experience_modifier }}</span>
                                    <span>Стресс: {{ $option->stress_modifier > 0 ? '+' : '' }}{{ $option->stress_modifier }}</span>
                                    <span>Энергия: {{ $option->energy_modifier > 0 ? '+' : '' }}{{ $option->energy_modifier }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Редактирование вариантов ответов пока не реализовано. Используйте API или базу данных для изменения опций.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
