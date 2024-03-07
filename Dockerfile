# Use the official image as a parent image
FROM php:7.4-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install project dependencies
RUN composer install --no-dev --optimize-autoloader

# Change owner of our applications
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Copy existing application directory permissions
COPY --chown=www-data:www-data . .

# Change current user to www
USER www-data

# Generate key
RUN php artisan key:generate

# Migrate the database
RUN php artisan migrate

# Start Apache service
CMD ["apache2-foreground"]