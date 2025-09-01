#!/bin/bash

# =============================================================================
# Tamagotchi API Deploy Script
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="tamagotchi-api"
REPO_URL="https://github.com/your-username/tamagotchi-api.git"  # Замените на ваш репозиторий
DEPLOY_DIR="/opt/$PROJECT_NAME"
BACKUP_DIR="/opt/backups/$PROJECT_NAME"
DOCKER_COMPOSE_VERSION="2.21.0"

# Functions
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

install_docker() {
    log_info "Installing Docker..."
    
    # Update package index
    apt-get update
    
    # Install required packages
    apt-get install -y \
        ca-certificates \
        curl \
        gnupg \
        lsb-release
    
    # Add Docker's official GPG key
    mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    # Set up the repository
    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
        $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Update package index again
    apt-get update
    
    # Install Docker Engine
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Start and enable Docker
    systemctl start docker
    systemctl enable docker
    
    log_success "Docker installed successfully"
}

install_docker_compose() {
    log_info "Installing Docker Compose..."
    
    # Download Docker Compose
    curl -L "https://github.com/docker/compose/releases/download/v${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    
    # Make it executable
    chmod +x /usr/local/bin/docker-compose
    
    # Create symlink for docker-compose command
    ln -sf /usr/local/bin/docker-compose /usr/bin/docker-compose
    
    log_success "Docker Compose installed successfully"
}

check_dependencies() {
    log_info "Checking system dependencies..."
    
    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        log_warning "Docker not found. Installing..."
        install_docker
    else
        log_success "Docker is already installed"
    fi
    
    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null; then
        log_warning "Docker Compose not found. Installing..."
        install_docker_compose
    else
        log_success "Docker Compose is already installed"
    fi
    
    # Install additional tools
    apt-get update
    apt-get install -y git curl wget unzip
}

create_backup() {
    if [ -d "$DEPLOY_DIR" ]; then
        log_info "Creating backup of existing deployment..."
        
        mkdir -p "$BACKUP_DIR"
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP"
        
        cp -r "$DEPLOY_DIR" "$BACKUP_PATH"
        log_success "Backup created at $BACKUP_PATH"
    fi
}

deploy_application() {
    log_info "Deploying application..."
    
    # Create deploy directory
    mkdir -p "$DEPLOY_DIR"
    cd "$DEPLOY_DIR"
    
    # Clone or update repository
    if [ -d ".git" ]; then
        log_info "Updating existing repository..."
        git pull origin main
    else
        log_info "Cloning repository..."
        git clone "$REPO_URL" .
    fi
    
    # Copy production environment file
    if [ ! -f ".env.docker" ]; then
        log_info "Setting up environment configuration..."
        cp docker/env.production .env.docker
        
        # Generate APP_KEY
        log_info "Generating application key..."
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/^APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env.docker
        
        log_warning "Please review and update .env.docker with your production settings:"
        log_warning "- Database credentials"
        log_warning "- Redis credentials"
        log_warning "- JWT secret"
        log_warning "- App URL"
        
        echo ""
        read -p "Press Enter after updating .env.docker file..."
    fi
}

setup_ssl() {
    log_info "Setting up SSL certificates..."
    
    # Create SSL directory
    mkdir -p "$DEPLOY_DIR/docker/ssl"
    
    if [ ! -f "$DEPLOY_DIR/docker/ssl/cert.pem" ]; then
        log_warning "SSL certificates not found. Please add your SSL certificates to:"
        log_warning "- $DEPLOY_DIR/docker/ssl/cert.pem"
        log_warning "- $DEPLOY_DIR/docker/ssl/privkey.pem"
        
        # Generate self-signed certificate for testing
        log_info "Generating self-signed certificate for testing..."
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout "$DEPLOY_DIR/docker/ssl/privkey.pem" \
            -out "$DEPLOY_DIR/docker/ssl/cert.pem" \
            -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
        
        log_warning "Self-signed certificate generated. Replace with valid certificates for production!"
    fi
}

setup_firewall() {
    log_info "Configuring firewall..."
    
    # Install UFW if not present
    apt-get install -y ufw
    
    # Configure firewall rules
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    
    # Allow SSH
    ufw allow ssh
    
    # Allow HTTP and HTTPS
    ufw allow 80/tcp
    ufw allow 443/tcp
    
    # Enable firewall
    ufw --force enable
    
    log_success "Firewall configured"
}

start_services() {
    log_info "Starting services..."
    
    cd "$DEPLOY_DIR"
    
    # Stop existing containers
    docker-compose down || true
    
    # Build and start containers
    docker-compose up --build -d
    
    # Wait for services to start
    log_info "Waiting for services to start..."
    sleep 30
    
    # Check if services are running
    if docker-compose ps | grep -q "Up"; then
        log_success "Services started successfully"
    else
        log_error "Failed to start services"
        docker-compose logs
        exit 1
    fi
}

setup_monitoring() {
    log_info "Setting up monitoring..."
    
    # Create monitoring script
    cat > /usr/local/bin/tamagotchi-monitor.sh << 'EOF'
#!/bin/bash

DEPLOY_DIR="/opt/tamagotchi-api"
cd "$DEPLOY_DIR"

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
    echo "$(date): Services are down. Restarting..." >> /var/log/tamagotchi-monitor.log
    docker-compose up -d
fi

# Check application health
if ! curl -f http://localhost/health > /dev/null 2>&1; then
    echo "$(date): Health check failed" >> /var/log/tamagotchi-monitor.log
fi
EOF

    chmod +x /usr/local/bin/tamagotchi-monitor.sh
    
    # Add to crontab
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/tamagotchi-monitor.sh") | crontab -
    
    log_success "Monitoring script installed"
}

setup_logrotate() {
    log_info "Setting up log rotation..."
    
    cat > /etc/logrotate.d/tamagotchi << 'EOF'
/opt/tamagotchi-api/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        docker-compose -f /opt/tamagotchi-api/docker-compose.yml restart app
    endscript
}
EOF

    log_success "Log rotation configured"
}

run_health_check() {
    log_info "Running health checks..."
    
    # Check if containers are running
    cd "$DEPLOY_DIR"
    if ! docker-compose ps | grep -q "Up"; then
        log_error "Containers are not running"
        return 1
    fi
    
    # Check application health endpoint
    sleep 10
    if curl -f http://localhost/health > /dev/null 2>&1; then
        log_success "Application health check passed"
    else
        log_warning "Application health check failed. Checking logs..."
        docker-compose logs app | tail -20
    fi
    
    # Check nginx
    if curl -f http://localhost > /dev/null 2>&1; then
        log_success "Nginx is responding"
    else
        log_error "Nginx is not responding"
        docker-compose logs nginx | tail -20
    fi
}

show_status() {
    log_info "Deployment Status:"
    echo ""
    
    cd "$DEPLOY_DIR"
    
    echo "Container Status:"
    docker-compose ps
    echo ""
    
    echo "Application URLs:"
    echo "  - API: http://$(hostname -I | awk '{print $1}')"
    echo "  - Health: http://$(hostname -I | awk '{print $1}')/health"
    echo "  - MailHog: http://$(hostname -I | awk '{print $1}'):8025"
    echo ""
    
    echo "Useful Commands:"
    echo "  - View logs: docker-compose logs -f"
    echo "  - Restart: docker-compose restart"
    echo "  - Update: cd $DEPLOY_DIR && git pull && docker-compose up --build -d"
    echo ""
}

main() {
    log_info "Starting Tamagotchi API deployment..."
    
    check_root
    check_dependencies
    create_backup
    deploy_application
    setup_ssl
    setup_firewall
    start_services
    setup_monitoring
    setup_logrotate
    run_health_check
    show_status
    
    log_success "Deployment completed successfully!"
    log_warning "Don't forget to:"
    log_warning "1. Update .env.docker with production settings"
    log_warning "2. Replace self-signed SSL certificates with valid ones"
    log_warning "3. Set up domain name and DNS"
    log_warning "4. Configure backup strategy"
}

# Run main function
main "$@"
