#!/bin/bash

killall -9 lightsOn.sh
killall -9 unclutter
killall -9 midori

./lightsOn.sh > /dev/null 2>&1 &
unclutter > /dev/null 2>&1 &

export DISPLAY=:0.0
midori -e Fullscreen -a http://localhost/ > /dev/null 2>&1