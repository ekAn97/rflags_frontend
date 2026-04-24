FROM php:8.3-apache AS web

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip curl git nano \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql zip

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . /var/www/html

# Fix 1: correct filename is env.example, not .env.example
# Fix 2: override drivers that would try to hit a DB at build time
RUN cp env.example .env \
    && sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env \
    && sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env \
    && sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' .env

RUN composer install --no-dev --optimize-autoloader

RUN npm ci || npm install
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

RUN php artisan key:generate \
    && mkdir -p storage/logs && touch storage/logs/laravel.log

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["apache2-foreground"]
