@extends('admin.layouts.app')

@section('title', 'Микро-действия')

@section('content')
<div class="space-y-6">
    
    <!-- Header with actions -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Микро-действия</h1>
            <p class="text-gray-600 mt-1">Управление техниками снятия стресса</p>
        </div>
        
        <div class="flex gap-3">
            @can('situations.delete')
            <button onclick="deleteAllMicroActions()" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Удалить все
            </button>
            @endcan
            
            @can('situations.create')
            <a href="{{ route('admin.micro-actions.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Добавить микро-действие
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
                <select name="category" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Все категории</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ (request('category') === $value) ? 'selected' : '' }}>
                            {{ $label }}
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
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    <i class="fas fa-search mr-2"></i>Фильтр
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $totalMicroActions = collect($microActions)->count();
            $activeMicroActions = collect($microActions)->where('is_active', true)->count();
            $categoryStats = collect($microActions)->groupBy('category');
        @endphp
        
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-bolt text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Всего</p>
                    <p class="text-xl font-semibold">{{ $totalMicroActions }}</p>
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
                    <p class="text-xl font-semibold">{{ $activeMicroActions }}</p>
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
                    <p class="text-sm text-gray-600">Популярная</p>
                    <p class="text-sm font-semibold">{{ $categoryStats->sortByDesc(fn($items) => $items->count())->keys()->first() ? \App\Enums\MicroActionCategory::from($categoryStats->sortByDesc(fn($items) => $items->count())->keys()->first())->getLabel() : 'Нет' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Micro-actions list -->
    <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
        @if(count($microActions) > 0)
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
                                Платформа
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Награды
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Кулдаун
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Уровень
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
                        @foreach($microActions as $microAction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-2xl mr-3">
                                        {{ $microAction->category->getIcon() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $microAction->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ Str::limit($microAction->description, 50) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $categoryColor = $microAction->category->getColor();
                                    $categoryLabel = $microAction->category->getLabel();
                                    $categoryHtml = sprintf(
                                        '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: %s20; color: %s;">%s</span>',
                                        $categoryColor,
                                        $categoryColor,
                                        $categoryLabel
                                    );
                                @endphp
                                {!! $categoryHtml !!}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $positionLabels = [
                                        'desktop' => '💻 Компьютер',
                                        'phone' => '📱 Телефон',
                                        'tablet' => '📊 Планшет',
                                        'tv' => '📺 Телевизор',
                                        'speaker' => '🔊 Колонка',
                                        'bookshelf' => '📚 Полка',
                                        'kitchen' => '🍳 Кухня',
                                        'table' => '🪑 Стол',
                                        'wallClock' => '🕐 Часы',
                                        'lapTop' => '💻 Ноутбук',
                                        'fridge' => '🧊 Холодильник',
                                        'trashCan' => '🗑️ Корзина',
                                        'bed' => '🛏️ Кровать',
                                        'mirror' => '🪞 Зеркало'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $positionLabels[$microAction->position] ?? '💻 Компьютер' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex space-x-2">
                                    @if($microAction->energy_reward > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-bolt mr-1"></i>{{ $microAction->energy_reward }}
                                        </span>
                                    @endif
                                    @if($microAction->experience_reward > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-star mr-1"></i>{{ $microAction->experience_reward }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $microAction->cooldown_minutes }} мин
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $microAction->unlock_level }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($microAction->is_active)
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
                                @can('situations.view')
                                <a href="{{ route('admin.micro-actions.edit', $microAction->id) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('situations.delete')
                                <form method="POST" action="{{ route('admin.micro-actions.destroy', $microAction->id) }}" 
                                      class="inline-block"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить это микро-действие?')">
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
            
            @if($pagination['total_pages'] > 1)
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($pagination['current_page'] > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Предыдущая
                            </a>
                        @endif
                        @if($pagination['current_page'] < $pagination['total_pages'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Следующая
                            </a>
                        @endif
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
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
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                @if($pagination['current_page'] > 1)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Предыдущая</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @else
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                @endif

                                @php
                                    $start = max(1, $pagination['current_page'] - 2);
                                    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                @endphp

                                @if($start > 1)
                                    <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        1
                                    </a>
                                    @if($start > 2)
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                            ...
                                        </span>
                                    @endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    @if($i == $pagination['current_page'])
                                        <span class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                            {{ $i }}
                                        </span>
                                    @else
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                                           class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                            {{ $i }}
                                        </a>
                                    @endif
                                @endfor

                                @if($end < $pagination['total_pages'])
                                    @if($end < $pagination['total_pages'] - 1)
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                            ...
                                        </span>
                                    @endif
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['total_pages']]) }}" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        {{ $pagination['total_pages'] }}
                                    </a>
                                @endif

                                @if($pagination['current_page'] < $pagination['total_pages'])
                                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Следующая</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-bolt text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Микро-действия не найдены</h3>
                <p class="text-gray-500 mb-4">Создайте первое микро-действие для снятия стресса.</p>
                @can('situations.create')
                <a href="{{ route('admin.micro-actions.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Добавить микро-действие
                </a>
                @endcan
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function deleteAllMicroActions() {
    if (!confirm('Вы уверены, что хотите удалить ВСЕ микро-действия? Это действие необратимо!')) {
        return;
    }
    
    if (!confirm('Это удалит все микро-действия и связанные данные игроков. Вы точно уверены?')) {
        return;
    }

    fetch('{{ route('admin.micro-actions.delete-all') }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при удалении');
    });
}
</script>
@endpush
@endsection
