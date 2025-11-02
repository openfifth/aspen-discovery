#!/bin/bash
set -e

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') [ENTRYPOINT] $1"
}

USER_NAME="www-data"
SOURCE_DIR="/usr/local/aspen-discovery"
APACHE_LOG_DIR="/var/log/apache2"

if [ -z "${LOCAL_USER_ID}" ]; then
	log "LOCAL_USER_ID must be set!. Exiting..."
	exit 1
fi

if [ "$#" -eq 0 ]; then

	log "Starting container with UID=${LOCAL_USER_ID}"

	if [[ "${LOCAL_USER_ID}" != "33" ]]; then
		log "Setting UID for ${USER_NAME} to ${LOCAL_USER_ID}"
		usermod -o -u "$LOCAL_USER_ID" "$USER_NAME"
	fi

	if [[ -d "${SOURCE_DIR}" ]]; then
		log "Fixing ownership on ${SOURCE_DIR}"
		chown -R "$USER_NAME" ${SOURCE_DIR}
	fi

	exec "/start.sh"


elif [ "$1" = 'apache' ]; then

	# Adjust permissions if required
	if [[ "${LOCAL_USER_ID}" != "33" ]]; then
		log "Setting UID for ${USER_NAME} to ${LOCAL_USER_ID}"
		usermod -o -u ${LOCAL_USER_ID} "$USER_NAME"

		# Fix permissions due to UID change
		chown -R ${USER_NAME} "${APACHE_LOG_DIR}"
	fi
	chown -R "$USER_NAME" "$SOURCE_DIR"
	exec /apache.sh

elif [ "$1" = 'cron' ]; then

	# Adjust permissions if required
	if [[ "${LOCAL_USER_ID}" != "33" ]]; then
		log "Setting UID for ${USER_NAME} to ${LOCAL_USER_ID}"
		usermod -o -u ${LOCAL_USER_ID} "$USER_NAME"
	fi
	chown -R ${USER_NAME} ${SOURCE_DIR}
	exec /cron.sh

else
	exec "$@"
fi
