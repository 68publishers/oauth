FROM php:8.1.25-cli-alpine3.18 AS php81

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apk add --no-cache ${PHPIZE_DEPS} \
    && pecl install pcov \
    && pecl install uopz-7.1.1 \
    && docker-php-ext-enable pcov uopz

CMD tail -f /dev/null

FROM php:8.2.13RC1-cli-alpine3.18 AS php82

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apk add --no-cache ${PHPIZE_DEPS} \
    && pecl install pcov \
    && pecl install uopz-7.1.1 \
    && docker-php-ext-enable pcov uopz

CMD tail -f /dev/null
