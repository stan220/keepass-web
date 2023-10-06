#!/bin/bash

echo "[pull code] update code from bitbucket repository"
git pull origin master

echo "[check requirements]"
symfony check:requirements

echo "[composer install] check package to install"
composer install --optimize-autoloader
#composer install --no-dev --optimize-autoloader

echo "[cache clear]"
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

