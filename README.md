BlueberryPI
==============

A bluetooth prxoximity detection script along with a TV kiosk display of who is in and out!

This system of scripts works nicely on a single Pi, but it has the potential for the admin and API side of things to live on a web server somewhere
and for the detection scripts to live on the Pi's. The Pi's could then just display the web page from the web server. For ease of use i've bundled everything together for now.


Testing
==============
I've included a vagrant file and a bootstrap script which will get a development enviroment up and running for you.
In the future I will make the development enviroment fake Bluetooth better so you can 'scan' and 'ping' devices.

    vagrant up

 will start your development enviroment and you can get to it at the frontend at http://localhost:8080/ and the admin at http://localhost:8080/admin 


Installation
=============

I am making a few assumptions in there instructions.

1. You have a PI with a base install of Raspbian wheezy
2. You have a keyboard or SSH or someother way of getting to the Raspberry Pi
3. You have a bluetooth dongle** installed via USB to the Raspberry Pi

** I have noticed that some dongles will work without Pairing your devices, or dongles will work without Pairing, as of right now this version of BlueberryPi only works nicely with dongles that don't require a pairing.

With these assumptions the following the installation is fairly easy.
 
First install the required packages (This will take a long time.. go get some coffee!)

    sudo apt-get -y install php5 php5-mysql php5-cli libapache2-mod-php5 mysql-server apache2 unclutter bluez xscreensaver


If you want always keep the software up-to-date lets go ahead and deploy this using git

	cd ~
	git clone https://github.com/exula/BlueberryPI.git
	cd BlueberryPI
	sudo rm -rf /var/www
	sudo ln -s web /var/www
	
Next we need to setup our MySQL database

    cd helpers
    mysql -u root -p < bluetoothtable.sql

At this point you have a blank version of the system, but the API keys need to fixed in some scripts.
We need to find the API key
    http://<address of your pi>/admin/

copy and paste the 'Current API Key';

That needs to updated in the following files
    service/bluetooth_proximity/proximity_ping.php

Verify that the API key is correct in

   web/js/blueberrypi.config.js
 

At this point there are two scrips that need to run.

The first script is the backend service script. This runs the scanning for new devices and pings existing devices to see if they are in range.
The second script is used to set up the frontend display for a Kiosk.

1. service/bluetooth_proximity/run_proximity.sh
2. service/start_display.sh

From the 'service' directory run the following. On the Wheezy install this will run these scripts once X11
	sudo cp dmrc /home/pi/.dmrc
	sudo cp autostart home/pi/.config/openbox/autostart


At this point you should be able to restart your RaspberyPI and have a fullscreen Midori window with the app.
You will have to go to the admin page and add devices.




