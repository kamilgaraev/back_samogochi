<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AntiStress Tamagotchi') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 dark:from-purple-900 dark:via-pink-900 dark:to-red-900 min-h-screen">
    <!-- Header -->
    <header class="fixed top-0 w-full bg-white/90 dark:bg-black/90 backdrop-blur-md z-50 border-b border-white/20">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-lg">🎮</span>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">AntiStress</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#features" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">Особенности</a>
                    <a href="#download" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">Скачать</a>
                    <a href="/docs" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">API Документация</a>
                    <a href="/admin" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">Админ</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <main class="pt-20">
        <section class="container mx-auto px-6 py-20">
            <div class="text-center">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight">
                    Твой личный<br>
                    <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                        АнтиСтресс Тамагочи
                    </span>
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Борись со стрессом играючи! Выращивай своего цифрового питомца, 
                    проходи ситуации и развивай навыки управления эмоциями.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/docs" class="bg-white text-purple-600 px-8 py-4 rounded-2xl font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-2xl">
                        📖 API Документация
                    </a>
                    <a href="/admin" class="bg-black/30 text-white border-2 border-white/30 px-8 py-4 rounded-2xl font-semibold text-lg hover:bg-white/10 transition-all transform hover:scale-105">
                        ⚙️ Админ-панель
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-white/10 backdrop-blur-lg py-20">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl md:text-5xl font-bold text-center text-white mb-16">
                    Основные возможности API
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white/20 backdrop-blur-lg rounded-3xl p-8 text-center hover:transform hover:scale-105 transition-all">
                        <div class="text-6xl mb-4">🎭</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Игровые ситуации</h3>
                        <p class="text-white/80">Полный CRUD для управления стрессовыми ситуациями с системой выбора решений</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-3xl p-8 text-center hover:transform hover:scale-105 transition-all">
                        <div class="text-6xl mb-4">👤</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Профили игроков</h3>
                        <p class="text-white/80">Управление прогрессом, уровнями, энергией и стрессом игроков</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-lg rounded-3xl p-8 text-center hover:transform hover:scale-105 transition-all">
                        <div class="text-6xl mb-4">🔧</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Админ-панель</h3>
                        <p class="text-white/80">Удобный веб-интерфейс для управления контентом и аналитикой</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20">
            <div class="container mx-auto px-6">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-white mb-2">JWT</div>
                        <div class="text-white/80 text-lg">Аутентификация</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-white mb-2">REST</div>
                        <div class="text-white/80 text-lg">API архитектура</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-white mb-2">Laravel</div>
                        <div class="text-white/80 text-lg">Backend framework</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-white mb-2">OpenAPI</div>
                        <div class="text-white/80 text-lg">Документация</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Download Section -->
        <section id="download" class="bg-white/10 backdrop-blur-lg py-20">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-8">
                    Готов начать разработку?
                </h2>
                <p class="text-xl text-white/90 mb-12 max-w-2xl mx-auto">
                    Изучи полную документацию API и начни создавать приложения для управления стрессом
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/docs" class="bg-white text-purple-600 px-8 py-4 rounded-2xl font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-2xl">
                        📖 Открыть документацию
                    </a>
                    <a href="/admin" class="bg-black/30 text-white border-2 border-white/30 px-8 py-4 rounded-2xl font-semibold text-lg hover:bg-white/10 transition-all transform hover:scale-105">
                        ⚙️ Войти в админку
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-black/30 backdrop-blur-lg border-t border-white/20 py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">🎮</span>
                        </div>
                        <span class="text-lg font-bold text-white">AntiStress API</span>
                    </div>
                    <p class="text-white/70">Мощный API для Tamagotchi.</p>
                </div>
                
            </div>
            <div class="border-t border-white/20 mt-12 pt-8 text-center">
                <p class="text-white/70">© 2024 AntiStress Tamagotchi API. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
