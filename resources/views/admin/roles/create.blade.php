@extends('admin.layouts.app')

@section('title', 'Создать новую роль')

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.roles.index') }}" 
       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
        <i class="fas fa-arrow-left mr-2"></i>Назад к ролям
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Header info -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold">🆕 Создание новой роли</h1>
        <p class="text-green-100 mt-1">
            Определите права доступа для новой роли в системе RBAC
        </p>
    </div>

    <!-- Create Role Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-user-shield text-green-600 mr-2"></i>
                Информация о роли
            </h3>
        </div>
        
        <form method="POST" action="{{ route('admin.roles.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 border-b pb-2">Основная информация</h4>
                    
                    <!-- Role Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Системное имя роли*
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="e.g. content-manager"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Только латинские буквы, цифры и дефисы</p>
                    </div>
                    
                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Отображаемое имя*
                        </label>
                        <input type="text" 
                               id="display_name" 
                               name="display_name" 
                               value="{{ old('display_name') }}"
                               placeholder="e.g. Контент-менеджер"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('display_name') border-red-500 @enderror"
                               required>
                        @error('display_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Описание роли
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Краткое описание функций и ответственности"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                            Приоритет роли*
                        </label>
                        <select id="priority" 
                                name="priority" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-500 @enderror"
                                required>
                            <option value="">-- Выберите приоритет --</option>
                            <option value="10" {{ old('priority') == '10' ? 'selected' : '' }}>10 - Низкий</option>
                            <option value="25" {{ old('priority') == '25' ? 'selected' : '' }}>25 - Стандартный</option>
                            <option value="50" {{ old('priority') == '50' ? 'selected' : '' }}>50 - Высокий</option>
                            <option value="75" {{ old('priority') == '75' ? 'selected' : '' }}>75 - Очень высокий</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Высший приоритет (100) зарезервирован для Super Admin</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Активная роль</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Неактивные роли нельзя назначать пользователям</p>
                    </div>
                </div>
                
                <!-- Permissions -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900 border-b pb-2">Разрешения</h4>
                    
                    @php
                        $permissionsByCategory = collect($permissions)->groupBy('category');
                    @endphp
                    
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-medium text-gray-900 capitalize">
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
                                        <i class="fas {{ $icon }} mr-2 text-blue-600"></i>
                                        {{ ucfirst($category) }}
                                    </h5>
                                    <button type="button" 
                                            onclick="toggleCategoryPermissions('{{ $category }}')"
                                            class="text-xs text-blue-600 hover:text-blue-800">
                                        Выбрать все
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($categoryPermissions as $permission)
                                        <label class="flex items-center p-2 hover:bg-gray-50 rounded">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   data-category="{{ $category }}"
                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <div class="ml-3">
                                                <span class="text-sm font-medium text-gray-900">{{ $permission->display_name }}</span>
                                                @if($permission->description)
                                                    <p class="text-xs text-gray-500">{{ $permission->description }}</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.roles.index') }}" 
                   class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Отмена
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Создать роль
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>
            Рекомендации по созданию ролей
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
            <div>
                <h4 class="font-medium mb-2">✅ Хорошие практики:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Назначайте минимально необходимые права</li>
                    <li>Используйте понятные названия ролей</li>
                    <li>Группируйте права по функциональности</li>
                    <li>Документируйте назначение роли</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium mb-2">⚠️ Избегайте:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Слишком широких прав доступа</li>
                    <li>Дублирования существующих ролей</li>
                    <li>Конфликтующих разрешений</li>
                    <li>Ролей для одного пользователя</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<script>
function toggleCategoryPermissions(category) {
    const checkboxes = document.querySelectorAll(`input[data-category="${category}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}
</script>

@endsection
