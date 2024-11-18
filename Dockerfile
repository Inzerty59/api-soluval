FROM php:8.2-apache

# Configurer Apache et installer les dépendances nécessaires
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
    locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libpng-dev libonig-dev libxslt1-dev \
    && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

# Activer le module rewrite pour Symfony
RUN a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer

# Installer Node.js
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash \
    && apt-get install -y nodejs

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin

# Installer les extensions PHP nécessaires
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
    pdo_mysql opcache intl zip calendar dom mbstring gd xsl \
    && pecl install apcu && docker-php-ext-enable apcu

# Définir le répertoire de travail
WORKDIR /var/www/api-soluval

# Copier les fichiers du projet dans le conteneur
COPY . /var/www/api-soluval

# Régler les permissions pour Apache
RUN chown -R www-data:www-data /var/www/api-soluval
