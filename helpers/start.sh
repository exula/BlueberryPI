#!/bin/bash
/home/pi/bluetooth_proximity/run_proximity.sh &

logger "Running Start Script"

/home/pi/display/lightsOn.sh 60 &

/home/pi/display/display_web http://cias-rasppi1.rit.edu/ &
