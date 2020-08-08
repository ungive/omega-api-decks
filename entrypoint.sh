#!/bin/bash

$(cd scripts; ./install.sh /opt/bin)

if [ "$#" -gt "0" ]; then
    $@; exit
fi

set +x

# TODO: if the database already exists, then run the update in the background
#  otherwise wait until the update is complete

update-database

# httpd -D FOREGROUND
docker-php-entrypoint apache2-foreground
