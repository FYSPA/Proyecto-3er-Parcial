FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev zlib1g-dev libzip-dev zip unzip git \
    && docker-php-ext-install gd pdo pdo_mysql mysqli opcache \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuración recomendada para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar solo archivos de dependencias primero (cache layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto del código
COPY . .

# Dar permisos finales
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html