FROM php:8.3-cli-alpine

RUN php --version

ENV COMPOSER_ALLOW_SUPERUSER 1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk add git \
    && rm -rf /var/cache/apk/* /var/tmp/* /tmp/*

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions json pcntl

ARG COMPOSER_REQUIRE_VERSION=dev-main

RUN composer global require marcelthole/openapi-merge:"$COMPOSER_REQUIRE_VERSION" \
	&& composer clear-cache

VOLUME ["/app"]
WORKDIR /app

ENTRYPOINT ["/root/.composer/vendor/bin/openapi-merge"]
