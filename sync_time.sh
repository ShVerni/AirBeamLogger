#!/bin/bash
# Take down network interface and stop AP relevant services
sudo ifconfig wlan0 0.0.0.0
sudo ifdown wlan0
sudo service hostapd stop
sudo service isc-dhcp-server stop
# Swap interface configureation
sudo cp /etc/network/interfaces.cl /etc/network/interfaces
# Bring interface up and get IP address
sudo ifup wlan0
sleep 5
sudo service dhcpcd start
sleep 5
# Set time via NTP
sudo timedatectl set-ntp True
sudo timedatectl set-ntp False
# Stop DHVP service
sudo service dhcpcd stop
# Assign static address bring interface down
sudo ifconfig wlan0 192.168.3.1
sudo ifdown wlan0
# Swap interface confguration back
sudo cp /etc/network/interfaces.ap /etc/network/interfaces
# Bring interface back up and start AP relevant services
sudo ifup wlan0
sleep 3
sudo service hostapd start
sudo service isc-dhcp-server start
exit 0
