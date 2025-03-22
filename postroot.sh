#!/bin/bash

# Prüfen, ob Loxberry-Umgebungsvariablen gesetzt sind
if [ -z "$LBPBIN" ] || [ -z "$LBPDATA" ]; then
    echo "<ERROR> LBPBIN or LBPDATA not available. Please ensure LoxBerry environment variables are loaded."
    exit 1
fi

# Wichtige Variablen
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
SERVICE_FILE="/etc/systemd/system/sonos-http-api.service"
SETTINGS_FILE="$LBPDATA/sonox/settings.json"

echo "<INFO> Starting the setup of systemd service for the API..."

NODE_VERSION="22.2.0"

# Architektur erkennen und Node.js-Build-Typ festlegen
ARCH=$(uname -m)
case "$ARCH" in
    x86_64)
        NODE_DISTRO="linux-x64"
        ;;
    aarch64)
        NODE_DISTRO="linux-arm64"
        ;;
    armv7l)
        NODE_DISTRO="linux-armv7l"
        ;;
    *)
        echo "<ERROR> Unsupported architecture: $ARCH"
        exit 1
        ;;
esac

NODE_DIR="$API_DIR/node"
NODE_PATH=""

# Check for global node binary
GLOBAL_NODE=$(command -v node)
USE_GLOBAL_NODE=false

if [ -n "$GLOBAL_NODE" ]; then
    GLOBAL_VERSION_RAW=$($GLOBAL_NODE -v | sed 's/^v//')
    GLOBAL_MAJOR=$(echo "$GLOBAL_VERSION_RAW" | cut -d. -f1)

    if [ "$GLOBAL_MAJOR" -ge 14 ] && [ "$GLOBAL_MAJOR" -lt 22 ]; then
        echo "<INFO> Found system Node.js v$GLOBAL_VERSION_RAW – accepted range (14 ≤ v < 22)."
        NODE_PATH="$GLOBAL_NODE"
        USE_GLOBAL_NODE=true
    else
        echo "<INFO> Found system Node.js v$GLOBAL_VERSION_RAW – not suitable. Will use local v$NODE_VERSION."
    fi
else
    echo "<INFO> No system Node.js found. Will use local v$NODE_VERSION."
fi


# Fallback: use local Node.js v22
if [ "$USE_GLOBAL_NODE" = false ]; then
    NODE_PATH="$NODE_DIR/bin/node"
    if [ ! -x "$NODE_PATH" ]; then
        echo "<INFO> Node.js v$NODE_VERSION not found locally. Downloading..."

        # Bereinige ggf. defekte oder unvollständige lokale Node.js-Installation
        if [ -d "$NODE_DIR" ]; then
            echo "<INFO> Removing existing local Node.js directory before reinstallation..."
            rm -rf "$NODE_DIR"
        fi
        mkdir -p "$NODE_DIR"

        echo "<INFO> Downloading Node.js from: https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-$NODE_DISTRO.tar.xz"
        curl -fSL "https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-$NODE_DISTRO.tar.xz" -o /tmp/node.tar.xz
        if [ $? -ne 0 ]; then
            echo "<ERROR> Download failed. Check your internet connection or URL."
            exit 1
        fi
        tar -xJf /tmp/node.tar.xz --strip-components=1 -C "$NODE_DIR"
        if [ ! -x "$NODE_PATH" ]; then
            echo "<ERROR> Failed to install Node.js v$NODE_VERSION locally."
            exit 1
        fi
        echo "<OK> Node.js v$NODE_VERSION installed locally at $NODE_PATH"
    else
        echo "<INFO> Node.js v$NODE_VERSION already available locally."
    fi
fi


# Prüfen, ob das API-Verzeichnis existiert
if [ ! -d "$API_DIR" ]; then
    echo "<ERROR> API dir does not exist $API_DIR. Cannot create service. Please try reinstall."
    exit 1
fi

# Installiere npm-Abhängigkeiten, wenn package.json vorhanden
if [ -f "$API_DIR/package.json" ]; then
    echo "<INFO> Installing npm dependencies for node-sonos-http-api..."
    cd "$API_DIR"
    if [ "$USE_GLOBAL_NODE" = true ]; then
        npm install --silent
    else
        PATH="$NODE_DIR/bin:$PATH" $NODE_DIR/bin/npm install --silent
    fi
    if [ $? -ne 0 ]; then
        echo "<ERROR> Failed to install npm dependencies."
        exit 1
    fi
    echo "<OK> npm dependencies successfully installed."
else
    echo "<WARNING> No package.json found in $API_DIR – skipping npm install."
fi

# Systemd-Dienst einrichten
if [ ! -f "$SERVICE_FILE" ]; then
    echo "<INFO> Creating systemd service for node-sonos-http-api..."
    cat <<EOF > "$SERVICE_FILE"
[Unit]
Description=Sonos HTTP API
After=network.target

[Service]
Type=simple
Environment="SETTINGS_PATH=$SETTINGS_FILE"
Environment="PATH=/usr/local/bin:/usr/bin:/bin"
ExecStartPre=/usr/bin/test -f $SETTINGS_FILE
ExecStart=$NODE_PATH $API_DIR/server.js
ExecStop=/bin/kill $MAINPID
WorkingDirectory=$API_DIR
Restart=always
RestartSec=5
User=loxberry
Group=loxberry
StandardOutput=journal
StandardError=journal
LimitNOFILE=8192


[Install]
WantedBy=multi-user.target
EOF
    systemctl daemon-reload
    systemctl enable sonos-http-api.service
    systemctl start sonos-http-api.service
    if [ $? -eq 0 ]; then
        echo "<OK> systemd service created successfully. sonos-http-api.service up and running."
    else
        echo "<ERROR> Error during creation and starting of the sonos-http-api.service."
        exit 1
    fi
else
    echo "<WARNING> systemd service sonos-http-api.service already exists."
fi

echo "<INFO> Checking if API is reachable on port 5005..."
curl -s --max-time 2 http://localhost:5005/zones > /dev/null

if [ $? -eq 0 ]; then
    echo "<OK> node-sonos-http-api is up and responding."
else
    echo "<WARNING> node-sonos-http-api is not responding. This might be due to port 5005 already being used."
    echo "<HINT> You can change the port in the plugin settings and save."
fi
exit 0
