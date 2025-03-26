FROM php:8.3-fpm

# Mise à jour et installation des dépendances système
RUN apt-get update && apt-get install -y \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        gnupg \
        default-mysql-client \
        wget \
        nano \
        netcat-openbsd \
        unzip \
        git \
        libjpeg-dev \
        zlib1g-dev \
        libldap2-dev \
        curl \
        g++ \
        acl \
        libzip-dev \
        zip \
        imagemagick \
        libmagickwand-dev \
        webp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache intl zip

# Activer les extensions
RUN docker-php-ext-enable opcache intl zip

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer

# Installer Imagick (pecl)
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Configuration PHP (upload et post size)
RUN echo "upload_max_filesize=20M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size=20M" >> /usr/local/etc/php/php.ini

# Ajouter le volume pour le code
VOLUME ["/var/www/html"]

# Ajouter les chemins pour Drush/Drupal
ENV PATH="$PATH:/var/www/html/vendor/bin"

# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
