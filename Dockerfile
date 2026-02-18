# Builder stage
FROM node:20-alpine as node_builder

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY resources/ ./resources/
COPY vite.config.js ./
RUN npm run build

# Composer stage
FROM composer:latest as composer_builder

WORKDIR /build

# Copy all application files needed for composer install
COPY composer.json composer.lock ./
COPY artisan ./
COPY app/ ./app/
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY public/ ./public/
COPY resources/ ./resources/
COPY routes/ ./routes/
COPY storage/ ./storage/
COPY .env.example ./

# Install dependencies without running scripts (artisan not fully available yet)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Production stage
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy built assets from node_builder
COPY --from=node_builder /build/public/build ./public/build

# Copy vendor dependencies from composer_builder
COPY --from=composer_builder /build/vendor ./vendor

# Generate optimized autoloader and discover packages
RUN composer dump-autoload --optimize && \
    php artisan package:discover --ansi

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Optimize Laravel
RUN php artisan optimize && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port (Render uses PORT environment variable)
EXPOSE 8080

# Start Laravel server
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
