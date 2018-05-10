#!/usr/bin/env python
import time
import serial
import os
import shutil
import subprocess
import json
# Initialize variables.
output_path = '/home/pi/output.csv'
server_path = '/var/www/html/output.csv'
config_path = '/home/pi/settings.json'
ser = serial.Serial(
	port='/dev/ttyACM0',
	baudrate = 9600,
	parity=serial.PARITY_NONE,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.EIGHTBITS,
	timeout=1
)

# Grab and parse the settings.
settings = "";
with open(config_path, 'r') as settings_file:
	settings = json.load(settings_file);
if settings == "":
	print("Malformed settings file")
	exit(1)

counter = 0
delay = int(settings["delay"])
use_date = int(settings["date"])
use_ntp = int(settings["ntp"])
# Set initial date.
if (use_date == 1) and (use_ntp == 1):
	time.sleep(5)
	subprocess.call(['/home/pi/sync_time.sh'])
# Start measurement loop.
while 1:
	# Empty serial buffer.
	ser.flushInput()
	# Read data.
	x=ser.readline()
	# Parse data.
	data = x.decode('UTF-8').strip().split(' ')
	print(data)
	# Check for good data and output to file.
	if len(data) > 10:
		with open(output_path, 'a') as output:
			if use_date == 1:
				output.write(time.strftime("%Y/%m/%d %H:%M:%S" + "," + data[2] + "," + data[3] + "," + data[4] + "," + data[5] + "," + data[6] + "," + data[7] + "," + data[8] + "," + data[9] + "," + data[10] + "\r\n"))
			else:
				timepoint = (counter * delay) / 60
				output.write(str(timepoint) + "," + data[2] + "," + data[3] + "," + data[4] + "," + data[5] + "," + data[6] + "," + data[7] + "," + data[8] + "," + data[9] + "," + data[10] + "\r\n")
		# Copy output to server directory.
		shutil.copy(output_path, server_path)
	counter += 1
	# Syncy date and time via NTP every 24 hours if needed.
	if (use_date == 1) and (use_ntp == 1) and (counter * delay > 86400):
		counter = 0
		subprocess.call(['/home/pi/sync_time.sh'])
	time.sleep(delay);
