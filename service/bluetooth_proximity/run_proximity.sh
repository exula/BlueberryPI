#!/bin/bash
cd /home/pi/BlueberryPI/service/bluetooth_proximity/
/etc/init.d/bluetooth restart
while [ true ]
do

sudo php proximity_ping.php #>> /home/pi/ping.log

done

