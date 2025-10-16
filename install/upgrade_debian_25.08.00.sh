#!/bin/bash

# Installs and rebuilds GD with RAQM support for enhanced placeholder cover image support.

# Only run GD/RAQM rebuild on Debian/Ubuntu systems.
if command -v apt-get &> /dev/null; then
    echo "Rebuilding GD with RAQM support for enhanced placeholder cover images..."

    # Remove duplicate PHP repository file if it exists.
    if [[ -f /etc/apt/sources.list.d/php.list ]]; then
        echo "Removing duplicate PHP repository configuration..."
        rm -f /etc/apt/sources.list.d/php.list
    fi

    # Clean and update package lists.
    echo "Updating package lists..."
    apt-get clean
    if ! apt-get update -qq; then
        echo "Warning: Some repository updates failed, but continuing with GD rebuild..."
    fi

    # Get the directory where this script is located.
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

    # Run the rebuild script if it exists.
    if [[ -f "$SCRIPT_DIR/rebuild_gd_raqm.sh" ]]; then
        bash "$SCRIPT_DIR/rebuild_gd_raqm.sh" --php-version 8.4 --gd-version gd-2.3.3
        echo "GD with RAQM support has been successfully rebuilt for Aspen Discovery 25.08.00."
    else
        echo "Error: rebuild_gd_raqm.sh script not found in $SCRIPT_DIR."
        exit 1
    fi
else
    echo "Error: Could not find apt-get. Cannot determine package manager."
    exit 1
fi
