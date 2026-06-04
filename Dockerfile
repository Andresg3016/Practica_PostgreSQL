FROM php:8.2-apache

# 1. Instalar dependencias del sistema esenciales
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Actualizar los certificados de seguridad del contenedor
RUN update-ca-certificates

# 3. INSTALACIÓN CRÍTICA: Forzar la versión de la extensión compatible con tu código (Rama 1.x)
RUN pecl install mongodb-1.20.0 \
    && docker-php-ext-enable mongodb

# 4. Habilitar el módulo de reescritura de Apache (útil para URLs amigables si las usas)
RUN a2enmod rewrite

# 5. Traer Composer de forma segura desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Permitir que Composer se ejecute como Root dentro de Docker sin alertas
ENV COMPOSER_ALLOW_SUPERUSER=1

# 7. Definir el directorio donde Apache busca la web
WORKDIR /var/www/html

# 8. Copiar todo el código de tu repositorio dentro del contenedor
COPY . .

# 9. Ajustar permisos para que el servidor web Apache (www-data) pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# 10. Instalar las dependencias de Composer de manera optimizada y sin saltarse la extensión
RUN composer install --no-interaction --optimize-autoloader

#11. PostgreSQL

RUN docker-php-ext-install pdo pdo_pgsql pgsql \
    && pecl install mongodb-1.20.0 \
# 12. Exponer el puerto 80 para que Render pueda redirigir el tráfico web
EXPOSE 80
