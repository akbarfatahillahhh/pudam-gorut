FROM php:8.2-apache

# Extensi yang dibutuhkan CI4 4.2 + mpdf:
#   intl, mysqli/pdo_mysql (DB), zip (nelexa/zip), gd (mpdf), mbstring/curl/json sudah built-in.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl mysqli pdo_mysql zip gd opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Isi image hanya sebagai fallback; saat runtime source di-bind-mount dari host.
COPY . .

# Docroot CI4 = public/
RUN sed -ri 's#/var/www/html#/var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' 'ServerName pudam.digiagent.web.id' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

EXPOSE 80

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
