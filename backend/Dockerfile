FROM php:8.2-apache

# Instalar extensiones necesarias (mysqli para base de datos)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Instalar git y unzip (necesarios para composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Cambiar el DocumentRoot de Apache a la carpeta 'app'
ENV APACHE_DOCUMENT_ROOT /var/www/html/app

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite de Apache (útil para rutas amigables si se necesitan)
RUN a2enmod rewrite

# Permisos correctos (especialmente para uploads si se usa almacenamiento local temporal)
RUN chown -R www-data:www-data /var/www/html
