#!/bin/bash

# Function to log with timestamp
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') [APACHE] $1"
}

log "Starting Apache initialization"

export CONFIG_DIRECTORY="/usr/local/aspen-discovery/sites/${SITE_NAME}"

# Function to handle shutdown signals
shutdown_handler() {
    log "Received shutdown signal, stopping Apache gracefully..."
    if [ -n "$APACHE_PID" ]; then
        kill -TERM "$APACHE_PID" 2>/dev/null || true
        wait "$APACHE_PID" 2>/dev/null || true
    fi
    log "Apache stopped"
    exit 0
}

# Set up signal handlers for graceful shutdown
trap shutdown_handler SIGTERM SIGINT SIGQUIT

# Check if site configuration exists
apacheConfFile="$CONFIG_DIRECTORY/httpd-${SITE_NAME}.conf"
log "Waiting for site configuration: $apacheConfFile"

tries=0
MAX_TRIES=10

while [ ! -f "$apacheConfFile" ]; do
    sleep 5
    ((tries++))
    log "Waiting for configuration file... (attempt $tries/$MAX_TRIES)"

    if [ $tries -eq $MAX_TRIES ]; then
        log "ERROR: Site configuration not found after $MAX_TRIES attempts. Exiting."
        exit 1
    fi
done

log "Configuration file found: $apacheConfFile"

# Prepare Apache environment
log "Preparing Apache environment"
mkdir -p /var/run/apache2
chown -R www-data:www-data /var/run/apache2

# Source environment variables
if [ -f /etc/apache2/envvars ]; then
    source /etc/apache2/envvars
    log "Apache environment variables loaded"
else
    log "WARNING: /etc/apache2/envvars not found"
fi

# Move to docker directory
cd "/usr/local/aspen-discovery/docker/files/apache2/" || {
    log "ERROR: Cannot change to docker directory"
    exit 1
}

# Set Apache configurations
log "Setting Apache configurations"
if ! php setApacheConf.php "$apacheConfFile"; then
    log "ERROR: Apache configuration failed"
    exit 1
fi

log "Apache configuration completed successfully"

# Validate Apache configuration
log "Validating Apache configuration"
if ! apache2 -t; then
    log "ERROR: Apache configuration test failed"
    exit 1
fi

log "Apache configuration is valid"


# Start Apache and capture its PID
log "Starting Apache in foreground mode..."
exec apache2 -D FOREGROUND


