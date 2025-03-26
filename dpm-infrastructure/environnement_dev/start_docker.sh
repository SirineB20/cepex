#!/usr/bin/env bash

export DEVELOPPER=$(whoami)


if [ ! -d $(pwd)"/sites/default/files" ]; then
    mkdir $(pwd)/sites/default/files
fi
chmod -R 755 $(pwd)/sites/default/files

cp $(pwd)/dpm-infrastructure/environnement_dev/config/settings.php sites/default/settings.php
chmod -R 644 $(pwd)/sites/default/settings.php

docker-compose -f $(pwd)/dpm-infrastructure/environnement_dev/docker-compose.yml down -v
docker-compose -f $(pwd)/dpm-infrastructure/environnement_dev/docker-compose.yml up --build
