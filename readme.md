SonoX - The Loxberry Plugin [![PayPal donate button](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.com/paypalme/normanalbusberger "Donate to SonoX using Paypal") 
-----------------------------
***Seamlessly integrate and control your Sonos speakers with Loxone.***

<p align="center">
  <img src="https://raw.githubusercontent.com/norman-albusberger/sonox/refs/heads/main/icons/icon_256.png" alt="Sonox Icon" width="256">
</p>


SonoX integrates Sonos (especially the S1 version.) speakers with your Loxone Smart Home system, leveraging the [node-sonos-http-api](https://github.com/jishi/node-sonos-http-api) for seamless control and automation.
Since other plugins are way too complicated and unstable I decided to create my own one. 

# Features
- **Playback Control**: Play, pause, skip, and adjust volume.
- **Group Management**: Synchronized playback across rooms.
- **Smart Home Integration**: Automate Sonos actions with Loxone events.
- **Web Interface**: Test and copy URLs for Loxone outputs.
- **Text-to-Speech (TTS)**: Announcements and notifications.
- **Clip Playback**: Audio clips for alarms or doorbell notifications.

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

