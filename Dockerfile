FROM php:8.3

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json ./
RUN composer update --no-interaction --prefer-dist

COPY . .

CMD ["php", "-a"]
