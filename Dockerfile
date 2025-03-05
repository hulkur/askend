FROM php:8.2-cli

RUN apt-get update -y && apt-get install -y libmcrypt-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_sqlite sqlite3 mbstring

WORKDIR /var/www/html
COPY . /var/www/html

RUN composer install

RUN PATH=$PATH:/var/www/html/bin:/var/www/html/vendor/bin

CMD ["php-fpm"]
