FROM php:8.4-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Library sistem yang diperlukan PHP dan Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        gd \
        intl \
        zip \
        mbstring \
        bcmath \
        opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Ambil Composer dari image resmi Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Salin Composer files lebih dahulu agar layer dependency dapat di-cache
COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader

# Salin seluruh aplikasi
COPY . /var/www/html

# Instal dependency dan jalankan Composer scripts aplikasi
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html

# Konfigurasi virtual host Apache
COPY docker/apache-vhost.conf \
    /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]