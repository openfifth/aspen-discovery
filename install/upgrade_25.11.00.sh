#!/bin/bash

if [ -z "$1" ]
  then
    echo "Please provide the server name to update as the first argument."
    exit 1
fi

echo "Updating log4j pattern layouts for $1..."
php /usr/local/aspen-discovery/install/update_log4j_patterns.php "$1"