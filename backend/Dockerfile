FROM php:8.2-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip && \
    docker-php-ext-install pdo pdo_mysql mysqli && \
    a2enmod rewrite

# Copiar archivos del backend al contenedor
COPY . /var/www/html

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
EXPOSE 80
