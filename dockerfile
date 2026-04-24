FROM php:8.3-apache AS web

# System deps
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip curl git nano \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# PHP extensions
RUN docker-php-ext-install pdo_mysql zip

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# ── Copy only dependency manifests first (better layer caching) ──
COPY composer.json composer.lock ./

# Run composer BEFORE npm (less memory pressure, faster cache invalidation)
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# Now copy the full project
COPY . /var/www/html

# Re-run autoloader with scripts now that all files are present
RUN composer dump-autoload --optimize --no-dev

# npm build
RUN npm ci || npm install
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

# Laravel setup
RUN cp .env.example .env \
    && php artisan key:generate \
    && mkdir -p storage/logs && touch storage/logs/laravel.log

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["apache2-foreground"]
