#!/bin/bash

# Important variables (provided by LoxBerry)
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
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

if ! command -v npm >/dev/null 2>&1; then
    echo "<ERROR> npm is not installed. Please install npm to proceed."
    exit 1
fi

# Check Node.js version
if command -v node >/dev/null 2>&1; then
    INSTALLED_NODE_VERSION=$(node -v | sed 's/v//')
    if [ "$(printf '%s\n' "$REQUIRED_NODE_VERSION" "$INSTALLED_NODE_VERSION" | sort -V | head -n1)" != "$REQUIRED_NODE_VERSION" ]; then
        echo "<ERROR> Node.js version $REQUIRED_NODE_VERSION or higher is required. Installed version is $INSTALLED_NODE_VERSION."
        exit 1
    else
        echo "<OK> Node.js version $INSTALLED_NODE_VERSION is compatible."
    fi
else
    echo "<ERROR> Node.js is not installed. Please install Node.js version $REQUIRED_NODE_VERSION or higher."
    exit 1
fi

# Check if the API is already installed
if [ -d "$API_DIR" ]; then
    echo "<INFO> node-sonos-http-api is already installed. Skipping installation."
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
    npm install
    if [ $? -ne 0 ]; then
        echo "<ERROR> Failed to install npm dependencies."
        exit 1
    fi
    echo "<OK> node-sonos-http-api successfully installed."
fi

echo "<INFO> Installation completed successfully."
exit 0
