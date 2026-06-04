FROM php:8.2-apache

# 1. Instalar dependencias del sistema esenciales
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    libpq-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Actualizar los certificados de seguridad del contenedor
RUN update-ca-certificates

# 3. Instalar TODAS las extensiones de PHP (Postgres y MongoDB)
RUN docker-php-ext-install pdo pdo_pgsql pgsql \
    && pecl install mongodb-1.20.0 \
    && docker-php-ext-enable mongodb

# 4. Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# 5. Traer Composer de forma segura desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 6. Definir el directorio de trabajo
WORKDIR /var/www/html

# 7. Copiar primero de forma estricta los archivos de Composer
COPY composer.json ./

# 8. Instalar dependencias (Esto creará la carpeta /var/www/html/vendor)
RUN composer install --no-interaction --optimize-autoloader

# 9. Copiar todo el resto del código del repositorio (incluyendo la carpeta controlador)
COPY . .

# 10. Ajustar permisos para Apache
RUN chown -R www-data:www-data /var/www/html

# 11. Exponer el puerto 80
EXPOSE 80
