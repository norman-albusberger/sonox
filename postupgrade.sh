#!/bin/bash

ARGV0=$0    # Shell command itself
ARGV1=$1    # Temp folder during install
ARGV2=$2    # Plugin name (e.g., sonox-pro)
ARGV3=$3    # Plugin installation folder (same as plugin name)
ARGV4=$4    # Plugin version
ARGV5=$5    # Base folder of LoxBerry

# Load bashlib for iniparser if needed
. $ARGV5/libs/bashlib/loxberry_log.sh

# Logging start
echo "<INFO> Starting postupgrade script for $ARGV2"

# Restore settings.json
if [ -f /tmp/${ARGV1}_upgrade/settings.json ]; then
  echo "<INFO> Restoring settings.json"
  cp -p -v /tmp/${ARGV1}_upgrade/settings.json $ARGV5/data/plugins/$ARGV3/settings.json
else
  echo "<WARNING> No settings.json backup found. Skipping restore."
fi

# Restore tts directory
if [ -d /tmp/${ARGV1}_upgrade/tts ]; then
  echo "<INFO> Restoring tts directory"
  cp -p -r /tmp/${ARGV1}_upgrade/tts $ARGV5/data/plugins/$ARGV3/
else
  echo "<WARNING> No presets backup found. Skipping restore."
fi

# Restore .git directory if it was backed up (for git pull functionality)
if [ -d /tmp/${ARGV1}_upgrade/git/.git ]; then
  echo "<INFO> Restoring .git directory"
  cp -rp /tmp/${ARGV1}_upgrade/git/.git $ARGV5/bin/plugins/$ARGV3/node-sonos-http-api/
else
  echo "<INFO> No .git backup found. Skipping restore."
fi

# Remove temporary upgrade folder
echo "<INFO> Cleaning up temporary upgrade directory"
rm -rf /tmp/${ARGV1}_upgrade

# Optional: restart systemd service
echo "<INFO> Attempting to restart sonos-http-api.service"
if command -v sudo >/dev/null 2>&1; then
  sudo systemctl restart sonos-http-api.service
  if [ $? -eq 0 ]; then
    echo "<OK> sonos-http-api.service restarted successfully."
  else
    echo "<ERROR> Failed to restart sonos-http-api.service."
  fi
else
  echo "<WARNING> sudo not available. Skipping service restart."
fi

exit 0