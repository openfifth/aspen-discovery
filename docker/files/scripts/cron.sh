#!/bin/bash

# Function to log with timestamp
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') [CRON] $1"
}

log "Starting Cron initialization"

export CONFIG_DIRECTORY="/usr/local/aspen-discovery/sites/${SITE_NAME}"

# Check if site configuration exists
confSiteFile="$CONFIG_DIRECTORY/conf/config.ini"

tries=0
MAX_TRIES=10

while [ ! -f "$confSiteFile" ]; do

	log "Waiting for site configuration: $confSiteFile... (attempt $tries/$MAX_TRIES)"
	sleep 5
	((tries++))

	if [ $tries -eq 10 ] ; then
		log "ERROR: Site configuration not initialized. Skipping cron startup and waiting..."
		exit 1
	fi

done

# Set crontab to be executed
log "Setting crontab to be executed"
crontab -u root "$CONFIG_DIRECTORY/conf/crontab" >/proc/1/fd/1 2>/proc/1/fd/2

log "Starting Cron in foreground mode..."
# Start cron daemon
cron -f -L 15 >/proc/1/fd/1 2>/proc/1/fd/2
