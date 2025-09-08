<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Админ панель') - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js для графиков -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <div :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
             class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 bg-gray-800">
                <h1 class="text-xl font-bold text-white">🎮 Tamagotchi Admin</h1>
            </div>

            <!-- Navigation -->
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Дашборд</span>
                    </a>

                    <!-- Пользователи -->
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Пользователи</span>
                    </a>

                    <!-- Ситуации -->
                    <a href="{{ route('admin.situations.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.situations.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-puzzle-piece w-5"></i>
                        <span class="ml-3">Ситуации</span>
                    </a>

                    <!-- Микро-действия -->
                    <a href="{{ route('admin.micro-actions.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.micro-actions.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-bolt w-5"></i>
                        <span class="ml-3">Микро-действия</span>
                    </a>

                    <!-- Конфигурации -->
                    <a href="{{ route('admin.configs.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.configs.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-cog w-5"></i>
                        <span class="ml-3">Настройки</span>
                    </a>

                    <!-- API Документация -->
                    <a href="/docs" target="_blank"
                       class="flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 hover:text-white">
                        <i class="fas fa-book w-5"></i>
                        <span class="ml-3">API Доки</span>
                        <i class="fas fa-external-link-alt text-xs ml-auto"></i>
                    </a>

                </div>
            </nav>

            <!-- User info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-white">{{ auth()->user()->name }}</p>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-white">Выйти</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" x-cloak 
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black bg-opacity-25 lg:hidden"></div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4">
                    
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Page title -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Дашборд')</h2>
                    </div>

                    <!-- Right side buttons -->
                    <div class="flex items-center space-x-4">
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-6">
                
                <!-- Flash messages -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-cloak
                         class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-cloak
                         class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show" x-cloak
                         class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- Page content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Additional scripts -->
    @stack('scripts')
</body>
</html>
