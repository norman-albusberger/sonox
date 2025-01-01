#!/bin/bash

# Wichtige Variablen
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
SERVICE_FILE="/etc/systemd/system/sonos-http-api.service"
SETTINGS_FILE="$LBPDATA/sonox/settings.json"

echo "<INFO> Starte die Einrichtung des Systemd-Dienstes für die Sonos-API..."

# Prüfen, ob das API-Verzeichnis existiert
if [ ! -d "$API_DIR" ]; then
    echo "<ERROR> API-Verzeichnis $API_DIR existiert nicht. Kann Systemd-Dienst nicht erstellen."
    exit 1
fi

# Systemd-Dienst einrichten
if [ ! -f "$SERVICE_FILE" ]; then
    echo "<INFO> Erstelle Systemd-Dienst für node-sonos-http-api..."
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
        echo "<OK> Systemd-Dienst erfolgreich eingerichtet und gestartet."
    else
        echo "<ERROR> Fehler beim Einrichten oder Starten des Systemd-Dienstes."
        exit 1
    fi
else
    echo "<WARNING> Systemd-Dienst existiert bereits. Überspringe diesen Schritt."
fi

echo "<OK> postinstall.sh erfolgreich abgeschlossen."
exit 0
