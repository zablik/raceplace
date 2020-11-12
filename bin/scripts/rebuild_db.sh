#!/bin/bash

cd /var/www/raceplace

php bin/console doctrine:schema:drop --force
php bin/console doctrine:migrations:version --delete --all --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
