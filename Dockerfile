FROM php:7.2-apache
LABEL maintainer="Katelyn Gigante"

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN mkdir -p /var/www/html/
WORKDIR /var/www/html/
COPY ./gatherling /var/www/html/

## Expose used ports
EXPOSE 80

ENV LOG_STDOUT true
ENV LOG_STDERR true
ENV LOG_LEVEL debug
