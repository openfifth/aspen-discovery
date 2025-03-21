#!/bin/bash
set -e

USER_NAME="www-data"
DEFAULT_UID=33

if [ "$#" -eq 0 ]; then

	echo "%   Starting container with UID=${LOCAL_USER_ID}"

	if [[ ! -z "${LOCAL_USER_ID}" && "${LOCAL_USER_ID}" != "33" ]]; then
		echo "%   Setting UID for ${USER_NAME} to ${LOCAL_USER_ID}"
		usermod -o -u "$LOCAL_USER_ID" "$USER_NAME"
	fi

	if [[ -d /usr/local/aspen-discovery ]]; then
		echo "%   Fixing ownership on /usr/local/aspen-discovery"
		chown -R "$USER_NAME" /usr/local/aspen-discovery
	fi

	exec "/start.sh"


elif [ "$1" = 'apache' ]; then

	# Adjust permissions if required
	if [[ ! -z "${LOCAL_USER_ID}" && "${LOCAL_USER_ID}" != "33" ]]; then
		echo "%   Setting www-data to UID=${LOCAL_USER_ID}"
		usermod -o -u ${LOCAL_USER_ID} "$USER_NAME"
		# Fix permissions due to UID change
		chown -R ${USER_NAME} "/var/log/apache2"
	fi
	chown -R "$USER_NAME" /usr/local/aspen-discovery
	exec /apache.sh

elif [ "$1" = 'cron' ]; then

	# Adjust permissions if required
	if [[ ! -z "${LOCAL_USER_ID}" && "${LOCAL_USER_ID}" != "33" ]]; then
		echo "%   Setting www-data to UID=${LOCAL_USER_ID}"
		usermod -o -u ${LOCAL_USER_ID} "$USER_NAME"
	fi
	chown -R ${USER_NAME} /usr/local/aspen-discovery
	exec /cron.sh

else
	exec "$@"
fi
