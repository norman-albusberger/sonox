#!/bin/bash

# Prüfen, ob Loxberry-Umgebungsvariablen gesetzt sind
if [ -z "$LBPBIN" ] || [ -z "$LBPDATA" ]; then
    echo "<ERROR> LBPBIN or LBPDATA not available. Please ensure LoxBerry environment variables are loaded."
    exit 1
fi

# Geräte-ID aus /etc/machine-id generieren (stabil und anonymisiert)
if [ -f /etc/machine-id ]; then
    DEVICE_ID=$(cat /etc/machine-id | sha256sum | cut -c1-16)
    echo "<INFO> Using stable device ID based on /etc/machine-id: $DEVICE_ID"
    echo "$DEVICE_ID" > "$LBPDATA/sonox/device_id.txt"


    FIREBASE_URL="https://sonox-db37a-default-rtdb.europe-west1.firebasedatabase.app/sonox-tracking/${DEVICE_ID}.json"
    TRACKING_DATA=$(cat <<EOF
{
  "event": "plugin_installed",
  "plugin_version": "1.1.7",
  "plugin_name": "SonoX",
  "timestamp": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
}
EOF
)

    curl -s -X PUT -H "Content-Type: application/json" -d "$TRACKING_DATA" "$FIREBASE_URL" >/dev/null && \
    echo "<INFO> tracked for $DEVICE_ID." || \
    echo "<INFO> not tracked for $DEVICE_ID."
else
    echo "<ERROR> /etc/machine-id not found. Cannot generate stable device ID."
fi

# Wichtige Variablen
API_DIR="$LBPBIN/sonox/node-sonos-http-api"
SERVICE_FILE="/etc/systemd/system/sonos-http-api.service"
SETTINGS_FILE="$LBPDATA/sonox/settings.json"

echo "<INFO> Starting the setup of systemd service for the API..."

# Muss zu den engines der API passen:
#   package.json           : node >=22 <25, npm >=10
#   vendor/sonos-discovery : node >=23 <25, npm >=10
#   .nvmrc                 : 24
# 24.x erfuellt beide, ist aktuelles LTS und identisch zur SonoX-Appliance.
NODE_VERSION="24.16.0"

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

# Akzeptierte System-Node-Versionen: 22..24 (siehe engines oben).
# 22 laeuft nachweislich, erzeugt aber eine EBADENGINE-Warnung von
# sonos-discovery (>=23) -> wird akzeptiert, aber mit Hinweis. Empfohlen: 24.
NODE_MIN_MAJOR=22
NODE_MAX_MAJOR=24

# Check for global node binary
GLOBAL_NODE=$(command -v node || true)
USE_GLOBAL_NODE=false

if [ -n "$GLOBAL_NODE" ]; then
    GLOBAL_VERSION_RAW=$("$GLOBAL_NODE" -v 2>/dev/null | sed 's/^v//')
    GLOBAL_MAJOR=${GLOBAL_VERSION_RAW%%.*}
    NPM_MAJOR=$(npm -v 2>/dev/null | cut -d. -f1)

    # Nicht-numerische Werte abfangen, sonst bricht [ -ge ] mit
    # "integer expression expected" ab.
    case "$GLOBAL_MAJOR" in ''|*[!0-9]*) GLOBAL_MAJOR=-1 ;; esac
    case "$NPM_MAJOR" in ''|*[!0-9]*) NPM_MAJOR=-1 ;; esac

    if [ "$GLOBAL_MAJOR" -ge "$NODE_MIN_MAJOR" ] && [ "$GLOBAL_MAJOR" -le "$NODE_MAX_MAJOR" ] && [ "$NPM_MAJOR" -ge 10 ]; then
        echo "<INFO> Found system Node.js v$GLOBAL_VERSION_RAW (npm $NPM_MAJOR) – accepted range ($NODE_MIN_MAJOR ≤ v ≤ $NODE_MAX_MAJOR)."
        if [ "$GLOBAL_MAJOR" -lt 23 ]; then
            echo "<WARNING> Node v$GLOBAL_VERSION_RAW is below the declared engine of sonos-discovery (>=23). It works, but v$NODE_VERSION is recommended."
        fi
        NODE_PATH="$GLOBAL_NODE"
        USE_GLOBAL_NODE=true
    else
        echo "<INFO> System Node.js v$GLOBAL_VERSION_RAW (npm $NPM_MAJOR) not suitable – requires node $NODE_MIN_MAJOR..$NODE_MAX_MAJOR with npm >= 10. Will use local v$NODE_VERSION."
    fi
else
    echo "<INFO> No system Node.js found. Will use local v$NODE_VERSION."
fi


# Fallback: lokale Node.js-Installation in $NODE_DIR
if [ "$USE_GLOBAL_NODE" = false ]; then
    NODE_PATH="$NODE_DIR/bin/node"
    LOCAL_VERSION_RAW=""
    if [ -x "$NODE_PATH" ]; then
        LOCAL_VERSION_RAW=$("$NODE_PATH" -v 2>/dev/null | sed 's/^v//')
    fi

    # Nicht nur "existiert?", sondern "richtige Version?" - sonst wuerde ein Bump
    # von NODE_VERSION bestehende Installationen nie aktualisieren.
    if [ "$LOCAL_VERSION_RAW" != "$NODE_VERSION" ]; then
        if [ -n "$LOCAL_VERSION_RAW" ]; then
            echo "<INFO> Local Node.js is v$LOCAL_VERSION_RAW, but v$NODE_VERSION is required. Reinstalling..."
        else
            echo "<INFO> Node.js v$NODE_VERSION not found locally. Downloading..."
        fi

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


 # Pull the latest changes from the API repo
if [ -d "$API_DIR/.git" ]; then
    echo "<INFO> Pulling latest changes from API Git repository..."
    cd "$API_DIR"
    git reset --hard HEAD
    git pull --rebase --autostash
    if [ $? -ne 0 ]; then
        echo "<WARNING> Git pull failed in $API_DIR"
    else
        echo "<OK> Git pull successful."
    fi
else
    echo "<WARNING> API directory is not a Git repository. Skipping git pull."
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

# Extrahiere den Port aus der settings.json
API_PORT=$(jq -r '.port // 5005' "$SETTINGS_FILE")

# Systemd-Dienst einrichten.
# Die Unit wird bei JEDEM Lauf neu geschrieben, nicht nur beim Erstinstall:
# NODE_PATH kann sich beim Upgrade aendern (z. B. System-Node wird jetzt
# abgelehnt -> lokales Node). Eine einmalig geschriebene Unit wuerde sonst
# weiterhin die alte Node-Binary starten und der Wechsel liefe ins Leere.
echo "<INFO> Writing systemd service for node-sonos-http-api..."
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
ExecStop=/bin/kill \$MAINPID
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
if ! systemctl enable sonos-http-api.service; then
    echo "<ERROR> Failed to enable sonos-http-api.service."
    exit 1
fi
echo "<OK> systemd service written. Node binary in use: $NODE_PATH"

echo "<INFO> Restarting sonos-http-api service..."
systemctl restart sonos-http-api.service
if [ $? -eq 0 ]; then
    echo "<OK> sonos-http-api.service restarted successfully."
else
    echo "<ERROR> Failed to restart sonos-http-api.service."
fi


echo "<INFO> Checking if API is reachable on port ${API_PORT}..."
for i in {1..7}; do
    curl -sf --max-time 2 http://127.0.0.1:${API_PORT}/zones > /dev/null && break
    echo "<INFO> Waiting for API to become available... ($i/7)"
    sleep 2
done

if curl -sf --max-time 2 http://127.0.0.1:${API_PORT}/zones > /dev/null; then
    echo "<OK> node-sonos-http-api is up and responding."
else
    echo "<WARNING> node-sonos-http-api is not responding. This might be due to port ${API_PORT} already being used."
    echo "<HINT> You can change the port in the plugin settings and save."
fi


exit 0
