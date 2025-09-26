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
                <div class="flex items-center mt-1">
                    <div id="connection-status" class="w-3 h-3 bg-green-400 rounded-full mr-2 pulse"></div>
                    <span class="text-sm">Подключено</span>
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

    <!-- Charts Row -->
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    }

    async fetchMetrics() {
        try {
            const response = await fetch('/api/admin/metrics/dashboard');
            const data = await response.json();
            
            if (data.success) {
                this.updateMetrics(data.data);
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
        // TODO: Implement WebSocket connection when Broadcasting is configured
        console.log('WebSocket connection would be initialized here');
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    new RealtimeMetrics();
});
</script>
@endpush
