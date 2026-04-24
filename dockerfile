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

RUN cp env.example .env \
    && sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env \
    && sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env \
    && sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' .env

# Create bootstrap/cache before composer so package:discover can write to it
RUN mkdir -p bootstrap/cache storage/logs \
    && chmod -R 775 bootstrap/cache storage

RUN php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader

RUN npm ci || npm install
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

RUN php artisan key:generate \
    && touch storage/logs/laravel.log

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["apache2-foreground"]
