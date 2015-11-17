#!/bin/bash

killall -9 lightsOn.sh
killall -9 unclutter
killall -9 midori

export DISPLAY=:0.0

/opt/BlueberryPI/service/lightsOn.sh > /dev/null 2>&1 &
unclutter > /dev/null 2>&1 &

midori -e Fullscreen -a http://localhost/ > /dev/null 2>&1 &

export DISPLAY=:0.1

midori -e Fullscreen -a http://work.cias.rit.edu/ciastech/widgets/unifiedschedule > /dev/null 2>&1 &
