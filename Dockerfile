# -------------------------------
# Stage 1: Builder
# -------------------------------
FROM php:8.2-cli AS builder

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    unzip git libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    libcurl4-openssl-dev libssl-dev libfreetype6-dev libjpeg62-turbo-dev \
    libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_pgsql zip soap gd bcmath intl curl xml mbstring fileinfo exif \
 && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir directorio de trabajo
WORKDIR /app

# Copiar todo el proyecto (asegúrate de que composer.json está en la raíz)
COPY . .

# Permisos seguros
RUN chown -R www-data:www-data /app \
 && chmod -R 775 /app

# Ejecutar Composer como www-data
USER www-data
RUN composer install --no-dev --optimize-autoloader --no-interaction
USER root

# -------------------------------
# Stage 2: Production
# -------------------------------
FROM php:8.2-apache

# Copiar el código y dependencias desde el builder
COPY --from=builder /app /var/www/html

# Crear directorios críticos y asignar permisos
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
 && a2enmod rewrite \
 && sed -i "s#/var/www/html#/var/www/html/public#g" /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Exponer puerto 80
EXPOSE 80

# Ejecutar migraciones y levantar Apache como www-data
USER www-data
CMD bash -c "php artisan migrate --force && apache2-foreground"
