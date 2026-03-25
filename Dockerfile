FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    unzip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install zip pdo mbstring json intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-interaction --prefer-dist

COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
