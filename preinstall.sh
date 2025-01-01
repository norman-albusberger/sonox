#!/bin/bash

# Wichtige Variablen (werden von LoxBerry bereitgestellt)
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
REQUIRED_NODE_VERSION="14.0.0"

echo "<INFO> Starte die Vorbereitung für die Installation des Sonox-Plugins..."

# Node.js-Version prüfen
if command -v node >/dev/null 2>&1; then
    INSTALLED_NODE_VERSION=$(node -v | sed 's/v//')
    if [ "$(printf '%s\n' "$REQUIRED_NODE_VERSION" "$INSTALLED_NODE_VERSION" | sort -V | head -n1)" != "$REQUIRED_NODE_VERSION" ]; then
        echo "<ERROR> Node.js-Version $REQUIRED_NODE_VERSION oder höher erforderlich. Installiert ist $INSTALLED_NODE_VERSION."
        exit 1
    else
        echo "<OK> Node.js-Version $INSTALLED_NODE_VERSION ist kompatibel."
    fi
else
    echo "<ERROR> Node.js ist nicht installiert. Bitte installieren Sie Node.js Version $REQUIRED_NODE_VERSION oder höher."
    exit 1
fi

# Prüfen, ob die API bereits installiert ist
if [ -d "$API_DIR" ]; then
    echo "<INFO> node-sonos-http-api ist bereits installiert. Überspringe die Installation."
else
    echo "<INFO> Installiere node-sonos-http-api..."
    git clone https://github.com/norman-albusberger/node-sonos-http-api.git "$API_DIR"
    if [ $? -ne 0 ]; then
        echo "<ERROR> Fehler beim Klonen des Repositories."
        exit 1
    fi
    cd "$API_DIR" || exit 1
    npm install
    if [ $? -ne 0 ]; then
        echo "<ERROR> Fehler bei der Installation der npm-Abhängigkeiten."
        exit 1
    fi
    echo "<OK> node-sonos-http-api erfolgreich installiert."
fi

exit 0
