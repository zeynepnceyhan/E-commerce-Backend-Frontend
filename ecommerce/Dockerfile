# PHP 8.1 imajını kullan
FROM php:8.1-apache

# Redis için PHP eklentisini kur
RUN apt-get update && \
    apt-get install -y libzip-dev libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    pecl install redis && \
    docker-php-ext-enable redis

# Apache yapılandırma dosyalarını kopyala
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# PHP dosyalarını kopyala
COPY ./src /var/www/html

# Apache'yi başlat
CMD ["apache2-foreground"]
