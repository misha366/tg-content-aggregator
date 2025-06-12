FROM php:8.2-alpine
WORKDIR /var/www/laravel
RUN docker-php-ext-install pdo pdo_mysql

RUN apk add --no-cache supervisor
COPY supervisor/supervisord.conf /etc/supervisord.conf

RUN apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
    && echo "Europe/Moscow" > /etc/timezone

RUN apk add --no-cache busybox-suid
COPY cron/laravel-schedule /etc/crontabs/root
