# Use an official PHP image as the base image
FROM php:8.3-fpm

# Set working directory in the container
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    curl \
    && docker-php-ext-install \
    intl \
    mbstring \
    opcache \
    pdo_mysql \
    zip \
    xml \
    curl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the project files into the container
COPY . /app

# Ensure cache and logs directories exist and have proper permissions
RUN mkdir -p /app/var/cache /app/var/log && \
    chown -R www-data:www-data /app/var && \
    chmod -R 775 /app/var

# Install Symfony dependencies
RUN composer install

# Set permissions for vendor directory
RUN chown -R www-data:www-data /app/vendor && \
    chmod -R 775 /app/vendor

# Start PHP-FPM
CMD ["php-fpm"]
