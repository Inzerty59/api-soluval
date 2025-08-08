FROM php:8.2-apache

# ====== Config mémoire PHP (illimité par défaut) ======
ARG PHP_MEMORY_LIMIT=-1

# ====== Apache & dépendances système ======
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
       locales apt-utils curl ca-certificates gnupg \
       git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt1-dev \
    && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

# Activer les modules Apache utiles à Symfony / proxy / SSL
RUN a2enmod rewrite ssl proxy proxy_http headers

# ====== Composer ======
RUN curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer

# ====== Node.js 18 LTS ======
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y --no-install-recommends nodejs

# ====== Symfony CLI (optionnel mais pratique) ======
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin

# ====== Extensions PHP “classiques” ======
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) pdo_mysql opcache intl zip calendar dom mbstring gd xsl \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# ====== Extension PHP SSH2 (pour ssh2_connect / ssh2_sftp) ======
# On installe les dépendances de build, on compile l’extension via PECL,
# on active l’extension puis on nettoie les paquets de build pour garder l’image légère.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libssh2-1 libssh2-1-dev autoconf make gcc g++ pkg-config \
    && pecl install ssh2-1.4.1 \
    && docker-php-ext-enable ssh2 \
    && apt-get purge -y autoconf make gcc g++ libssh2-1-dev \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# ====== Mémoire PHP ======
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini

# ====== Apache vhost par défaut ======
RUN a2ensite 000-default.conf && a2dissite default-ssl.conf

# ====== Code de l'app ======
WORKDIR /var/www/api-soluval
COPY . /var/www/api-soluval

# ====== Permissions Symfony ======
RUN chown -R www-data:www-data /var/www/api-soluval \
    && chmod -R 775 /var/www/api-soluval/var \
    && chown -R www-data:www-data /var/www/api-soluval/var/log \
    && chmod -R 775 /var/www/api-soluval/var/log \
    && touch /var/www/api-soluval/var/log/prod.log \
    && chown www-data:www-data /var/www/api-soluval/var/log/prod.log \
    && chmod 664 /var/www/api-soluval/var/log/prod.log

EXPOSE 80
CMD ["apache2-foreground"]
