#!/bin/bash

# =============================================================================
# Tamagotchi API Installation Script (without Docker)
# For Ubuntu 20.04+ / Debian 11+
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
REPO_URL="https://github.com/kamilgaraev/back_samogochi.git"
DEPLOY_DIR="/opt/tamagotchi-api"
WEB_USER="www-data"

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

install_packages() {
    log_info "Installing system packages..."
    
    # Update package list
    apt update
    
    # Install basic packages
    apt install -y software-properties-common ca-certificates curl git unzip supervisor
    
    # Add PHP repository (for latest PHP versions)
    add-apt-repository -y ppa:ondrej/php
    apt update
    
    # Install PHP and extensions
    apt install -y \
        php8.3-fpm \
        php8.3-cli \
        php8.3-xml \
        php8.3-mbstring \
        php8.3-curl \
        php8.3-zip \
        php8.3-gd \
        php8.3-bcmath \
        php8.3-pgsql \
        php8.3-redis \
        php8.3-intl
    
    # Install Nginx
    apt install -y nginx
    
    # Install Redis (optional, if not using external Redis)
    # apt install -y redis-server
    
    log_success "Packages installed"
}

install_composer() {
    log_info "Installing Composer..."
    
    # Download and install Composer
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    
    log_success "Composer installed"
}

setup_application() {
    log_info "Setting up application..."
    
    # Create deploy directory
    mkdir -p "$DEPLOY_DIR"
    cd "$DEPLOY_DIR"
    
    # Clone repository
    git clone "$REPO_URL" .
    
    # Install dependencies
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Set up environment file
    if [ ! -f ".env" ]; then
        if [ -f ".env.docker" ]; then
            cp .env.docker .env
            log_info "Copied .env.docker to .env"
        else
            cp .env.example .env
            log_warning "Copied .env.example to .env - please configure it!"
        fi
    fi
    
    # Generate application key if needed
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate
    fi
    
    # Set permissions
    chown -R "$WEB_USER:$WEB_USER" "$DEPLOY_DIR"
    chmod -R 755 "$DEPLOY_DIR"
    chmod -R 775 storage bootstrap/cache
    
    log_success "Application set up"
}

configure_nginx() {
    log_info "Configuring Nginx..."
    
    # Copy nginx configuration
    cp "$DEPLOY_DIR/ops/nginx/tamagotchi.conf" /etc/nginx/sites-available/tamagotchi
    
    # Enable site
    ln -sf /etc/nginx/sites-available/tamagotchi /etc/nginx/sites-enabled/tamagotchi
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    # Test configuration
    nginx -t
    
    # Restart nginx
    systemctl restart nginx
    systemctl enable nginx
    
    log_success "Nginx configured"
}

configure_php() {
    log_info "Configuring PHP-FPM..."
    
    # Ensure PHP-FPM is running
    systemctl restart php8.3-fpm
    systemctl enable php8.3-fpm
    
    log_success "PHP-FPM configured"
}

setup_queue_worker() {
    log_info "Setting up queue worker..."
    
    # Copy systemd service
    cp "$DEPLOY_DIR/ops/systemd/tamagotchi-queue.service" /etc/systemd/system/
    
    # Reload systemd and start service
    systemctl daemon-reload
    systemctl enable tamagotchi-queue
    systemctl start tamagotchi-queue
    
    log_success "Queue worker configured"
}

setup_scheduler() {
    log_info "Setting up task scheduler..."
    
    # Copy cron file
    cp "$DEPLOY_DIR/ops/cron/tamagotchi-schedule" /etc/cron.d/
    
    # Restart cron
    systemctl restart cron
    
    log_success "Task scheduler configured"
}

configure_firewall() {
    log_info "Configuring firewall..."
    
    # Install and configure UFW
    apt install -y ufw
    
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    
    # Allow SSH, HTTP, HTTPS
    ufw allow ssh
    ufw allow 80/tcp
    ufw allow 443/tcp
    
    # Enable firewall
    ufw --force enable
    
    log_success "Firewall configured"
}

run_final_setup() {
    log_info "Running final setup..."
    
    cd "$DEPLOY_DIR"
    
    # Run migrations
    php artisan migrate --force
    
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log_success "Final setup completed"
}

show_status() {
    log_info "Installation Status:"
    echo ""
    
    echo "Service Status:"
    systemctl status nginx php8.3-fpm tamagotchi-queue --no-pager
    echo ""
    
    echo "Application URLs:"
    echo "  - API: http://$(hostname -I | awk '{print $1}')"
    echo "  - Health: http://$(hostname -I | awk '{print $1}')/health"
    echo ""
    
    echo "Next Steps:"
    echo "1. Configure your .env file: nano $DEPLOY_DIR/.env"
    echo "2. Set up SSL certificate with certbot (optional)"
    echo "3. Configure your domain DNS"
    echo ""
    
    echo "Management Commands:"
    echo "  - Deploy updates: $DEPLOY_DIR/ops/scripts/deploy.sh"
    echo "  - View queue logs: journalctl -u tamagotchi-queue -f"
    echo "  - Restart services: systemctl restart nginx php8.3-fpm tamagotchi-queue"
    echo ""
}

main() {
    log_info "Starting Tamagotchi API installation..."
    
    check_root
    install_packages
    install_composer
    setup_application
    configure_nginx
    configure_php
    setup_queue_worker
    setup_scheduler
    configure_firewall
    run_final_setup
    show_status
    
    log_success "Installation completed successfully!"
    log_warning "Don't forget to configure your .env file!"
}

# Run main function
main "$@"
