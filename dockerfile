FROM php:8.3-apache AS web

# Εγκατάσταση απαιτούμενων πακέτων
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    nano \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Ενεργοποίηση Apache mod_rewrite
RUN a2enmod rewrite

# Εγκατάσταση PHP extensions
RUN docker-php-ext-install pdo_mysql zip

# Εγκατάσταση Node.js και npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

# Ρύθμιση DocumentRoot του Apache για Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Αντιγραφή του project
COPY . /var/www/html

# Ορισμός Working Directory
WORKDIR /var/www/html

# Εγκατάσταση Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ορισμός περιβάλλοντος για τον Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Εγκατάσταση των npm εξαρτήσεων
RUN npm ci --prefer-offline || npm install
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

RUN composer install --no-dev --optimize-autoloader
# Αντιγραφή .env και δημιουργία κλειδιού Laravel
RUN cp .env.example .env
RUN php artisan key:generate

RUN mkdir -p /var/www/html/storage/logs && touch /var/www/html/storage/logs/laravel.log

# Ορισμός σωστών δικαιωμάτων σε storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Εκκίνηση του Apache
CMD ["apache2-foreground"]