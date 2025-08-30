# AntiStress Tamagotchi Docker Management Script for Windows PowerShell

param(
    [Parameter(Mandatory=$true)]
    [string]$Command,
    [int]$N = 2
)

function Show-Help {
    Write-Host "AntiStress Tamagotchi Docker Commands:" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Environment:"
    Write-Host "  env              Create .env.docker from production template" -ForegroundColor Green
    Write-Host "  test-connections Test connections to PostgreSQL and Redis" -ForegroundColor Green
    Write-Host ""
    Write-Host "Build and Deploy:"
    Write-Host "  build            Build Docker images" -ForegroundColor Green
    Write-Host "  up               Start production containers" -ForegroundColor Green
    Write-Host "  dev              Start development containers" -ForegroundColor Green
    Write-Host "  down             Stop all containers" -ForegroundColor Green
    Write-Host "  restart          Restart all containers" -ForegroundColor Green
    Write-Host ""
    Write-Host "Setup:"
    Write-Host "  setup            Complete setup (first time)" -ForegroundColor Yellow
    Write-Host "  migrate          Run database migrations" -ForegroundColor Green
    Write-Host "  keys             Generate application and JWT keys" -ForegroundColor Green
    Write-Host "  optimize         Optimize application for production" -ForegroundColor Green
    Write-Host ""
    Write-Host "Development:"
    Write-Host "  shell            Access application container shell" -ForegroundColor Green
    Write-Host "  logs             Show application logs" -ForegroundColor Green
    Write-Host "  test             Run PHPUnit tests" -ForegroundColor Green
    Write-Host "  tinker           Run Laravel Tinker" -ForegroundColor Green
    Write-Host ""
    Write-Host "Background Jobs:"
    Write-Host "  jobs-status      Check background jobs status" -ForegroundColor Green
    Write-Host "  energy-regen     Manually run energy regeneration" -ForegroundColor Green
    Write-Host "  daily-rewards    Manually run daily rewards" -ForegroundColor Green
    Write-Host ""
    Write-Host "Monitoring:"
    Write-Host "  health           Check application health" -ForegroundColor Green
    Write-Host "  stats            Show container stats" -ForegroundColor Green
    Write-Host ""
    Write-Host "Scaling:"
    Write-Host "  scale-workers -N 3   Scale queue workers" -ForegroundColor Green
    Write-Host ""
    Write-Host "Usage: .\docker.ps1 <command> [-N <number>]"
}

function Create-EnvFile {
    if (!(Test-Path ".env.docker")) {
        Copy-Item "docker/env.production" ".env.docker"
        Write-Host "✅ Created .env.docker from production template with your database settings." -ForegroundColor Green
        Write-Host "⚠️  Run '.\docker.ps1 keys' to generate APP_KEY and JWT_SECRET" -ForegroundColor Yellow
    } else {
        Write-Host "⚠️  .env.docker already exists" -ForegroundColor Yellow
    }
}

function Test-Connections {
    Write-Host "=== Testing PostgreSQL 17 Connection ===" -ForegroundColor Cyan
    Write-Host "Host: 192.168.0.4:5432"
    Write-Host "Database: default_db"
    Write-Host "User: gen_user"
    
    docker run --rm -it postgres:17-alpine psql 'postgresql://gen_user:X3wbvNWxCtT4B%24@192.168.0.4:5432/default_db' -c "SELECT version();"
    
    Write-Host ""
    Write-Host "=== Testing Redis 8.1 Connection ===" -ForegroundColor Cyan  
    Write-Host "Host: 192.168.0.5:6379"
    Write-Host "User: default"
    
    docker run --rm -it redis:7-alpine redis-cli -h 192.168.0.5 -p 6379 --user default --pass '?:W3K@aXg(0D!@' ping
}

switch ($Command.ToLower()) {
    "help" { 
        Show-Help 
    }
    "env" { 
        Create-EnvFile 
    }
    "test-connections" { 
        Test-Connections 
    }
    "build" { 
        Write-Host "🔨 Building Docker images..." -ForegroundColor Yellow
        docker compose build --no-cache 
    }
    "up" { 
        Create-EnvFile
        Write-Host "🚀 Starting production containers..." -ForegroundColor Yellow
        docker compose up -d 
    }
    "dev" { 
        Create-EnvFile
        Write-Host "🔧 Starting development containers..." -ForegroundColor Yellow
        docker compose -f docker-compose.yml -f docker-compose.override.yml up -d 
    }
    "down" { 
        Write-Host "🛑 Stopping all containers..." -ForegroundColor Yellow
        docker compose down 
    }
    "restart" { 
        Write-Host "🔄 Restarting containers..." -ForegroundColor Yellow
        docker compose down
        docker compose up -d 
    }
    "migrate" { 
        Write-Host "📊 Running database migrations..." -ForegroundColor Yellow
        docker compose exec app php artisan migrate --seed 
    }
    "keys" { 
        Write-Host "🔑 Generating application keys..." -ForegroundColor Yellow
        docker compose exec app php artisan key:generate --force
        docker compose exec app php artisan jwt:secret --force 
    }
    "optimize" { 
        Write-Host "⚡ Optimizing application..." -ForegroundColor Yellow
        docker compose exec app php artisan optimize
        docker compose exec app php artisan view:cache
        docker compose exec app php artisan route:cache 
    }
    "setup" { 
        Write-Host "🎯 Complete setup starting..." -ForegroundColor Cyan
        Create-EnvFile
        docker compose up -d
        Start-Sleep -Seconds 10
        docker compose exec app php artisan migrate --seed
        docker compose exec app php artisan key:generate --force
        docker compose exec app php artisan jwt:secret --force
        docker compose exec app php artisan optimize
        Write-Host "✅ Setup complete!" -ForegroundColor Green
    }
    "shell" { 
        docker compose exec app sh 
    }
    "logs" { 
        docker compose logs -f app 
    }
    "test" { 
        docker compose exec app php artisan test 
    }
    "tinker" { 
        docker compose exec app php artisan tinker 
    }
    "jobs-status" { 
        docker compose exec app supervisorctl status 
    }
    "energy-regen" { 
        docker compose exec app php artisan game:energy-regen 
    }
    "daily-rewards" { 
        docker compose exec app php artisan game:daily-rewards 
    }
    "health" { 
        try {
            $response = Invoke-RestMethod -Uri "http://localhost/health" -Method Get
            Write-Host "✅ Health check: $response" -ForegroundColor Green
        } catch {
            Write-Host "❌ Health check failed: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    "stats" { 
        Write-Host "=== Container Status ===" -ForegroundColor Cyan
        docker compose ps
        Write-Host ""
        Write-Host "=== Resource Usage ===" -ForegroundColor Cyan
        docker stats --no-stream
    }
    "scale-workers" { 
        Write-Host "⚡ Scaling queue workers to $N instances..." -ForegroundColor Yellow
        docker compose up -d --scale queue-worker=$N 
    }
    "clean" { 
        Write-Host "🧹 Cleaning containers and volumes..." -ForegroundColor Yellow
        docker compose down -v
        docker system prune -f 
    }
    default { 
        Write-Host "❌ Unknown command: $Command" -ForegroundColor Red
        Write-Host ""
        Show-Help 
    }
}
