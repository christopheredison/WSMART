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
    libzip-dev \
    && docker-php-ext-install \
    intl \
    mbstring \
    xml \
    pdo_mysql \
    gd \
    opcache \
    zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Salin konfigurasi Apache untuk Laravel dan timpa default
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Optimalkan cache dependency composer dengan menyalin file manifest terlebih dahulu
COPY composer.json composer.lock ./
# Tahap awal: install dependency tanpa menjalankan skrip Composer (artisan belum ada)
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

# Salin seluruh source code ke image
COPY . .

# Setelah seluruh kode ada, jalankan kembali composer install agar skrip dijalankan
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Pastikan permission untuk Laravel storage & cache
RUN mkdir -p storage/framework/sessions storage/framework/cache storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache \
    && find storage -type d -exec chmod 775 {} \; \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80 (default untuk Apache)
EXPOSE 80

# Jalankan Apache sebagai default command
CMD ["apache2-foreground"]
