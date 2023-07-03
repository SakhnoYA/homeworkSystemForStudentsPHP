FROM php:8.1.0-fpm

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev nginx git \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean

WORKDIR /var/www/html

COPY . .
COPY nginx-fpm.conf /etc/nginx/sites-available/default

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD ["bash", "-c", "php-fpm & nginx -g 'daemon off;'"]
