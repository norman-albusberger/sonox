SonoX - A Loxberry Plugin for Sonos Speakers [![PayPal donate button](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.com/paypalme/normanalbusberger "Donate to SonoX using Paypal") 
-------
You can download the latest release from the [Loxberry Page ](https://wiki.loxberry.de/plugins/sonox/start).


***Seamlessly integrate and control your Sonos speakers with Loxone.***

<p align="center">
  <img src="https://raw.githubusercontent.com/norman-albusberger/sonox/refs/heads/main/icons/icon_256.png" alt="Sonox Icon" width="256">
</p>



SonoX integrates Sonos (especially the S1 version.) speakers with your Loxone Smart Home system for seamless control and automation. The plugin provides a web interface for testing commands and copying URLs for Loxone outputs. You can control playback, adjust volume, and manage groups across rooms. SonoX also supports text-to-speech (TTS) and audio clips for announcements and notifications. The plugin publishes Sonos events to MQTT topics for further integration with your smart home system.

# Features at a Glance
- **Playback Control**: Play, pause, skip, and adjust volume.
- **Group Management**: Synchronized playback across rooms.
- **Smart Home Integration**: Automate Sonos actions with Loxone events.
- **Web Interface**: Test and copy URLs for Loxone outputs.
- **Text-to-Speech (TTS)**: Announcements and notifications.
- **Clip Playback**: Audio clips for alarms or doorbell notifications.
- **MQTT Support**: Publishes Sonos events to MQTT topics.

# Requirements
1. **Loxberry**: Installed and running with NodeJS.
2. **Sonos System**: At least one speaker connected to your network.

# Installation
1. **Install Plugin**: Upload the Sonox plugin via Loxberry’s web interface and complete the setup wizard.
2. **Configure**: Add API server details in plugin settings and test the connection.

# Usage Examples
1. **Control Playback**
    - Use HTTP endpoints for room-specific commands:
        - **Play**: `http://<your-loxberry-ip:5005>/{room}/play`
        - **Pause**: `http://<your-loxberry-ip:5005>/{room}/pause`
        - **Volume**: `http://<your-loxberry-ip:5005>/{room}/volume/{value}`
2. **TTS & Clips**
    - TTS: `http://<your-loxberry-ip:5005>/{room}/say/{phrase}/{language}/{announceVolume}`
    - Clip: `http://<your-loxberry-ip:5005>/sayall/{phrase}/{language}/{announceVolume}`
3. **Web Interface**
    - Test commands and copy URLs for integration.
    - Settings for changing the port and other.

# License
Distributed under the MIT License. See the LICENSE file for details.

