# Multi-stage build
FROM php:8.3-cli as builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl git unzip zip \
    libpq-dev libgd-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql pdo_mysql gd bcmath curl mbstring xml zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Runtime stage
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq5 libgd3 libfreetype6 libjpeg62-turbo libpng6 \
    && docker-php-ext-enable opcache \
    && a2enmod rewrite

RUN docker-php-ext-install \
    pdo pdo_pgsql pdo_mysql gd bcmath curl mbstring xml zip

WORKDIR /var/www/html
COPY --from=builder /app /var/www/html
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s|AllowOverride None|AllowOverride All|' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
