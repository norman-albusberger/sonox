#!/bin/bash

# Wichtige Variablen
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
SERVICE_FILE="/etc/systemd/system/sonos-http-api.service"
SETTINGS_FILE="$LBPDATA/sonox/settings.json"

echo "<INFO> Starting the setup of systemd service for the API..."

# Prüfen, ob das API-Verzeichnis existiert
if [ ! -d "$API_DIR" ]; then
    echo "<ERROR> API dir does not exist $API_DIR. Cannot create service. Please try reinstall."
    exit 1
fi

# Systemd-Dienst einrichten
if [ ! -f "$SERVICE_FILE" ]; then
    echo "<INFO> Creating systemd service for node-sonos-http-api..."
    cat <<EOF > "$SERVICE_FILE"
[Unit]
Description=Sonos HTTP API
After=network.target

[Service]
Environment="SETTINGS_PATH=$SETTINGS_FILE"
ExecStart=/usr/bin/node $API_DIR/server.js
WorkingDirectory=$API_DIR
Restart=always
User=loxberry
Group=loxberry

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

echo "<OK> postinstall.sh finished successfully."
exit 0
