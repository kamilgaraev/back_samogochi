@extends('admin.layouts.app')

@section('title', 'Редактировать микро-действие')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Редактировать микро-действие</h1>
            <p class="text-gray-600 mt-1">{{ $microAction->name }}</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.micro-actions.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border">
        <form method="POST" action="{{ route('admin.micro-actions.update', $microAction->id) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')
            
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Основная информация</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Название <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $microAction->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Например: Глубокое дыхание"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Описание <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                  placeholder="Подробное описание техники..."
                                  required>{{ old('description', $microAction->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Категория <span class="text-red-500">*</span>
                        </label>
                        <select id="category" 
                                name="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror"
                                required>
                            <option value="">Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->value }}" 
                                        {{ old('category', $microAction->category->value) === $category->value ? 'selected' : '' }}>
                                    {{ $category->getIcon() }} {{ $category->getLabel() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="unlock_level" class="block text-sm font-medium text-gray-700 mb-2">
                            Требуемый уровень <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="unlock_level" 
                               name="unlock_level" 
                               value="{{ old('unlock_level', $microAction->unlock_level) }}"
                               min="1" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unlock_level') border-red-500 @enderror"
                               required>
                        @error('unlock_level')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Rewards and Timing -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Награды и тайминги</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="energy_reward" class="block text-sm font-medium text-gray-700 mb-2">
                            Восстановление энергии <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="energy_reward" 
                                   name="energy_reward" 
                                   value="{{ old('energy_reward', $microAction->energy_reward) }}"
                                   min="0" 
                                   max="100"
                                   class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('energy_reward') border-red-500 @enderror"
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-bolt text-blue-500"></i>
                            </div>
                        </div>
                        @error('energy_reward')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="experience_reward" class="block text-sm font-medium text-gray-700 mb-2">
                            Опыт <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="experience_reward" 
                                   name="experience_reward" 
                                   value="{{ old('experience_reward', $microAction->experience_reward) }}"
                                   min="0" 
                                   max="100"
                                   class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('experience_reward') border-red-500 @enderror"
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-star text-green-500"></i>
                            </div>
                        </div>
                        @error('experience_reward')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="cooldown_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Кулдаун (минуты) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="cooldown_minutes" 
                                   name="cooldown_minutes" 
                                   value="{{ old('cooldown_minutes', $microAction->cooldown_minutes) }}"
                                   min="1" 
                                   max="1440"
                                   class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cooldown_minutes') border-red-500 @enderror"
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-clock text-orange-500"></i>
                            </div>
                        </div>
                        @error('cooldown_minutes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Настройки</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $microAction->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Активировать микро-действие
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Submit buttons -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.micro-actions.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Отмена
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Сохранить изменения
                </button>
            </div>
        </form>
    </div>

    <!-- Additional Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Информация о микро-действии</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">ID:</span>
                <span class="font-medium">{{ $microAction->id }}</span>
            </div>
            <div>
                <span class="text-gray-500">Создано:</span>
                <span class="font-medium">{{ $microAction->created_at->format('d.m.Y H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Обновлено:</span>
                <span class="font-medium">{{ $microAction->updated_at->format('d.m.Y H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Статус:</span>
                <span class="font-medium {{ $microAction->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $microAction->is_active ? 'Активно' : 'Неактивно' }}
                </span>
            </div>
        </div>
    </div>

</div>
@endsection
