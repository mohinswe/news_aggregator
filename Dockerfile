# Use the official PHP image with FPM
FROM php:8.3-fpm

# Install Supervisor and other dependencies
RUN apt-get update && apt-get install -y \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    mariadb-client \
    supervisor 

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Copy Laravel project files into the container
COPY ./news-aggregator /var/www/html

# Copy Supervisor configuration file
COPY ./supervisord.conf /etc/supervisord.conf

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies (production-ready)
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress

# Set ownership and permissions for Laravel's writable directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the Laravel application port
EXPOSE 8080

# Start Supervisor to manage Laravel and related processes
# CMD ["supervisord", "-c", "/etc/supervisord.conf"]


CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

