#!/usr/bin/env python
import time
import serial
import os
import shutil
import subprocess
import json
import os.path

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
	data = [];
	retries = 10;
	while len(data) != 24 and retries > 0:
		# Empty serial buffer.
		ser.flushInput()
		# Read data.
		x=ser.readline()
		# Parse data.
		data = x.decode('UTF-8').strip().split(' ')
		print(data)
		# Short delay to retry reading data if necessary.
		time.sleep(0.1)
		retries -= 1;
	# Check for good data and output to file.
	if len(data) == 24:
		# Check if file exists or it's a new file.
		newfile = not os.path.isfile(output_path)
		with open(output_path, 'a') as output:
			if use_date == 1:
				if (newfile):
					# Write first line of new file
					output.write("Time, Temperature (C), %RH, PM1, PM2.5, PM10\r\n")
				output.write(time.strftime("%Y/%m/%d %H:%M:%S") + "," + data[15] + "," + data[17] + "," + data[19] + "," + data[21] + "," + data[23] + "\r\n")
			else:
				if (newfile):
					# Write first line of new file
					output.write("Timepoint, Temperature (C), %RH, PM1, PM2.5, PM10\r\n")
				timepoint = (counter * delay) / 60
				output.write(str(timepoint) + "," + data[15] + "," + data[17] + "," + data[19] + "," + data[21] + "," + data[23] + "\r\n")
		# Copy output to server directory.
		shutil.copy(output_path, server_path)
	counter += 1
	# Sync date and time via NTP every 24 hours if needed.
	if (use_date == 1) and (use_ntp == 1) and (counter * delay > 86400):
		counter = 0
		subprocess.call(['/home/pi/sync_time.sh'])
	time.sleep(delay);
