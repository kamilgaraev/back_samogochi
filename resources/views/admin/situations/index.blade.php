@extends('admin.layouts.app')

@section('title', 'Управление ситуациями')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Filters -->
    <form method="GET" class="flex items-center space-x-2">
        <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Все категории</option>
            @foreach(\App\Enums\SituationCategory::cases() as $category)
                <option value="{{ $category->value }}" {{ request('category') == $category->value ? 'selected' : '' }}>
                    {{ $category->getLabel() }}
                </option>
            @endforeach
        </select>
        
        <select name="difficulty_level" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Все уровни</option>
            @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" {{ request('difficulty_level') == $i ? 'selected' : '' }}>
                    Уровень {{ $i }}
                </option>
            @endfor
        </select>
        
        <select name="is_active" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Все статусы</option>
            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Активные</option>
            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Неактивные</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-filter mr-1"></i>Фильтр
        </button>
    </form>
    
    <!-- Import/Export buttons -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('admin.situations.export-template') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" 
           title="Скачать шаблон Excel с примерами">
            <i class="fas fa-file-download mr-2"></i>Скачать шаблон
        </a>
        
        <button type="button" 
                onclick="document.getElementById('importFile').click()" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                title="Импортировать ситуации из Excel">
            <i class="fas fa-file-upload mr-2"></i>Импортировать
        </button>
        
        <form id="importForm" method="POST" action="{{ route('admin.situations.import') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" id="importFile" name="file" accept=".xlsx,.xls" onchange="document.getElementById('importForm').submit()">
        </form>
    </div>
    
    <!-- Danger zone - Delete all -->
    @can('situations.delete')
    <button type="button" 
            onclick="deleteAllSituations()" 
            class="px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 border-2 border-red-900"
            title="ОПАСНО: Удалить все ситуации из базы данных">
        <i class="fas fa-trash-alt mr-2"></i>Удалить все
    </button>
    @endcan
    
    <!-- Create button -->
    <a href="{{ route('admin.situations.create') }}" 
       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Создать ситуацию
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="situationsManager()">
    
    <!-- Bulk actions bar -->
    <div x-show="selectedIds.length > 0" 
         x-transition
         class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
            <span class="text-blue-800 font-medium">Выбрано: <span x-text="selectedIds.length"></span></span>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="clearSelection()" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-times mr-1"></i>Отменить
            </button>
            @can('situations.delete')
                <button type="button" @click="bulkDelete()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-1"></i>Удалить выбранные
                </button>
            @endcan
        </div>
    </div>
    
    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-puzzle-piece text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ collect($situations)->count() }}</p>
                    <p class="text-sm text-gray-600">Всего ситуаций</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ collect($situations)->where('is_active', true)->count() }}</p>
                    <p class="text-sm text-gray-600">Активных</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ collect($situations)->filter(function($s) { 
                        $level = is_int($s->difficulty_level) ? $s->difficulty_level : $s->difficulty_level->value; 
                        return $level >= 4; 
                    })->count() }}</p>
                    <p class="text-sm text-gray-600">Сложных (4-5)</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ collect($situations)->pluck('options')->flatten()->count() }}</p>
                    <p class="text-sm text-gray-600">Всего вариантов</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Situations table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-puzzle-piece text-blue-600 mr-2"></i>
                Список ситуаций
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input type="checkbox" 
                                   @change="toggleAll($event.target.checked)"
                                   :checked="allSelected"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ситуация
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Категория
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сложность
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Награды/Влияние
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Место показа
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
                    @forelse($situations as $situation)
                        <tr class="hover:bg-gray-50" :class="{'bg-blue-50': selectedIds.includes({{ $situation->id }})}">
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       :checked="selectedIds.includes({{ $situation->id }})"
                                       @change="toggleSelection({{ $situation->id }})"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $situation->title }}</div>
                                    <div class="text-sm text-gray-500 truncate">{{ Str::limit($situation->description, 80) }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Опций: {{ count($situation->options) }} • 
                                        Мин. уровень: {{ $situation->min_level_required }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $categoryColors = [
                                        'work' => 'blue',
                                        'study' => 'purple',
                                        'personal' => 'pink',
                                        'health' => 'green'
                                    ];
                                    $categoryValue = is_string($situation->category) ? $situation->category : $situation->category->value;
                                    $color = $categoryColors[$categoryValue] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    @if(is_string($situation->category))
                                        {{ ucfirst($situation->category) }}
                                    @else
                                        {{ $situation->category->getLabel() }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @php
                                        $difficultyValue = is_int($situation->difficulty_level) ? $situation->difficulty_level : $situation->difficulty_level->value;
                                    @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $difficultyValue ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">({{ $difficultyValue }}/5)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                            <i class="fas fa-trophy mr-1"></i>{{ $situation->experience_reward }} опыта
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center text-xs {{ $situation->stress_impact > 0 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }} px-2 py-1 rounded">
                                            <i class="fas fa-{{ $situation->stress_impact > 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                            {{ abs($situation->stress_impact) }} стресс
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $positionData = [
                                        'phone' => ['icon' => '📱', 'label' => 'Телефон', 'color' => 'blue'],
                                        'tablet' => ['icon' => '📊', 'label' => 'Планшет', 'color' => 'green'],
                                        'desktop' => ['icon' => '💻', 'label' => 'Компьютер', 'color' => 'gray'],
                                        'tv' => ['icon' => '📺', 'label' => 'Телевизор', 'color' => 'purple'],
                                        'speaker' => ['icon' => '🔊', 'label' => 'Колонка', 'color' => 'orange'],
                                        'bookshelf' => ['icon' => '📚', 'label' => 'Полка', 'color' => 'yellow'],
                                        'kitchen' => ['icon' => '🍳', 'label' => 'Кухня', 'color' => 'red'],
                                    ];
                                    $position = $positionData[$situation->position ?? 'desktop'] ?? $positionData['desktop'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $position['color'] }}-100 text-{{ $position['color'] }}-800">
                                    {{ $position['icon'] }} {{ $position['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($situation->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Активна
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-pause-circle mr-1"></i> Неактивна
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.situations.edit', $situation->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit mr-1"></i>Редактировать
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.situations.destroy', $situation->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Удалить ситуацию? Это действие нельзя отменить!')">
                                            <i class="fas fa-trash mr-1"></i>Удалить
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-puzzle-piece text-4xl mb-4 opacity-50"></i>
                                <p class="text-lg font-medium mb-2">Ситуации не найдены</p>
                                <p class="text-sm">
                                    <a href="{{ route('admin.situations.create') }}" class="text-blue-600 hover:text-blue-800">
                                        Создать первую ситуацию <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($pagination) && $pagination['total_pages'] > 1)
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Показано {{ count($situations) }} из {{ $pagination['total'] }} ситуаций
                    </div>
                    <div class="flex space-x-2">
                        @if($pagination['current_page'] > 1)
                            <a href="?page={{ $pagination['current_page'] - 1 }}" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">
                                Назад
                            </a>
                        @endif
                        
                        @for($i = 1; $i <= $pagination['total_pages']; $i++)
                            @if($i == $pagination['current_page'])
                                <span class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ $i }}</span>
                            @else
                                <a href="?page={{ $i }}" 
                                   class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor
                        
                        @if($pagination['current_page'] < $pagination['total_pages'])
                            <a href="?page={{ $pagination['current_page'] + 1 }}" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">
                                Далее
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Hidden form for bulk delete -->
    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.situations.bulk-delete') }}" class="hidden">
        @csrf
        <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>
</div>

<script>
function situationsManager() {
    return {
        selectedIds: [],
        allIds: @json(collect($situations)->pluck('id')->toArray()),
        
        get allSelected() {
            return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
        },
        
        toggleAll(checked) {
            if (checked) {
                this.selectedIds = [...this.allIds];
            } else {
                this.selectedIds = [];
            }
        },
        
        toggleSelection(id) {
            const index = this.selectedIds.indexOf(id);
            if (index === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(index, 1);
            }
        },
        
        clearSelection() {
            this.selectedIds = [];
        },
        
        bulkDelete() {
            if (this.selectedIds.length === 0) {
                alert('Не выбрано ни одной ситуации');
                return;
            }
            
            const count = this.selectedIds.length;
            if (confirm(`Вы уверены, что хотите удалить ${count} ситуаций? Это действие нельзя отменить!`)) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    }
}

function deleteAllSituations() {
    const totalCount = {{ isset($pagination) ? $pagination['total'] : collect($situations)->count() }};
    
    if (totalCount === 0) {
        alert('Нет ситуаций для удаления');
        return;
    }
    
    const confirmed = confirm(
        `⚠️ ОПАСНАЯ ОПЕРАЦИЯ ⚠️\n\n` +
        `Вы уверены, что хотите удалить ВСЕ ситуации?\n` +
        `Будет удалено: ${totalCount} ситуаций из базы данных\n\n` +
        `Это действие НЕЛЬЗЯ отменить!\n\n` +
        `Нажмите ОК для подтверждения.`
    );
    
    if (!confirmed) return;
    
    const doubleConfirm = confirm(
        `Последнее предупреждение!\n\n` +
        `Вы действительно хотите БЕЗВОЗВРАТНО удалить ВСЕ ${totalCount} ситуаций из базы данных?\n\n` +
        `Нажмите ОК для окончательного подтверждения.`
    );
    
    if (!doubleConfirm) return;
    
    fetch('{{ route("admin.situations.delete-all") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✓ ${data.message}`);
            window.location.reload();
        } else {
            alert(`✗ Ошибка: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при удалении ситуаций');
    });
}
</script>
@endsection
