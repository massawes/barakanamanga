# Namanga Secondary School website: PHP 8 + Apache.
# The database runs in its own container (see docker-compose.yml).
FROM php:8.3-apache

# PHP extensions the site uses: pdo_mysql (database), gd + exif (gallery
# thumbnails / photo rotation), zip (download-all). mbstring and fileinfo
# are already built into the base image.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libjpeg62-turbo-dev libpng-dev libwebp-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd exif zip \
    && rm -rf /var/lib/apt/lists/*

# .htaccess needs mod_rewrite (sitemap.xml); the base image already
# allows .htaccess overrides under /var/www/.
RUN a2enmod rewrite headers

COPY docker/php.ini /usr/local/etc/php/conf.d/namanga.ini
COPY docker/apache.conf /etc/apache2/conf-enabled/namanga.conf
COPY docker/entrypoint.sh /usr/local/bin/namanga-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/namanga-entrypoint \
    && chmod +x /usr/local/bin/namanga-entrypoint

WORKDIR /var/www/html
COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80
ENTRYPOINT ["namanga-entrypoint"]
CMD ["apache2-foreground"]
