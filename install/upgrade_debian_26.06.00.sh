#!/bin/bash

apt-get install -y composer
composer --version
cd /usr/local/aspen-discovery/code/web || exit
composer install --no-interaction --prefer-dist
composer check-platform-reqs
