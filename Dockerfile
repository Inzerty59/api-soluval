FROM php:8.2-apache

# Déclaration de l'argument de build PHP_MEMORY_LIMIT avec une valeur par défaut (-1 ici)
ARG PHP_MEMORY_LIMIT=-1

# Configurer Apache et installer les dépendances
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
    locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt1-dev \
    && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

# Activer les modules nécessaires pour Symfony, SSL et reverse proxy
RUN a2enmod rewrite ssl proxy proxy_http headers

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
    && docker-php-ext-install pdo_mysql opcache intl zip calendar dom mbstring gd xsl \
    && pecl install apcu && docker-php-ext-enable apcu

# Définir la limite de mémoire PHP en utilisant l'argument de build
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini

# Activation du site par défaut (la configuration sera remplacée par le volume monté)
RUN a2ensite 000-default.conf && a2dissite default-ssl.conf

# Définir le répertoire de travail
WORKDIR /var/www/api-soluval

# Copier les fichiers du projet dans le container
COPY . /var/www/api-soluval

# Régler les permissions pour Apache et Symfony
RUN chown -R www-data:www-data /var/www/api-soluval \
    && chmod -R 775 /var/www/api-soluval/var

# Régler les permissions pour le répertoire de logs
RUN chown -R www-data:www-data /var/www/api-soluval/var/log \
    && chmod -R 775 /var/www/api-soluval/var/log

# Créer le fichier de log et régler les permissions
RUN touch /var/www/api-soluval/var/log/prod.log \
    && chown www-data:www-data /var/www/api-soluval/var/log/prod.log \
    && chmod 664 /var/www/api-soluval/var/log/prod.log

# Exposer le port 80 (Traefik gère la terminaison SSL)
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]
