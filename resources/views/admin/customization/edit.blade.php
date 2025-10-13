@extends('admin.layouts.app')

@section('title', 'Редактировать элемент кастомизации')

@section('content')
<div class="space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Редактировать элемент кастомизации</h1>
            <p class="text-gray-600 mt-1">{{ $item->name }}</p>
        </div>
        
        <a href="{{ route('admin.customization.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Назад к списку
        </a>
    </div>

    <div class="bg-white shadow-sm rounded-lg border">
        <form method="POST" action="{{ route('admin.customization.update', $item->id) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')
            
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
                               value="{{ old('name', $item->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Описание
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $item->description) }}</textarea>
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
                            @foreach($categories as $category)
                                <option value="{{ $category->value }}" 
                                        {{ old('category', $item->category) === $category->value ? 'selected' : '' }}>
                                    {{ $category->getIcon() }} {{ $category->getLabel() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Ключ категории <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="category_key" 
                               name="category_key" 
                               value="{{ old('category_key', $item->category_key) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_key') border-red-500 @enderror"
                               required>
                        <p class="text-sm text-gray-500 mt-1">Уникальный идентификатор группы</p>
                        @error('category_key')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="unlock_level" class="block text-sm font-medium text-gray-700 mb-2">
                            Уровень разблокировки <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="unlock_level" 
                               name="unlock_level" 
                               value="{{ old('unlock_level', $item->unlock_level) }}"
                               min="1" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unlock_level') border-red-500 @enderror"
                               required>
                        @error('unlock_level')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            Порядок сортировки
                        </label>
                        <input type="number" 
                               id="order" 
                               name="order" 
                               value="{{ old('order', $item->order) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('order') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Чем меньше значение, тем выше в списке</p>
                        @error('order')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL изображения
                        </label>
                        <input type="text" 
                               id="image_url" 
                               name="image_url" 
                               value="{{ old('image_url', $item->image_url) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('image_url') border-red-500 @enderror">
                        @error('image_url')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Настройки</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_default" 
                               name="is_default" 
                               value="1"
                               {{ old('is_default', $item->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 block text-sm text-gray-900">
                            Элемент по умолчанию (выдается всем игрокам сразу)
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Активный элемент
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.customization.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Отмена
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Сохранить изменения
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

