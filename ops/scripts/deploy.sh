#!/bin/bash

# =============================================================================
# Tamagotchi API Deployment Script (without Docker)
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
DEPLOY_DIR="/opt/tamagotchi-api"
BACKUP_DIR="/opt/backups/tamagotchi-api"
WEB_USER="www-data"
COMPOSER_BIN="$(command -v composer || echo /usr/local/bin/composer)"

# Ensure environment for composer in non-interactive root context
export HOME="${HOME:-/root}"
export COMPOSER_HOME="${COMPOSER_HOME:-$HOME/.composer}"
export COMPOSER_ALLOW_SUPERUSER=1
mkdir -p "$COMPOSER_HOME"

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root"
        exit 1
    fi
}

create_backup() {
    log_info "Creating backup..."
    
    mkdir -p "$BACKUP_DIR"
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP"
    
    if [ -d "$DEPLOY_DIR" ]; then
        cp -r "$DEPLOY_DIR" "$BACKUP_PATH"
        log_success "Backup created at $BACKUP_PATH"
    fi
}

update_code() {
    log_info "Updating application code..."
    
    cd "$DEPLOY_DIR"
    
    # Pull latest changes
    git fetch origin && git reset --hard origin/main || {
        log_warning "Git reset failed, attempting fallback pull..."
        git pull --rebase --autostash origin main || true
    }
    
    log_success "Code updated"
}

install_dependencies() {
    log_info "Installing dependencies..."
    
    cd "$DEPLOY_DIR"
    
    # Install PHP dependencies
    "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction || \
    "$COMPOSER_BIN" update --no-dev --optimize-autoloader --no-interaction
    
    log_success "Dependencies installed"
}

run_migrations() {
    log_info "Running database migrations..."
    cd "$DEPLOY_DIR"

    # Read DB host/port from .env with safe fallbacks
    local DB_HOST
    local DB_PORT
    DB_HOST=$(grep -E '^DB_HOST=' .env | sed -E 's/^DB_HOST=//; s/^"|\'"'"'//; s/"|\'"'"'$//')
    DB_PORT=$(grep -E '^DB_PORT=' .env | sed -E 's/^DB_PORT=//; s/[^0-9]//g')
    [ -z "$DB_PORT" ] && DB_PORT=5432

    # If DB host is empty, try localhost
    [ -z "$DB_HOST" ] && DB_HOST="127.0.0.1"

    # Check TCP connectivity to DB. If unreachable, skip migrations to not break deploy
    if timeout 3 bash -c ">/dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null; then
        php artisan migrate --force
        log_success "Migrations completed"
    else
        log_warning "Database $DB_HOST:$DB_PORT is unreachable. Skipping migrations."
    fi
}

optimize_application() {
    log_info "Optimizing application..."
    
    cd "$DEPLOY_DIR"
    
    # Clear and cache config
    php artisan config:clear
    php artisan config:cache
    
    # Clear and cache routes
    php artisan route:clear
    php artisan route:cache
    
    # Clear and cache views
    php artisan view:clear
    php artisan view:cache
    
    # Optimize autoloader
    "$COMPOSER_BIN" dump-autoload --optimize
    
    log_success "Application optimized"
}

fix_permissions() {
    log_info "Fixing permissions..."
    
    cd "$DEPLOY_DIR"
    
    # Set correct ownership
    chown -R "$WEB_USER:$WEB_USER" "$DEPLOY_DIR"
    
    # Set directory permissions
    find "$DEPLOY_DIR" -type d -exec chmod 755 {} \;
    find "$DEPLOY_DIR" -type f -exec chmod 644 {} \;
    
    # Set executable permissions for artisan
    chmod +x artisan
    
    # Set writable permissions for storage and cache
    chmod -R 775 storage bootstrap/cache
    chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache
    
    log_success "Permissions fixed"
}

restart_services() {
    log_info "Restarting services..."
    
    # Restart queue worker
    systemctl restart tamagotchi-queue
    
    # Reload PHP-FPM
    systemctl reload php8.3-fpm
    
    # Reload Nginx
    systemctl reload nginx
    
    log_success "Services restarted"
}

health_check() {
    log_info "Running health check..."
    
    # Wait a moment for services to start
    sleep 5
    
    # Check if application responds
    if curl -f http://localhost/health > /dev/null 2>&1; then
        log_success "Application is healthy"
    else
        log_warning "Health check failed. Check logs:"
        journalctl -u nginx -n 10 --no-pager
        journalctl -u php8.3-fpm -n 10 --no-pager
        journalctl -u tamagotchi-queue -n 10 --no-pager
    fi
}

show_status() {
    log_info "Deployment Status:"
    echo ""
    
    echo "Service Status:"
    systemctl status nginx php8.3-fpm tamagotchi-queue --no-pager -l
    echo ""
    
    echo "Application URLs:"
    echo "  - API: http://$(hostname -I | awk '{print $1}')"
    echo "  - Health: http://$(hostname -I | awk '{print $1}')/health"
    echo ""
    
    echo "Useful Commands:"
    echo "  - View logs: journalctl -u tamagotchi-queue -f"
    echo "  - Restart queue: systemctl restart tamagotchi-queue"
    echo "  - Deploy update: $0"
    echo ""
}

main() {
    log_info "Starting deployment..."
    
    check_root
    create_backup
    update_code
    install_dependencies
    run_migrations
    optimize_application
    fix_permissions
    restart_services
    health_check
    show_status
    
    log_success "Deployment completed successfully!"
}

# Run main function
main "$@"
