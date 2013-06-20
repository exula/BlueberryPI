#!/bin/bash
URL=$1
export DISPLAY=:0.0

sed -i 's/"exited_cleanly": false/"exited_cleanly": true/' ~/.config/chromium/Default/Preferences

chromium --kiosk $1
