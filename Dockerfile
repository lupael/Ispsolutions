# Build stage
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    nodejs \
    npm \
    rrdtool-dev \
    rrdtool

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install RRD PHP extension
RUN pecl install rrd \
    && docker-php-ext-enable rrd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy dependency files
COPY composer.json composer.lock package.json package-lock.json ./

# Install dependencies
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts \
    && npm ci --omit=dev

# Copy application code
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Production stage
FROM php:8.2-fpm-alpine

# Install runtime dependencies only
RUN apk add --no-cache \
    libpng \
    libzip \
    libjpeg-turbo \
    freetype \
    mysql-client \
    nodejs \
    npm \
    bash \
    rrdtool

# Copy compiled PHP extensions from builder instead of recompiling
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Set working directory
WORKDIR /var/www

# Copy application and dependencies from builder
COPY --from=builder --chown=www-data:www-data /var/www /var/www

# Create storage directories and set permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/rrd \
    && mkdir -p storage/app/graphs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose port 8000
EXPOSE 8000

# Health check
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s --retries=3 \
    CMD php -v || exit 1

# Switch to non-root user
USER www-data

# Start PHP built-in server
# For production, use php-fpm with nginx/Apache: CMD ["php-fpm"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
