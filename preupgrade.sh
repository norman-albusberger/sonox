#!/bin/bash

# Argumente vom LoxBerry-System
ARGV1=$1 # Temp-Verzeichnisname z. B. install_abc123
ARGV2=$2 # Plugin-DB-Key
ARGV3=$3 # Plugin-Ordnername
ARGV4=$4 # Aktuelle Version
ARGV5=$5 # LoxBerry-Basisverzeichnis (/opt/loxberry)

echo "<INFO> SonoX: Starte preupgrade.sh"

# Temporäres Backup-Verzeichnis
TMPDIR="/tmp/${ARGV1}_upgrade"
mkdir -p "$TMPDIR"

echo "<INFO> Sichere settings.json..."
cp -p "$ARGV5/data/plugins/$ARGV3/settings.json" "$TMPDIR/settings.json" 2>/dev/null

echo "<INFO> Sichere tts-Verzeichnis..."
cp -p -r "$ARGV5/data/plugins/$ARGV3/tts" "$TMPDIR/tts" 2>/dev/null

echo "<INFO> Sichere device_id.txt..."
cp -p "$ARGV5/data/plugins/$ARGV3/device_id.txt" "$TMPDIR/device_id.txt" 2>/dev/null

echo "<INFO> Sicherung der .git-Directory für node-sonos-http-api"
if [ -d "$ARGV5/bin/plugins/$ARGV3/node-sonos-http-api/.git" ]; then
    mkdir -p "$TMPDIR/git"
    cp -rp "$ARGV5/bin/plugins/$ARGV3/node-sonos-http-api/.git" "$TMPDIR/git/"
fi

exit 0