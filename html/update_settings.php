<?php
// Open settings file.
$settings_file = fopen("/home/pi/settings.json", "w") or die ("Unable to open settings file");
// Update with new settings.
$text = "{\n \"date\": " . $_POST["date"] . ",\n \"delay\": " . $_POST["delay"] . ",\n \"ntp\": " . $_POST["ntp"] . "\n}";
fwrite($settings_file, $text);
fclose($settings_file);
// Check which setting was modified.
switch ($_POST["type"]) {
        case 0:
                echo("Sampling interval set to " . $_POST["delay"] / 60 . " minutes");
                break;
        case 1:
                if ($_POST["date"] == 1)
                {
                        echo("Enabled date and time stamp");
                }
                else
                {
                        echo("Disabled date and time stamp");
                }
                break;
        case 2:
                 if ($_POST["ntp"] == 1)
                {
                        echo("Enabled NTP time sync.");
                }
                else
                {
                        echo("Disabled NTP time sync.");
                }
                break;
        default:
                echo("Invalid setting.");
}
?>

