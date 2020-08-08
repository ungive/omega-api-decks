FROM php:7.4-apache

RUN DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        curl \
        git \
        zip unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

# TODO: remove Imagick

RUN install-php-extensions \
        opcache \
    && a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

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


# TODO: move this upwards
RUN install-php-extensions \
        gd \
    && a2enmod rewrite


VOLUME /opt/data
ENV DATA_DIR /opt/data
RUN mkdir -p /opt/data && \
    chown www-data /opt/data

COPY scripts /var/www/scripts
COPY entrypoint.sh /usr/local/bin/

RUN chmod +x \
    scripts/install.sh \
    /usr/local/bin/entrypoint.sh

RUN mkdir -p /opt/bin
ENV PATH="/opt/bin:${PATH}"

ENTRYPOINT ["entrypoint.sh"]
CMD []
