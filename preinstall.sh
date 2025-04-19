#!/bin/bash

# Important variables (provided by LoxBerry)
API_DIR="$LBPBIN/sonox-pro/node-sonos-http-api"
REQUIRED_NODE_VERSION="14.0.0"

echo "<INFO> Starting preparation for the installation of the Sonox plugin..."

# Check if LBPBIN is set
if [ -z "$LBPBIN" ]; then
    echo "<ERROR> LBPBIN variable is not set. Please ensure LoxBerry environment variables are loaded."
    exit 1
fi

# Check dependencies
if ! command -v git >/dev/null 2>&1; then
    echo "<ERROR> git is not installed. Please install git to proceed."
    exit 1
fi

# Check if the API is already installed
if [ -d "$API_DIR" ]; then
    echo "<INFO> node-sonos-http-api is already installed. Skipping git clone."
else
    echo "<INFO> Installing node-sonos-http-api..."
    git clone https://github.com/norman-albusberger/node-sonos-http-api.git "$API_DIR"
    if [ $? -ne 0 ]; then
        echo "<ERROR> Failed to clone the repository."
        rm -rf "$API_DIR"
        exit 1
    fi
    if ! cd "$API_DIR"; then
        echo "<ERROR> Failed to change directory to $API_DIR."
        exit 1
    fi
    echo "<OK> node-sonos-http-api successfully installed."
fi

echo "<INFO> Installation completed successfully."
exit 0
