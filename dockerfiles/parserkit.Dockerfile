FROM php:8.2-bookworm
WORKDIR /var/www/laravel
RUN apt-get update && apt-get install -y \
    python3 python3-venv \
    git
RUN docker-php-ext-install pdo pdo_mysql
