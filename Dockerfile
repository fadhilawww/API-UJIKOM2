# 1. Gunakan image dasar php versi CLI
FROM php:8.4-cli

# 2. Instal depedensi sistem dasar dan ekstensi PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql
# 3. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Tetapkan direktori kerja utama didalam container
WORKDIR /var/www