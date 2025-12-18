#!/bin/bash
set -e

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [BACKEND] $1"
}

echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "%%%%%%%%%%%%                                                                           %%%%%%%%%%%%%"
echo "%%%%%%%%%%%%                      WELCOME TO ASPEN DISCOVERY                           %%%%%%%%%%%%%"
echo "%%%%%%%%%%%%                                                                           %%%%%%%%%%%%%"
echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
echo "                                        "
echo "                                        "
echo "                                        "
echo "                                        "
echo "                                        "


log "Aspen Discovery starting for: ${SITE_NAME}"

export CONFIG_DIRECTORY="/usr/local/aspen-discovery/sites/$SITE_NAME"

# Move to docker directory
cd "/usr/local/aspen-discovery/docker/files/scripts" || exit

# Check if site configuration exists
confSiteFile="$CONFIG_DIRECTORY/conf/config.ini"
if [ ! -f "$confSiteFile" ] ; then
	log "$confSiteFile not found. Generating..."
	mkdir -p "$CONFIG_DIRECTORY"
	if ! php createConfig.php "$CONFIG_DIRECTORY" ; then
		log "ERROR: Failed to create instance config"
		exit 1
	fi
fi

# Sync environment variables to config files (runs every start)
log "Syncing environment variables to config..."
if ! php syncEnvToConfig.php ; then
	log "WARNING: Environment sync failed, using existing config"
fi

# Initialize Aspen database
log "Initializing database";
if ! php initDatabase.php ; then
	log "ERROR: Database initialization failed"
	exit 1
fi

# Initialize Koha Connection
log "Initializing Koha link";
if ! php initKohaLink.php ; then
	log "ERROR: Koha link error"
	exit 1
fi

# Create missing dirs and fix ownership and permissions if needed
log "Setting up data and log directories";
if ! php createDirs.php ; then
	log "ERROR: Directories creation and permission fixes failed"
	exit 1
fi

# FIXME: This seems to be creating dirs like images/images, etc
#        It should be put outside the codebase, and mounted accordingly

# Move and create temporarily sym-links to data directory
dataDir="/data/aspen-discovery/$SITE_NAME"
localDir="/usr/local/aspen-discovery/code/web"

directories=(files images fonts)
for dir in "${directories[@]}"; do

	source="$localDir/$dir"
    dest="$dataDir/$dir"

    # Ensure persistent target directory exists
    mkdir -p "$dest"

    # Move original data only if source is a real directory and target is empty
    if [ -d "$source" ] && [ ! -L "$source" ] && [ "$(ls -A "$dest")" == "" ]; then
        mv "$source"/* "$dest"/ 2>/dev/null || true
    fi

    # Remove source only if it's a real directory (not a symlink)
    # This is required because ln -sfn can't replace a directory
    if [ -d "$source" ] && [ ! -L "$source" ]; then
        rm -rf "$source"
    fi

    # Create symlink atomically (ln -sfn replaces existing symlink in one operation)
    ln -sfn "$dest" "$source"

    log "Created symlink: $source → $dest"
done

# Run pending database updates
log "Running pending database updates..."
php updateDatabase.php "$SITE_NAME"

sudo -u www-data php /usr/local/aspen-discovery/docker/files/cron/checkBackgroundProcessesDocker.php $SITE_NAME >/proc/1/fd/1 2>/proc/1/fd/2

log "Starting PHP-FPM in foreground mode..."
php-fpm8.4 --test && exec php-fpm8.4 -F