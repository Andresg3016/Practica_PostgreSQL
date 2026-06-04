FROM php:8.2-apache

# 1. Instalar dependencias del sistema y librerías de desarrollo para Postgres
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

# 3. Instalar TODAS las extensiones de PHP primero (Postgres y MongoDB)
RUN docker-php-ext-install pdo pdo_pgsql pgsql \
    && pecl install mongodb-1.20.0 \
    && docker-php-ext-enable mongodb

# 4. Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# 5. Traer Composer de forma segura desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Permitir que Composer se ejecute como Root dentro de Docker sin alertas
ENV COMPOSER_ALLOW_SUPERUSER=1

# 7. Definir el directorio de trabajo
WORKDIR /var/www/html

# 8. Copiar todo el código de tu repositorio dentro del contenedor
COPY . .

# 9. Instalar las dependencias de Composer (Ahora sí con todas las extensiones PHP listas)
RUN composer install --no-interaction --optimize-autoloader

# 10. Ajustar permisos para que Apache (www-data) pueda leer TODO (incluyendo la carpeta vendor nueva)
RUN chown -R www-data:www-data /var/www/html

# 11. Exponer el puerto 80 para Render u otros servicios
EXPOSE 80
