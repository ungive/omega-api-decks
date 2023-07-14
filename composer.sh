#!/bin/bash

TEMP_CONTAINER_NAME=omega-api-decks-composer-update-b87b0498cb3bc649

echo "Running command: composer $@"

docker build -f composer.dockerfile -t "$TEMP_CONTAINER_NAME" . \
    && docker container run --rm -v $(pwd):/app/ "$TEMP_CONTAINER_NAME" \
        /bin/bash -c "cd app; composer $@" \
    && docker image rm "$TEMP_CONTAINER_NAME"
