# AntiStress Tamagotchi Docker Management

.PHONY: help build up down logs shell migrate test optimize clean dev prod

# Default target
help: ## Show this help message
	@echo "AntiStress Tamagotchi Docker Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

# Environment setup
env: ## Create .env.docker from production template
	@if [ ! -f .env.docker ]; then \
		cp docker/env.production .env.docker; \
		echo "Created .env.docker from production template with your database settings."; \
		echo "Run 'make keys' to generate APP_KEY and JWT_SECRET"; \
	else \
		echo ".env.docker already exists"; \
	fi

test-connections: ## Test connections to PostgreSQL and Redis
	@echo "Testing external service connections..."
	@chmod +x docker/connection-test.sh
	@./docker/connection-test.sh

# Build and deployment
build: ## Build Docker images
	docker compose build --no-cache

up: env ## Start production containers
	docker compose up -d

dev: env ## Start development containers
	docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

down: ## Stop all containers
	docker compose down

restart: down up ## Restart all containers

# Database operations  
migrate: ## Run database migrations
	docker compose exec app php artisan migrate --seed

migrate-fresh: ## Fresh migration with seed
	docker compose exec app php artisan migrate:fresh --seed

# Application setup
setup: up migrate keys optimize ## Complete setup (first time)

keys: ## Generate application and JWT keys
	docker compose exec app php artisan key:generate --force
	docker compose exec app php artisan jwt:secret --force

optimize: ## Optimize application for production
	docker compose exec app php artisan optimize
	docker compose exec app php artisan view:cache
	docker compose exec app php artisan route:cache

clear-cache: ## Clear all caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

# Development tools
shell: ## Access application container shell
	docker compose exec app sh

logs: ## Show application logs
	docker compose logs -f app

nginx-logs: ## Show Nginx logs  
	docker compose logs -f nginx

test: ## Run PHPUnit tests
	docker compose exec app php artisan test

tinker: ## Run Laravel Tinker
	docker compose exec app php artisan tinker

# Background processes
jobs-status: ## Check background jobs status
	docker compose exec app supervisorctl status

jobs-restart: ## Restart background jobs
	docker compose exec app supervisorctl restart all

energy-regen: ## Manually run energy regeneration
	docker compose exec app php artisan game:energy-regen

daily-rewards: ## Manually run daily rewards
	docker compose exec app php artisan game:daily-rewards

# Monitoring
health: ## Check application health
	curl -s http://localhost/health

stats: ## Show container stats
	docker compose ps
	docker stats --no-stream

# Cleanup
clean: ## Remove containers and volumes
	docker compose down -v
	docker system prune -f

clean-all: ## Remove everything including images
	docker compose down -v --rmi all
	docker system prune -af

# Scaling
scale-workers: ## Scale queue workers (usage: make scale-workers N=3)
	docker compose up -d --scale queue-worker=${N}

scale-app: ## Scale app containers (usage: make scale-app N=2) 
	docker compose up -d --scale app=${N}

# Production helpers
backup-db: ## Backup database (implement with your backup strategy)
	@echo "Implement database backup for your PostgreSQL server"

deploy: build migrate optimize ## Deploy to production
	@echo "Production deployment complete"

status: ## Show detailed status
	@echo "=== Container Status ==="
	docker compose ps
	@echo ""
	@echo "=== Background Jobs ==="
	docker compose exec app supervisorctl status
	@echo ""
	@echo "=== Health Check ==="
	curl -s http://localhost/health || echo "Health check failed"
