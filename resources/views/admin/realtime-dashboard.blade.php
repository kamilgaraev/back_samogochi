@extends('admin.layouts.app')

@section('title', 'Real-time мониторинг')

@push('styles')
<style>
.metric-card {
    transition: all 0.3s ease;
}
.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.trend-up { color: #10b981; }
.trend-down { color: #ef4444; }
.trend-stable { color: #6b7280; }
.status-healthy { color: #10b981; }
.status-warning { color: #f59e0b; }
.status-critical { color: #ef4444; }
.pulse {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">🔴 Real-time мониторинг</h1>
                <p class="text-blue-100 mt-1">Живые метрики системы обновляются каждую минуту</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-100">Последнее обновление</div>
                <div id="last-update" class="text-lg font-semibold">--:--:--</div>
                <div class="flex items-center justify-end mt-1">
                    <div id="connection-status" class="w-3 h-3 bg-green-400 rounded-full mr-2 pulse"></div>
                    <span class="text-sm mr-3">Подключено</span>
                </div>
                <div class="flex gap-2 mt-3">
                    <button id="refresh-metrics-btn" onclick="refreshMetrics()" 
                            class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition flex items-center gap-2 font-medium">
                        <i class="fas fa-sync-alt"></i>
                        <span>Обновить сейчас</span>
                    </button>
                    <button onclick="clearMetricsCache()" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-trash-alt"></i>
                        <span>Очистить кэш</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Players Online -->
        <div class="metric-card bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Игроков онлайн</p>
                    <p id="players-online" class="text-3xl font-bold text-gray-900">--</p>
                    <div class="flex items-center mt-2">
                        <span id="players-online-trend" class="text-sm font-medium"></span>
                    </div>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Players Hour -->
        <div class="metric-card bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Активных за час</p>
                    <p id="active-players-hour" class="text-3xl font-bold text-gray-900">--</p>
                    <div class="flex items-center mt-2">
                        <span id="active-players-trend" class="text-sm font-medium"></span>
                    </div>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Situations Completed -->
        <div class="metric-card bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ситуаций/час</p>
                    <p id="situations-hour" class="text-3xl font-bold text-gray-900">--</p>
                    <div class="flex items-center mt-2">
                        <span id="situations-trend" class="text-sm font-medium"></span>
                    </div>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-puzzle-piece text-xl"></i>
                </div>
            </div>
        </div>

        <!-- API Response Time -->
        <div class="metric-card bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Время отклика API</p>
                    <p id="api-response-time" class="text-3xl font-bold text-gray-900">--ms</p>
                    <div class="flex items-center mt-2">
                        <span id="response-time-trend" class="text-sm font-medium"></span>
                    </div>
                </div>
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded">
                    <i class="fas fa-bolt text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Микро-действий/час</p>
                    <p id="micro-actions-hour" class="text-lg font-semibold">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded">
                    <i class="fas fa-heartbeat text-red-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Средний стресс</p>
                    <p id="avg-stress" class="text-lg font-semibold">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded">
                    <i class="fas fa-battery-three-quarters text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Средняя энергия</p>
                    <p id="avg-energy" class="text-lg font-semibold">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded">
                    <i class="fas fa-user-plus text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Регистраций/час</p>
                    <p id="new-registrations" class="text-lg font-semibold">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Ошибок API/час</p>
                    <p id="api-errors" class="text-lg font-semibold">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded">
                    <i class="fas fa-percentage text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Конверсия %</p>
                    <p id="newcomer-conversion" class="text-lg font-semibold">--%</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Players Activity Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-area text-blue-600 mr-2"></i>
                Активность игроков (12 часов)
            </h3>
            <div style="height: 300px;">
                <canvas id="players-activity-chart"></canvas>
            </div>
        </div>

        <!-- System Performance Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-server text-green-600 mr-2"></i>
                Производительность системы
            </h3>
            <div style="height: 300px;">
                <canvas id="system-performance-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Players Level Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-bar text-purple-600 mr-2"></i>
                Распределение игроков по уровням
            </h3>
            <div style="height: 300px;">
                <canvas id="level-distribution-chart"></canvas>
            </div>
        </div>

        <!-- Stress & Energy Over Time -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-heartbeat text-red-600 mr-2"></i>
                Стресс и Энергия (24 часа)
            </h3>
            <div style="height: 300px;">
                <canvas id="stress-energy-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- Charts Row 3 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Situation Categories -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-pie text-indigo-600 mr-2"></i>
                Популярные категории ситуаций
            </h3>
            <div style="height: 300px;">
                <canvas id="situation-categories-chart"></canvas>
            </div>
        </div>

        <!-- Activity by Hour -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-clock text-orange-600 mr-2"></i>
                Активность по времени суток
            </h3>
            <div style="height: 300px;">
                <canvas id="hourly-activity-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- Charts Row 4 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Micro Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                Топ-10 микро-действий
            </h3>
            <div style="height: 300px;">
                <canvas id="top-micro-actions-chart"></canvas>
            </div>
        </div>

        <!-- Progress & Achievements -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                Прогресс и выполнение целей
            </h3>
            <div style="height: 300px;">
                <canvas id="progress-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- Charts Row 5 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Conversion Funnel -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-filter text-teal-600 mr-2"></i>
                Воронка конверсии новичков
            </h3>
            <div style="height: 300px;">
                <canvas id="conversion-funnel-chart"></canvas>
            </div>
        </div>

        <!-- Platform Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-mobile-alt text-pink-600 mr-2"></i>
                Распределение по платформам
            </h3>
            <div style="height: 300px;">
                <canvas id="platform-distribution-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- System Health -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-heartbeat text-red-600 mr-2"></i>
                Состояние системы
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                <div class="flex items-center">
                    <div id="system-status-indicator" class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Общий статус</p>
                        <p id="system-status" class="text-xs text-gray-500">Проверка...</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded mr-3">
                        <i class="fas fa-microchip text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">CPU</p>
                        <p id="cpu-usage" class="text-xs text-gray-500">--%</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded mr-3">
                        <i class="fas fa-memory text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Память</p>
                        <p id="memory-usage" class="text-xs text-gray-500">--%</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded mr-3">
                        <i class="fas fa-database text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Redis</p>
                        <p id="redis-status" class="text-xs text-gray-500">Проверка...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
class RealtimeMetrics {
    constructor() {
        this.charts = {};
        this.initCharts();
        this.startPolling();
        this.connectWebSocket();
    }

    initCharts() {
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                point: {
                    radius: 3
                }
            }
        };

        // Players Activity Chart
        const playersCtx = document.getElementById('players-activity-chart').getContext('2d');
        this.charts.playersActivity = new Chart(playersCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Активные игроки',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: chartOptions
        });

        // System Performance Chart
        const systemCtx = document.getElementById('system-performance-chart').getContext('2d');
        this.charts.systemPerformance = new Chart(systemCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Время отклика (ms)',
                        data: [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'CPU %',
                        data: [],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Level Distribution Chart
        const levelCtx = document.getElementById('level-distribution-chart').getContext('2d');
        this.charts.levelDistribution = new Chart(levelCtx, {
            type: 'bar',
            data: {
                labels: ['1-5', '6-10', '11-15', '16-20', '21-25', '26-30', '31+'],
                datasets: [{
                    label: 'Количество игроков',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(147, 51, 234, 0.7)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 2
                }]
            },
            options: chartOptions
        });

        // Stress & Energy Chart
        const stressEnergyCtx = document.getElementById('stress-energy-chart').getContext('2d');
        this.charts.stressEnergy = new Chart(stressEnergyCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Средний стресс',
                        data: [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Средняя энергия',
                        data: [],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: chartOptions
        });

        // Situation Categories Chart
        const categoriesCtx = document.getElementById('situation-categories-chart').getContext('2d');
        this.charts.situationCategories = new Chart(categoriesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Работа', 'Учёба', 'Личное', 'Здоровье'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(236, 72, 153, 0.7)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Hourly Activity Chart
        const hourlyCtx = document.getElementById('hourly-activity-chart').getContext('2d');
        this.charts.hourlyActivity = new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: ['00', '02', '04', '06', '08', '10', '12', '14', '16', '18', '20', '22'],
                datasets: [{
                    label: 'Активность',
                    data: [],
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.3)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Top Micro Actions Chart
        const topActionsCtx = document.getElementById('top-micro-actions-chart').getContext('2d');
        this.charts.topMicroActions = new Chart(topActionsCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Использований',
                    data: [],
                    backgroundColor: 'rgba(234, 179, 8, 0.7)',
                    borderColor: 'rgb(234, 179, 8)',
                    borderWidth: 2
                }]
            },
            options: {
                ...chartOptions,
                indexAxis: 'y'
            }
        });

        // Progress Chart
        const progressCtx = document.getElementById('progress-chart').getContext('2d');
        this.charts.progress = new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: ['Новички', 'Опытные', 'Мастера', 'Эксперты'],
                datasets: [
                    {
                        label: 'Завершено ситуаций',
                        data: [],
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2
                    },
                    {
                        label: 'Выполнено действий',
                        data: [],
                        backgroundColor: 'rgba(234, 179, 8, 0.7)',
                        borderColor: 'rgb(234, 179, 8)',
                        borderWidth: 2
                    }
                ]
            },
            options: chartOptions
        });

        // Conversion Funnel Chart
        const funnelCtx = document.getElementById('conversion-funnel-chart').getContext('2d');
        this.charts.conversionFunnel = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: ['Регистрация', 'Первый вход', 'Первая ситуация', 'День 7', 'День 30'],
                datasets: [{
                    label: 'Пользователей',
                    data: [],
                    backgroundColor: [
                        'rgba(20, 184, 166, 0.9)',
                        'rgba(20, 184, 166, 0.7)',
                        'rgba(20, 184, 166, 0.5)',
                        'rgba(20, 184, 166, 0.3)',
                        'rgba(20, 184, 166, 0.2)'
                    ],
                    borderWidth: 2
                }]
            },
            options: chartOptions
        });

        // Platform Distribution Chart
        const platformCtx = document.getElementById('platform-distribution-chart').getContext('2d');
        this.charts.platformDistribution = new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet', 'Other'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(156, 163, 175, 0.7)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    async fetchMetrics() {
        try {
            const response = await fetch('/admin/metrics/dashboard');
            const data = await response.json();
            
            if (data.success) {
                this.updateMetrics(data.data);
            } else {
                console.error('Response not successful:', data);
                this.updateConnectionStatus(false);
            }
        } catch (error) {
            console.error('Ошибка получения метрик:', error);
            this.updateConnectionStatus(false);
        }
    }

    updateMetrics(data) {
        const metrics = data.metrics;
        
        // Update main metrics
        document.getElementById('players-online').textContent = metrics.players_online || '--';
        document.getElementById('active-players-hour').textContent = metrics.active_players_hour || '--';
        document.getElementById('situations-hour').textContent = metrics.situations_completed_hour || '--';
        document.getElementById('api-response-time').textContent = (metrics.api_response_time || '--') + 'ms';
        
        // Update secondary metrics
        document.getElementById('micro-actions-hour').textContent = metrics.micro_actions_hour || '--';
        document.getElementById('avg-stress').textContent = metrics.avg_stress_level || '--';
        document.getElementById('avg-energy').textContent = metrics.avg_energy_level || '--';
        document.getElementById('new-registrations').textContent = metrics.new_registrations_hour || '--';
        document.getElementById('api-errors').textContent = metrics.api_errors_hour || '--';
        document.getElementById('newcomer-conversion').textContent = (metrics.newcomer_conversion || '--') + '%';
        
        // Update trends
        this.updateTrends(data.trends);
        
        // Update system health
        this.updateSystemHealth(metrics.system_health);
        
        // Update charts
        if (data.charts) {
            this.updateCharts(data.charts);
        }
        
        // Update timestamp
        document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
        this.updateConnectionStatus(true);
    }

    updateTrends(trends) {
        if (!trends) return;
        
        Object.keys(trends).forEach(metric => {
            const trend = trends[metric];
            const element = document.getElementById(metric.replace('_', '-') + '-trend');
            
            if (element) {
                const icon = trend.direction === 'up' ? '↗' : trend.direction === 'down' ? '↘' : '→';
                const className = trend.direction === 'up' ? 'trend-up' : trend.direction === 'down' ? 'trend-down' : 'trend-stable';
                
                element.textContent = `${icon} ${Math.abs(trend.change)}%`;
                element.className = `text-sm font-medium ${className}`;
            }
        });
    }

    updateSystemHealth(health) {
        if (!health) return;
        
        const statusElement = document.getElementById('system-status');
        const indicatorElement = document.getElementById('system-status-indicator');
        
        statusElement.textContent = health.status === 'healthy' ? 'Отличное' : 
                                  health.status === 'warning' ? 'Внимание' : 'Критично';
        
        const statusClass = health.status === 'healthy' ? 'bg-green-500' : 
                           health.status === 'warning' ? 'bg-yellow-500' : 'bg-red-500';
        
        indicatorElement.className = `w-4 h-4 rounded-full mr-3 ${statusClass}`;
        
        document.getElementById('cpu-usage').textContent = (health.cpu_usage || '--') + '%';
        document.getElementById('memory-usage').textContent = (health.memory_usage || '--') + '%';
        document.getElementById('redis-status').textContent = health.redis_status ? 'Подключен' : 'Ошибка';
    }

    updateCharts(charts) {
        if (charts.players_activity && charts.players_activity.length > 0) {
            const labels = charts.players_activity.map(item => 
                new Date(item.timestamp).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
            );
            const data = charts.players_activity.map(item => Math.max(0, parseInt(item.value) || 0));
            
            this.charts.playersActivity.data.labels = labels;
            this.charts.playersActivity.data.datasets[0].data = data;
            this.charts.playersActivity.update('none');
        } else {
            // Если нет данных, показываем пустой график
            this.charts.playersActivity.data.labels = [];
            this.charts.playersActivity.data.datasets[0].data = [];
            this.charts.playersActivity.update('none');
        }
        
        if (charts.system_performance && charts.system_performance.length > 0) {
            const labels = charts.system_performance.map(item => 
                new Date(item.timestamp).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
            );
            const responseData = charts.system_performance.map(item => Math.max(0, parseFloat(item.response_time) || 0));
            const cpuData = charts.system_performance.map(item => Math.max(0, Math.min(100, parseFloat(item.cpu_usage) || 0)));
            
            this.charts.systemPerformance.data.labels = labels;
            this.charts.systemPerformance.data.datasets[0].data = responseData;
            this.charts.systemPerformance.data.datasets[1].data = cpuData;
            this.charts.systemPerformance.update('none');
        }

        if (charts.level_distribution) {
            this.charts.levelDistribution.data.datasets[0].data = charts.level_distribution;
            this.charts.levelDistribution.update('none');
        }

        if (charts.stress_energy && charts.stress_energy.length > 0) {
            const labels = charts.stress_energy.map(item => 
                new Date(item.timestamp).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
            );
            const stressData = charts.stress_energy.map(item => parseFloat(item.stress) || 0);
            const energyData = charts.stress_energy.map(item => parseFloat(item.energy) || 0);
            
            this.charts.stressEnergy.data.labels = labels;
            this.charts.stressEnergy.data.datasets[0].data = stressData;
            this.charts.stressEnergy.data.datasets[1].data = energyData;
            this.charts.stressEnergy.update('none');
        }

        if (charts.situation_categories) {
            // Преобразуем в массив если это объект
            const categoriesData = Array.isArray(charts.situation_categories) 
                ? charts.situation_categories 
                : Object.values(charts.situation_categories);
            
            const hasData = categoriesData.some(val => val > 0);
            if (!hasData) {
                // Если все значения 0, показываем хотя бы по 1 чтобы график был видным
                this.charts.situationCategories.data.datasets[0].data = [1, 1, 1, 1];
            } else {
                this.charts.situationCategories.data.datasets[0].data = categoriesData;
            }
            this.charts.situationCategories.update('none');
        }

        if (charts.hourly_activity) {
            this.charts.hourlyActivity.data.datasets[0].data = charts.hourly_activity;
            this.charts.hourlyActivity.update('none');
        }

        if (charts.top_micro_actions) {
            const hasData = charts.top_micro_actions.labels && charts.top_micro_actions.labels.length > 0;
            if (hasData) {
                // Обрезаем и очищаем названия
                const cleanLabels = charts.top_micro_actions.labels.map(label => {
                    // Убираем placeholder-ы типа {{variable}}
                    let cleaned = label.replace(/\{\{[^}]+\}\}/g, '[данные игрока]');
                    // Обрезаем длинные названия
                    if (cleaned.length > 40) {
                        cleaned = cleaned.substring(0, 37) + '...';
                    }
                    return cleaned;
                });
                
                this.charts.topMicroActions.data.labels = cleanLabels;
                this.charts.topMicroActions.data.datasets[0].data = charts.top_micro_actions.data;
            } else {
                // Если нет данных, показываем заглушку
                this.charts.topMicroActions.data.labels = ['Нет данных за последние 24 часа'];
                this.charts.topMicroActions.data.datasets[0].data = [0];
            }
            this.charts.topMicroActions.update('none');
        }

        if (charts.progress) {
            this.charts.progress.data.datasets[0].data = charts.progress.situations || [];
            this.charts.progress.data.datasets[1].data = charts.progress.actions || [];
            this.charts.progress.update('none');
        }

        if (charts.conversion_funnel) {
            this.charts.conversionFunnel.data.datasets[0].data = charts.conversion_funnel;
            this.charts.conversionFunnel.update('none');
        }

        if (charts.platform_distribution) {
            // Преобразуем в массив если это объект
            const platformData = Array.isArray(charts.platform_distribution) 
                ? charts.platform_distribution 
                : Object.values(charts.platform_distribution);
            
            const hasData = platformData.some(val => val > 0);
            if (!hasData) {
                // Если все значения 0, показываем равномерное распределение для визуализации
                this.charts.platformDistribution.data.datasets[0].data = [1, 1, 1, 1];
            } else {
                this.charts.platformDistribution.data.datasets[0].data = platformData;
            }
            this.charts.platformDistribution.update('none');
        }
    }

    updateConnectionStatus(connected) {
        const statusElement = document.getElementById('connection-status');
        const className = connected ? 'w-3 h-3 bg-green-400 rounded-full mr-2 pulse' : 'w-3 h-3 bg-red-400 rounded-full mr-2';
        statusElement.className = className;
    }

    startPolling() {
        this.fetchMetrics();
        setInterval(() => this.fetchMetrics(), 60000); // Every minute
    }

    connectWebSocket() {
        // WebSocket не используется - метрики обновляются через polling
    }
}

// Функция для ручного обновления метрик
async function refreshMetrics() {
    const btn = document.getElementById('refresh-metrics-btn');
    const icon = btn.querySelector('i');
    const text = btn.querySelector('span');
    
    // Дизейблим кнопку и показываем загрузку
    btn.disabled = true;
    icon.classList.add('fa-spin');
    text.textContent = 'Обновление...';
    
    try {
        const response = await fetch('/admin/metrics/refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Перезагружаем метрики
            window.realtimeMetrics.fetchMetrics();
            
            // Показываем уведомление об успехе
            showNotification('Метрики успешно обновлены!', 'success');
        } else {
            showNotification('Ошибка при обновлении метрик: ' + (data.message || 'Неизвестная ошибка'), 'error');
        }
    } catch (error) {
        console.error('Error refreshing metrics:', error);
        showNotification('Ошибка при обновлении метрик', 'error');
    } finally {
        // Возвращаем кнопку в исходное состояние
        btn.disabled = false;
        icon.classList.remove('fa-spin');
        text.textContent = 'Обновить сейчас';
    }
}

// Функция для очистки кэша метрик
async function clearMetricsCache() {
    if (!confirm('Вы уверены, что хотите очистить кэш метрик? Это может временно замедлить работу дашборда.')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/metrics/clear-cache', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Кэш метрик очищен!', 'success');
            // Обновляем метрики
            window.realtimeMetrics.fetchMetrics();
        } else {
            showNotification('Ошибка при очистке кэша: ' + (data.message || 'Неизвестная ошибка'), 'error');
        }
    } catch (error) {
        console.error('Error clearing cache:', error);
        showNotification('Ошибка при очистке кэша', 'error');
    }
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Убираем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.realtimeMetrics = new RealtimeMetrics();
});
</script>
@endpush
