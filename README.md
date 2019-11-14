# AirBeamLogger
This project is for a stand-alone data logger for the [AirBeam2](http://www.takingspace.org/aircasting/airbeam/) air quality monitor. The AirBeam2 is a relatively inexpensive device that can measure air quality, and while it does an excellent job when connected to the cloud to report data, it does have some limitations that this project seeks to address. The AirBeam2 can connect via Wi-Fi or cellular radios, but it does not support the WPA-Enterprise protected networks often found in schools and offices. It also cannot operate without a tethered smartphone or laptop in areas where there may not be a reliable signal, or any signal at all, such as subway tunnels, caves, and basements.

This [data logger](http://www.takingspace.org/raspberry-pi-airbeam-data-logger/) addresses those issues by plugging the AirBeam2 into a Raspberry Pi which will record the data from the AirBeam2 and store it internally. The Raspberry Pi will also broadcast an access point which can be connected to in order to retrieve the data and control the system. This gives the AirBeam2 greater flexibility in how and where it can be used.

## Update for firmware 3.7.19
As noted by [Hairem](https://github.com/ShVerni/AirBeamLogger/issues/4), an update to the AirBeam2 firmware (at least as of 3.7.19) has changed the format of the serial output the AirBeam2 produces. The [data_logger.py](https://github.com/ShVerni/AirBeamLogger/blob/master/data_logger.py) file has been updated to account for this, as well as some output formatting improvements. If you are using an earlier firmware, you can replace data_logger.py with [data_logger_old.py](https://github.com/ShVerni/AirBeamLogger/blob/master/data_logger_old.py) (renaming it to data_logger.py) to get the original functionality.

## Features
* Creates a Wi-Fi access point for easy data retrieval and control
* Easy to use web interface
* Plenty of storage for weeks or months of data (depends on SD card size)
* Can be easily solar or battery powered via USB battery pack or solar phone charger
### Measurement Modes
The data logger supports several measurement modes depending on what you need. These modes can be changed in the web interface.
#### Date and Time Stamp
If this is enabled, the data logger will synchronize  the Raspberry Pi's clock to your computer's clock whenever you click the `Start Data Logger` button in the web interface. It will then include the date and time stamp with every data point. If this mode is disabled, the data logger will include with each data point how many minutes have passed since starting the data logger. The time stamp mode is great for a couple of days, but the clock can tend to drift over time. To keep the clock accurate over longer peiods of time, see the next section.
#### NTP Time Sync
When enabled along with Date and Time Stamp mode, this mode will synchronize the Raspberry Pi's clock using NTP whenever the data logger is started and once every 24-hours thereafter. This requires that you've configured either a WPA or WPA-Enterprise network in the [Configure the Access Point](#configure-the-access-point) section. This will also mean that the data logger takes an additional minute or so to start up.

## Materials
For this project you will need the following items:
* A Raspberry Pi 3 or Zero W
* A USB OTG cable (if using a Zero)
* An SD card
* Power supply for the Raspberry Pi
* AirBeam2

There is a convenient [starter kit](https://www.amazon.com/CanaKit-Raspberry-Wireless-Complete-Starter/dp/B072N3X39J/) that provides everything you need for the Pi Zero W setup.
## Setting Up
These instructions will walk you through setting up the data logger. This guide assumes you're already familiar with the basics of the Linux command line and the Raspberry Pi. It is not recommended to do this setup by connecting to SSH via the Raspberry Pi's wireless connection, as this will cause problems when setting up the access point. Connecting via Ethernet or plugging in a keyboard and monitor works well.
### Configure the AirBeam2
You'll need to connect to your AirBeam2 via the AirCasting app and configure it for a mobile session (see the instructions in the AirBeam2 link above). You should be able to start a session and see the data on your smartphone if everything is wroking. This only needs to be done once, and afterwards you no longer need the AirCasting app. After doing this you should be able to turn on your AirBeam2 and see the indicator LED blinking red after a minute or two (the AirBeam2 should *not* be connected to the AirCasting app at this point); this means everything is working.
### Configure the Raspberry Pi
Set up your Raspberry Pi with Raspbian or Raspbian lite (you can refer to the [official documentation](https://www.raspberrypi.org/help/) for help). If you're setting up your Pi headlessly (without a monitor or keyboard) you can refer to [this guide](https://learn.sparkfun.com/tutorials/headless-raspberry-pi-setup) for help. The Wi-Fi with DHCP option works well for the Pi Zero W.

You will need an Internet connection for the start of this process so please ensure your Pi is connected via Ethernet or Wi-Fi to the Internet. SSH into your Pi and run the command `sudo raspi-config`. While you can confgiure all the options here, the important ones are:
* Change User Password
* Network Options > N3 Network interface names > No (important to ensure wlan0 is the Wi-Fi interface name)
* Boot Options > B1 Desktop / CLI > B2 Console Autologin
* Localisation Options (do each item in this submenu, especially setting the time zone which is important for timestamping)
* Advanced Options > A1 Expand Filesystem
* Advanced Options > A3 Memory Split (set to 16, optional)
* Finish (push tab key to get to this option)

Next run the following commands in order, answering yes or `y` when prompted:
```bash
sudo apt-get update
sudo apt-get dist-upgrade
sudo apt-get install nginx php-fpm hostapd isc-dhcp-server python3-serial
```
At this point your Pi is set up and no longer needs an Internet connection.
### Set Up Data Logger
To get all the necessary program files run the following commands:
```bash
wget https://github.com/ShVerni/AirBeamLogger/archive/master.zip
unzip master.zip
cd AirBeamLogger-master
cp settings.json ~/settings.json
cp shutdown.sh ~/shutdown.sh
cp sync_time.sh ~/sync_time.sh
cp data_logger.py ~/data_logger.py
cd ..
chmod 755 data_logger.py sync_time.sh shutdown.sh
chmod 766 settings.json
```
The base data logger files are now set up. You can even test it by running the command `sudo python3 data_logger.py` once you connect and turn on your AirBeam2. You should start seeing the data being output to the console after a minute or so. The default data sampling rate is every 5 seconds to help make this testing easier.
### Configure the Web Interface
To set up the Nginx webserver, first start Nginx with the command `sudo /etc/init.d/nginx start`. Next run `sudo nano /etc/nginx/sites-enabled/default`. Find the line that says:
```bash
index index.html index.htm;
```
and change it to
```bash
index index.php index.html index.htm;
```
Then find the lines that read
```bash
# pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
#
# location ~ \.php$ {
#         include snippets/fastcgi-php.conf;
#
#   	  # With php5-cgi alone:
#         fastcgi_pass 127.0.0.1:9000;
#         # With php5-fpm:
#         fastcgi_pass unix:/var/run/php/php5.0-fpm.sock;
# }

```
and chage them to read
```bash
# pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
#
 location ~ \.php$ {
         include snippets/fastcgi-php.conf;

#   	 # With php5-cgi alone:
#        fastcgi_pass 127.0.0.1:9000;
#        # With php7-fpm:
         fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
 }
```
Pay special attention to the last line which must be `php7.0-fpm.sock` not `php5.0-fpm.sock` which can be the default. Save and close the file. Reload Nginx with the command
```bash
sudo /etc/init.d/nginx reload
```
Next run the following commands to set up the web interface:
```bash
sudo rm /var/www/html/index.nginx-debian.html
sudo cp AirBeamLogger-master/html/* /var/www/html/
sudo chown root:root /var/www/html/*
sudo chmod 644 /var/www/html/*
```
Lastly, you need to give the www-data user sudo permission (__WARNING:__ This is generally not a good idea security wise, but since this device will almost never be accessible over a network aside from its own protected access point, this is okay. Do *not* do this if you plan to have your Raspberry Pi permanently connected to a network). To do this, run `sudo visudo` to edit the file and find the section that reads:
```bash
# User privilege specification
root    ALL=(ALL:ALL) ALL
```
and change it to read
```bash
# User privilege specification
root    ALL=(ALL:ALL) ALL
www-data    ALL=(ALL:ALL) NOPASSWD: ALL
```
Save and close the file.

You should now be able to connect to the webserver by opening up your browser and pointing it at the Raspberry Pi's IP address. Sometimes a reboot is required.
### Configure the Access Point
Setting up the access point is based on [this guide](https://learn.adafruit.com/setting-up-a-raspberry-pi-as-a-wifi-access-point/overview) with a lot of customization. Run to command `sudo nano /etc/dhcp/dhcpd.conf` and make the following changes to the file:
Find the lines that read
 ```bash
option domain-name "example.org";
option domain-name-servers ns1.example.org, ns2.example.org;
```
and change them to
```bash
#option domain-name "example.org";
#option domain-name-servers ns1.example.org, ns2.example.org;
```
Then find the lines that read
```bash
# If this DHCP server is the official DHCP server for the local
# network, the authoritative directive should be uncommented.
#authoritative;
```
and change them to
```bash
# If this DHCP server is the official DHCP server for the local
# network, the authoritative directive should be uncommented.
authoritative;
```
Then at the bottom of the file add:
```bash
subnet 192.168.3.0 netmask 255.255.255.0 {
    range 192.168.3.12 192.168.3.200;
    option broadcast-address 192.168.3.255;
    option routers 192.168.3.1;
    default-lease-time 600;
    max-lease-time 7200;
    option domain-name "local";
    option domain-name-servers 192.168.3.1, 8.8.8.8;
}
```
Save and close the file.

Run the command `sudo nano /etc/default/isc-dhcp-server` and edit the file so that the lines that read
```bash
INTERFACESv4=""
INTERFACESv6=""
```
instead read
```bash
INTERFACESv4="wlan0"
#INTERFACESv6=""
```
Save and close the file, then run the command `sudo rm /var/run/dhcpd.pid`.

Next run the commands
```bash
sudo cp AirBeamLogger-master/network/interfaces /etc/network/interfaces
sudo cp AirBeamLogger-master/network/interfaces.ap /etc/network/interfaces.ap
sudo chown root:root /etc/network/interfaces /etc/network/interfaces.ap
sudo chmod 644 /etc/network/interfaces /etc/network/interfaces.ap
```

What you do next depends on if you're planning to use the NTP functionality with a WPA-Enterprise network or a regular WPA2 network (if you're not planning to use NTP at all you can skip down to enabling the static IP).

For a WPA-Enterprise network run the following commands:
```bash
sudo cp AirBeamLogger-master/network/interfaces-enterprise.cl /etc/network/interfaces.cl
sudo chown root:root /etc/network/interfaces.cl
sudo chmod 644 /etc/network/interfaces.cl
sudo cp AirBeamLogger-master/wpa_supplicant/wpa_supplicant-enterprise.conf /etc/wpa_supplicant/wpa_supplicant.conf
sudo chown root:root /etc/wpa_supplicant/wpa_supplicant.conf
sudo chmod 644 /etc/wpa_supplicant/wpa_supplicant.conf
```
Run `sudo nano /etc/wpa_supplicant/wpa_supplicant.conf` and enter in the appropriate Wi-Fi network name and your user name and password for the network. Change the `country` parameter in the file if needed to match your country, then save and close the file.

For a WPA2 network run:
```bash
sudo cp AirBeamLogger-master/network/interfaces-wpa.cl /etc/network/interfaces.cl
sudo chown root:root /etc/network/interfaces.cl
sudo chmod 644 /etc/network/interfaces.cl
sudo cp AirBeamLogger-master/wpa_supplicant/wpa_supplicant-wpa.conf /etc/wpa_supplicant/wpa_supplicant.conf
sudo chown root:root /etc/wpa_supplicant/wpa_supplicant.conf
sudo chmod 644 /etc/wpa_supplicant/wpa_supplicant.conf
```
Run `sudo nano /etc/wpa_supplicant/wpa_supplicant.conf` and enter in the appropriate Wi-Fi network name and password. Change the `country` parameter in the file if needed to match your country, then save and close the file.

To enable the static IP address for the access point, run the command
```bash
sudo ifconfig wlan0 192.168.3.1
```

Next run `sudo nano /etc/hostapd/hostapd.conf` and edit the file to read:
```bash
interface=wlan0
ssid=AirBeam
country_code=US
hw_mode=g
channel=6
ignore_broadcast_ssid=0
macaddr_acl=0
wpa=2
wpa_passphrase=<passphrase>
wpa_key_mgmt=WPA-PSK
wpa_pairwise=CCMP
wpa_group_rekey=86400
ieee80211n=1
wme_enabled=1
```
Change `<passphrase>` to the Wi-Fi password you'd like to use. Save and close the file.

Run `sudo nano /etc/default/hostapd` and edit the file so that the line reading
```bash
#DAEMON_CONF=""
```
instead reads (be sure to remove the leading `#`)
```
DAEMON_CONF="/etc/hostapd/hostapd.conf"
```
Then run `sudo nano /etc/init.d/hostapd` and find the line that reads
```bash
DAEMON_CONF=
```
and change it to
```bash
DAEMON_CONF=/etc/hostapd/hostapd.conf
```
Lastly, run `sudo update-rc.d hostapd enable`. You should be able to reboot the Raspberry Pi and see and connect to the access point. You can access the web interface by typing `192.168.3.1` in your browser.

And you're done! Your data logger is now ready to grab some data. This can be easily modified to further process or upload the data somewhere else, or to work with other devices besides the AirBeam2 that have USB serial interfaces, or even those with SPI, UART, or I2C interfaces.

## Troubleshooting
If you're having issues with your AirBeam not sending data to your Raspberry Pi here are a few troubleshooting steps you can try to fix the problem:
* Make sure your AirBeam is fully charged before connecting to your Pi (this isn't strictly necessary, but if the battery is too low it may try to draw too much power for the Pi's USB port to handle).
* Fully boot your Raspberry Pi first and then turn on your AirBeam. Wait until the red LED is blinking on the AirBeam before starting the data logger; it could take over a minute for the AirBeam to be ready.
* Turn on your AirBeam first and make sure the red LED is blinking before turning on your Pi.
* The Raspberry Pi Zero case sometimes prevents the USB cable from being fully inserted into the Pi's USB port. Try removing the case and inserting the cable fully, or even try a different USB cable if possible.
* Connect to the AirBeam via the AirCasting app and start the mobile session again as described [here](https://github.com/ShVerni/AirBeamLogger/blob/master/README.md#configure-the-airbeam2). Thanks to [stopfortheklopp](https://github.com/ShVerni/AirBeamLogger/issues/3) for finding that.
* You can plug your AirBeam into a computer vis USB and connect to it with a serial monitor like PuTTY or the [Arduino](https://www.arduino.cc/en/Main/Software) app using a baud rate of 9600 kbps. You can then see any status messages or data the AirBeam is sending. Any error messages might help identify a problem, or if you see the data being sent like normal, it means your problem isn't the AirBeam at least.
