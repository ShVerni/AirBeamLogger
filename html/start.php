<?php
// Set time.
exec("sudo date -u -s \"@" . $_POST["time"] . "\"");
// Make sure device is connected.
$port = exec("ls -ltr /dev|grep -i ttyACM0");
if ($port != "")
{
	// Kill any running data loggers.
	exec("sudo pkill python3");
	// Start the data logger.
	exec("sudo python3 /home/pi/data_logger.py > /dev/null &");
	echo("Started Data Logger");
}
else
{
	echo("No device connected");
}
?>
