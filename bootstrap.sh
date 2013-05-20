#!/usr/bin/env bash

apt-get update

echo "mysql-server-5.5 mysql-server/root_password_again password root"| debconf-set-selections
echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections
apt-get install -y php5 php5-mysql mysql-server apache2 
rm -rf /var/www
ln -fs /vagrant/web/ /var/www

#Config the Databse
mysql -u root -proot < /vagrant/helpers/bluetoothtable.sql
