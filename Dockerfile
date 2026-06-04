FROM php:8.2-apache

# 1. Instalar dependencias del sistema (combinadas para PostgreSQL, MongoDB y Composer)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Actualizar certificados de seguridad y habilitar mod_rewrite de Apache
RUN update-ca-certificates && a2enmod rewrite

# 3. Instalar extensiones de PHP (PostgreSQL nativo y MongoDB vía PECL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql \
    && pecl install mongodb-1.20.0 \
    && docker-php-ext-enable mongodb

# 4. Traer Composer desde su imagen oficial y configurar entorno
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 5. Definir el directorio de trabajo
WORKDIR /var/www/html

# 6. Copiar el código del proyecto al contenedor
COPY . .

# 7. Instalar las dependencias de Composer
RUN composer install --no-interaction --optimize-autoloader

# 8. Ajustar permisos para que Apache (www-data) pueda manejar los archivos
RUN chown -R www-data:www-data /var/www/html

# 9. Exponer el puerto 80
EXPOSE 80