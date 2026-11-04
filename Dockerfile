FROM php:8.4-alpine AS base

ARG APP_ENV=production

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    unzip \
    curl \
    supervisor \
    linux-headers \
  && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pdo_mysql \
    zip \
    opcache \
  && pecl install swoole \
  && docker-php-ext-enable swoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --no-interaction --optimize --classmap-authoritative

FROM base AS dev

RUN apk add --no-cache linux-headers \
  && pecl install pcov xdebug \
  && docker-php-ext-enable pcov xdebug

RUN composer install --no-interaction --no-progress

FROM base AS production

COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
