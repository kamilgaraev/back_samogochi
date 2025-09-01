# Multi-stage Docker build for Laravel application (Debian-based)
FROM php:8.3-fpm-bookworm AS base

# Improve apt network resilience (force IPv4, retries) and switch to reliable mirrors
RUN set -eux; \
  printf 'Acquire::Retries "5";\nAcquire::http::Pipeline-Depth "0";\nAcquire::ForceIPv4 "true";\n' > /etc/apt/apt.conf.d/99network; \
  rm -f /etc/apt/sources.list.d/debian.sources || true; \
  printf 'deb http://mirror.yandex.ru/debian bookworm main contrib non-free non-free-firmware\n' > /etc/apt/sources.list; \
  printf 'deb http://mirror.yandex.ru/debian bookworm-updates main contrib non-free non-free-firmware\n' >> /etc/apt/sources.list; \
  printf 'deb http://mirror.yandex.ru/debian-security bookworm-security main contrib non-free non-free-firmware\n' >> /etc/apt/sources.list

# Force reliable DNS inside build stage (workaround for hosting DNS issues)
RUN rm -f /etc/resolv.conf \
  && printf 'nameserver 1.1.1.1\nnameserver 8.8.8.8\n' > /etc/resolv.conf

# Install system dependencies
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git curl zip unzip \
    libpq-dev libzip-dev libxml2-dev \
    libjpeg62-turbo-dev libfreetype6-dev libpng-dev \
    autoconf pkg-config make g++ \
  && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
     gd pdo pdo_pgsql mbstring exif pcntl bcmath zip

# Install Redis extension
RUN pecl install redis \
  && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Leverage build cache for composer dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Copy application code
COPY . .

# Re-run composer to install app code specific autoload files (idempotent)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions expected by Laravel
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache

# Production stage
FROM base AS production

# Production php.ini
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini

# Expose PHP-FPM port
EXPOSE 9000

# Default command: php-fpm
CMD ["php-fpm"]

# Development stage
FROM base AS development

# Install and enable Xdebug
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug

# Development php.ini
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini

EXPOSE 9000
CMD ["php-fpm"]
