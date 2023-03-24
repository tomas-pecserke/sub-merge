FROM php:8.2-cli-alpine

RUN set -xe \
  && apk add --no-cache --virtual .build-deps icu-dev libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev libpng-dev\
  && docker-php-ext-install intl gd \
  && apk del --no-network .build-deps

RUN apk add --no-cache beanstalkd ffmpeg icu-libs supervisor

COPY supervisord.conf /etc/supervisor/conf.d/
COPY symfony-cron /etc/crontabs/

VOLUME ["/config"]

EXPOSE 80

WORKDIR /app
CMD supervisord -c /etc/supervisor/conf.d/supervisord.conf
