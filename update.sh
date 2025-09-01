#!/bin/bash

# =============================================================================
# Tamagotchi API Update Script
# =============================================================================

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

DEPLOY_DIR="/opt/tamagotchi-api"

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

main() {
    log_info "Starting application update..."
    
    cd "$DEPLOY_DIR"
    
    # Create backup
    log_info "Creating backup..."
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_DIR="/opt/backups/tamagotchi-api/update_$TIMESTAMP"
    mkdir -p "$BACKUP_DIR"
    docker-compose -f docker-compose.yml -f docker-compose.production.yml logs > "$BACKUP_DIR/logs.txt"
    
    # Pull latest changes
    log_info "Pulling latest changes..."
    git pull origin main
    
    # Rebuild and restart containers
    log_info "Rebuilding containers..."
    docker-compose -f docker-compose.yml -f docker-compose.production.yml down
    docker-compose -f docker-compose.yml -f docker-compose.production.yml up --build -d
    
    # Wait for services to start
    log_info "Waiting for services to start..."
    sleep 30
    
    # Health check
    log_info "Running health check..."
    if curl -f http://localhost/health > /dev/null 2>&1; then
        log_success "Application is healthy"
    else
        log_warning "Health check failed"
        docker-compose -f docker-compose.yml -f docker-compose.production.yml logs app | tail -10
    fi
    
    log_success "Update completed!"
}

main "$@"
