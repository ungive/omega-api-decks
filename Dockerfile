FROM php:7.4-apache

ARG BUILD_DEVELOPMENT
ENV PHP_ENV=${BUILD_DEVELOPMENT:+development}
ENV PHP_ENV=${PHP_ENV:-production}

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        curl \
        git \
        zip unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

RUN install-php-extensions \
        opcache \
        gd \
    && a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN mv $PHP_INI_DIR/php.ini-$PHP_ENV $PHP_INI_DIR/php.ini

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require hirak/prestissimo --no-plugins --no-scripts


WORKDIR /var/www

COPY composer.json .
COPY composer.lock .

RUN composer install --prefer-dist --no-dev --no-autoloader \
    && rm -rf $(composer config --global home)

COPY config /var/www/config
COPY public /var/www/public
COPY src /var/www/src

RUN composer dump-autoload --no-dev --optimize


ENV DATA_DIR /opt/data
RUN mkdir -p ${DATA_DIR} && \
    chown www-data ${DATA_DIR}
VOLUME ${DATA_DIR}

COPY scripts /var/www/scripts
COPY entrypoint.sh /usr/local/bin/

RUN chmod +x \
    scripts/install.sh \
    /usr/local/bin/entrypoint.sh

RUN mkdir -p /opt/bin
ENV PATH="/opt/bin:${PATH}"

ENTRYPOINT ["entrypoint.sh"]
CMD []
