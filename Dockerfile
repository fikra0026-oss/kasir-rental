# Multi-stage build
FROM php:8.3-cli AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl git unzip zip pkg-config \
    libpq-dev libgd-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libcurl4-openssl-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql pdo_mysql gd bcmath curl mbstring xml zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Runtime stage
FROM php:8.3-apache

# Copy compiled PHP extensions from builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

# Install runtime dependencies only (no dev packages)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq5 libgd3 libfreetype6 libjpeg62-turbo libpng16-16 \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-enable pdo pdo_pgsql pdo_mysql gd bcmath curl mbstring xml zip \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY --from=builder /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s|AllowOverride None|AllowOverride All|' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
