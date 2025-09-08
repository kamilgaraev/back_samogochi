<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход в админ панель - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-900 to-purple-900 min-h-screen flex items-center justify-center">
    
    <div class="max-w-md w-full space-y-8 p-8">
        
        <!-- Logo and title -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-white rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-gamepad text-3xl text-blue-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Админ панель</h2>
            <p class="text-blue-200">Tamagotchi API Management</p>
        </div>

        <!-- Login form -->
        <div class="bg-white rounded-lg shadow-xl p-8">
            <form method="POST" action="{{ route('admin.authenticate') }}" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="admin@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Пароль
                    </label>
                    <input id="password" name="password" type="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Запомнить меня
                    </label>
                </div>

                <!-- Submit button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-medium transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Войти в админку
                    </button>
                </div>

                <!-- Back to site -->
                <div class="text-center">
                    <a href="/" class="text-sm text-blue-600 hover:text-blue-500">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Вернуться на сайт
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-blue-200 text-sm">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Админ панель v1.0</p>
        </div>
    </div>

</body>
</html>
