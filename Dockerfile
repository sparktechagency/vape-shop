# Use PHP 8.2 with FPM as the base image
FROM php:8.2-fpm

# Set environment variable to install packages without user interaction
ENV DEBIAN_FRONTEND=noninteractive

# Install essential system dependencies and PHP extension dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev

# Configure and install the gd extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install other required PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring exif pcntl bcmath zip

# Get the latest Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www

# Copy the application source code into the container
# The 'vendor' directory will be created by the composer install command
COPY . .

# Install Composer dependencies
RUN composer install --no-interaction --no-plugins --no-scripts --optimize-autoloader


# First, we set the ownership and permissions.
# Then, using '&&', we start the main process, 'php-fpm'.
CMD /bin/sh -c 'chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache && php-fpm'

# Expose port 9000 for PHP-FPM
EXPOSE 9000
