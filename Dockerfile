FROM railway/rails-nixpacks:latest

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install PHP and required extensions
RUN apt-get update && apt-get install -y \
    php8.3-cli \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-sqlite3 \
    php8.3-gd \
    php8.3-curl \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-zip \
    composer \
    && rm -rf /var/lib/apt/lists/*

# Install dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Generate APP_KEY if not set
RUN if [ -z "$APP_KEY" ]; then php artisan key:generate; fi

# Run migrations
RUN php artisan migrate --force

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public/"]
