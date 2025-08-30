@echo off
:: AntiStress Tamagotchi Quick Setup for Windows

echo ========================================
echo AntiStress Tamagotchi Setup
echo ========================================
echo.

:: Check if Docker is running
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not installed or running
    echo Please install Docker Desktop and make sure it's running
    pause
    exit /b 1
)

echo ✅ Docker is running

:: Check if docker-compose is available
docker compose version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose is not available
    echo Please update Docker Desktop to the latest version
    pause
    exit /b 1
)

echo ✅ Docker Compose is available

:: Create environment file
if not exist .env.docker (
    echo 📝 Creating .env.docker from production template...
    copy "docker\env.production" ".env.docker"
    echo ✅ Created .env.docker
    echo ⚠️  Make sure to check your database settings in .env.docker
) else (
    echo ⚠️  .env.docker already exists
)

echo.
echo 🚀 Starting containers...
docker compose up -d

echo.
echo ⏳ Waiting for containers to start...
timeout /t 10 /nobreak > nul

echo.
echo 📊 Running database migrations...
docker compose exec app php artisan migrate --seed

echo.
echo 🔑 Generating application keys...
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan jwt:secret --force

echo.
echo ⚡ Optimizing application...
docker compose exec app php artisan optimize

echo.
echo ========================================
echo ✅ Setup Complete!
echo ========================================
echo.
echo Your API is running at: http://localhost
echo Health check: http://localhost/health
echo.
echo Useful commands:
echo   .\docker.ps1 logs     - View logs
echo   .\docker.ps1 stats    - Container stats  
echo   .\docker.ps1 shell    - Access container
echo   .\docker.ps1 help     - All commands
echo.
echo Press any key to continue...
pause > nul
