# Gunakan PHP 8.3 dengan Apache sebagai base image
FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install ekstensi PHP yang diperlukan
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    && docker-php-ext-install \
    intl \
    mbstring \
    xml \
    pdo_mysql \
    gd \
    opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Expose port 80 (default untuk Apache)
EXPOSE 80

# Jalankan Apache sebagai default command
CMD ["apache2-foreground"]
