@extends('admin.layouts.app')

@section('title', 'Кастомизация')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Элементы кастомизации</h1>
            <p class="text-gray-600 mt-1">Управление гардеробом и мебелью</p>
        </div>
        
        @can('configs.edit')
        <a href="{{ route('admin.customization.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Добавить элемент
        </a>
        @endcan
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
                <select name="category" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->value }}" {{ (request('category') === $category->value) ? 'selected' : '' }}>
                            {{ $category->getIcon() }} {{ $category->getLabel() }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="is_active" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Все</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Активные</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Неактивные</option>
                </select>
            </div>

            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Уровень</label>
                <input type="number" name="unlock_level" value="{{ request('unlock_level') }}" 
                       placeholder="Уровень разблокировки"
                       class="w-full rounded-md border-gray-300 shadow-sm">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    <i class="fas fa-search mr-2"></i>Фильтр
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalItems = collect($items)->count();
            $activeItems = collect($items)->where('is_active', true)->count();
            $categoryStats = collect($items)->groupBy('category');
        @endphp
        
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-tshirt text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Всего элементов</p>
                    <p class="text-xl font-semibold">{{ $totalItems }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Активных</p>
                    <p class="text-xl font-semibold">{{ $activeItems }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-tags text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Категорий</p>
                    <p class="text-xl font-semibold">{{ $categoryStats->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-star text-orange-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">По умолчанию</p>
                    <p class="text-xl font-semibold">{{ collect($items)->where('is_default', true)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Название
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Категория
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ключ категории
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Уровень
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Порядок
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Статус
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-10 h-10 rounded mr-3 object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded mr-3 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->name }}
                                            @if($item->is_default)
                                                <span class="ml-1 text-xs text-blue-600">⭐</span>
                                            @endif
                                        </div>
                                        @if($item->description)
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($item->description, 40) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $categoryEnum = \App\Enums\CustomizationCategory::from($item->category);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $categoryEnum->getIcon() }} {{ $categoryEnum->getLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $item->category_key }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Ур. {{ $item->unlock_level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->order }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Активно
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Неактивно
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @can('configs.edit')
                                <a href="{{ route('admin.customization.edit', $item->id) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('configs.edit')
                                <form method="POST" action="{{ route('admin.customization.destroy', $item->id) }}" 
                                      class="inline-block"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот элемент?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($pagination['last_page'] > 1)
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Показано 
                            <span class="font-medium">{{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}</span>
                            до 
                            <span class="font-medium">{{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}</span>
                            из 
                            <span class="font-medium">{{ $pagination['total'] }}</span>
                            результатов
                        </p>
                    </div>
                </div>
            </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-tshirt text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Элементы не найдены</h3>
                <p class="text-gray-500 mb-4">Создайте первый элемент кастомизации.</p>
                @can('configs.edit')
                <a href="{{ route('admin.customization.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Добавить элемент
                </a>
                @endcan
            </div>
        @endif
    </div>

</div>
@endsection

