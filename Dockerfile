# Multi-stage Docker build for Laravel application
FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    redis \
    supervisor \
    nginx \
    autoconf \
    gcc \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Production stage
FROM base AS production

# Copy configurations
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini

# Create log directories
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/www/html/storage/logs

# Expose port
EXPOSE 80

# Start supervisor (which will start nginx, php-fpm, and workers)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# Development stage
FROM base AS development

# Install development dependencies
RUN composer install --optimize-autoloader --no-interaction

# Install Xdebug for development
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy development php.ini
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini

# Copy supervisor configuration for development
COPY docker/supervisor/supervisord-dev.conf /etc/supervisor/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
