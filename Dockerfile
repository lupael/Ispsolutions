FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
RUN npm install

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 8000
EXPOSE 8000

# DEVELOPMENT: Start PHP built-in server
# PRODUCTION: Use php-fpm with nginx/Apache instead of artisan serve
# For production, change CMD to: CMD ["php-fpm"] and configure with nginx/Apache
CMD php artisan serve --host=0.0.0.0 --port=8000
