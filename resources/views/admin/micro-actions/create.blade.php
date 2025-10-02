@extends('admin.layouts.app')

@section('title', 'Создать микро-действие')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Создать микро-действие</h1>
            <p class="text-gray-600 mt-1">Добавление новой техники снятия стресса</p>
        </div>
        
        <a href="{{ route('admin.micro-actions.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Назад к списку
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border">
        <form method="POST" action="{{ route('admin.micro-actions.store') }}" class="p-6 space-y-6">
            @csrf
            
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
                               value="{{ old('name') }}"
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
                                  required>{{ old('description') }}</textarea>
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
                                        {{ old('category') === $category->value ? 'selected' : '' }}>
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
                               value="{{ old('unlock_level', 1) }}"
                               min="1" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unlock_level') border-red-500 @enderror"
                               required>
                        @error('unlock_level')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                            Платформа <span class="text-red-500">*</span>
                        </label>
                        <select id="position" 
                                name="position"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('position') border-red-500 @enderror"
                                required>
                            <option value="">Выберите позицию</option>
                            <option value="phone" {{ old('position') === 'phone' ? 'selected' : '' }}>📱 Телефон</option>
                            <option value="tablet" {{ old('position') === 'tablet' ? 'selected' : '' }}>📊 Планшет</option>
                            <option value="desktop" {{ old('position', 'desktop') === 'desktop' ? 'selected' : '' }}>💻 Компьютер</option>
                            <option value="tv" {{ old('position') === 'tv' ? 'selected' : '' }}>📺 Телевизор</option>
                            <option value="speaker" {{ old('position') === 'speaker' ? 'selected' : '' }}>🔊 Колонка</option>
                            <option value="bookshelf" {{ old('position') === 'bookshelf' ? 'selected' : '' }}>📚 Книжная полка</option>
                            <option value="kitchen" {{ old('position') === 'kitchen' ? 'selected' : '' }}>🍳 Кухня</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Рекомендуемая платформа для выполнения действия</p>
                        @error('position')
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
                                   value="{{ old('energy_reward', 10) }}"
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
                                   value="{{ old('experience_reward', 5) }}"
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
                                   value="{{ old('cooldown_minutes', 60) }}"
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
                               {{ old('is_active', true) ? 'checked' : '' }}
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
                    <i class="fas fa-save mr-2"></i>Создать микро-действие
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const energyInput = document.getElementById('energy_reward');
    const cooldownInput = document.getElementById('cooldown_minutes');
    
    // Auto-fill typical values based on category
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        
        if (category && energyInput.value == energyInput.defaultValue) {
            const typicalValues = {
                'relaxation': { energy: 15, cooldown: 10 },
                'exercise': { energy: 20, cooldown: 30 },
                'creativity': { energy: 10, cooldown: 45 },
                'social': { energy: 12, cooldown: 60 }
            };
            
            if (typicalValues[category]) {
                energyInput.value = typicalValues[category].energy;
                cooldownInput.value = typicalValues[category].cooldown;
            }
        }
    });
});
</script>
@endsection
