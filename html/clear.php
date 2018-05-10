<?php
// Stop any running data loggers.
exec("sudo pkill python3");
// Delete all output files.
exec("sudo rm /home/pi/output.csv");
exec("sudo rm /var/www/html/output.csv");
echo("Data Cleared");
?>
