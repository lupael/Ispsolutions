FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pcntl bcmath opcache

# Install Redis extension
RUN apk add --no-cache redis \
    && mkdir -p /usr/src/php/ext/redis \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/refs/tags/6.0.2.tar.gz | tar xz -C /usr/src/php/ext/redis --strip 1 \
    && docker-php-ext-install redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www

# Copy application files
COPY --chown=www:www . /var/www/html

# Set proper permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install dependencies
USER www

# PHP production configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]
