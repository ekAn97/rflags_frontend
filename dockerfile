FROM php:8.3-apache AS web

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    nano \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN docker-php-ext-install pdo_mysql zip

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html

WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1

# 1. Composer first — Laravel's autoloader is needed by Vite/mix
RUN composer install --no-dev --optimize-autoloader

# 2. Copy .env and generate key BEFORE npm build (Vite may read APP_* vars)
RUN cp .env.example .env
RUN php artisan key:generate

# 3. npm build last
RUN npm ci --prefer-offline || npm install
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

RUN mkdir -p /var/www/html/storage/logs && touch /var/www/html/storage/logs/laravel.log

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["apache2-foreground"]
