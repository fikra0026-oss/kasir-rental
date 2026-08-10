FROM php:8.3-apache

# Enable required PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libgd-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    gd \
    bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set document root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' /etc/apache2/apache2.conf

# Install dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
