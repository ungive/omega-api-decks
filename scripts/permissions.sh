#!/bin/bash

if [ $EUID != 0 ]; then
    sudo "$0" "$@"
    exit $?
fi

USER=${1:-$SUDO_USER}

WWW_UID=$(docker compose exec development id www-data -u | tr -d '\r')
WWW_GID=$(docker compose exec development id www-data -g | tr -d '\r')
echo "www-data uid:gid: $WWW_UID:$WWW_GID"

set -x

mkdir -p data
chown -R $WWW_UID:$WWW_GID data
chmod g+s data
