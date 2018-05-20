<?php
	// Load JSON settings from file.
	$settings = json_decode(file_get_contents("/home/pi/settings.json"), true);
?>
<!DOCTYPE html>
<html>
<head>
<title>Data Server</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
<script src="jquery.min.js"></script>
<script type = "text/javascript">
// Create AJAX functions to control the data logger.
function start() {
$.ajax( { type : 'POST',
          data : { time: Math.round($.now() / 1000) },
          url  : 'start.php',
          success: function ( data ) {
            alert( data );
          },
          error: function ( xhr ) {
            alert( "error" );
          }
        });
}
function stop() {
$.ajax( { type : 'POST',
          data : { },
          url  : 'stop.php',
          success: function ( data ) {
            alert( data );
          },
          error: function ( xhr ) {
            alert( "error" );
          }
        });
}
function clear_data() {
$.ajax( { type : 'POST',
          data : { },
          url  : 'clear.php',
          success: function ( data ) {
            alert( data );
          },
          error: function ( xhr ) {
            alert( "error" );
          }
        });
}
function start_shutdown() {
$.ajax( { type : 'POST',
          data : { },
          url  : 'shutdown.php',
          success: function ( data ) {
            alert( data );
          },
          error: function ( xhr ) {
            alert( "error" );
          }
        });
}
function update_settings(setting) {
    if (Math.floor($("#delay").val()) > 0 && Math.floor($("#delay").val()) <= 1440)
	{
        var use_date = 0;
        if ($("#date").prop('checked')) {
                use_date = 1;
        }
        var use_ntp = 0;
        if ($("#ntp").prop('checked')) {
                use_ntp = 1;
        }

        $.ajax( { type : 'POST',
                  data : {
                                type: setting,
                                ntp: use_ntp,
                                delay: Math.floor($("#delay").val()) * 60,
                                date: use_date
                        },
                  url  : 'update_settings.php',
                  success: function ( data ) {
                    alert( data );
                  },
                  error: function ( xhr ) {
                    alert( "error" );
                  }
         });
    }
}
$(function() {
	// Disable form submission, it's all handled via JavaScript.
	$("#settings").submit(function(e) {
		e.preventDefault();
		return false;
	});
});
</script>
</head>
<body>
<h1>Welcome to the Data Server</h1>
<p><a style="font-size: x-large; font-weight: bold;"  href="output.csv">Data File</a></p>
<p><button onclick="start()">Start Data Logger</button></p>
<p><button onclick="stop()">Stop Data Logger</button></p>
<p><button onclick="clear_data()">Clear Data</button></p>
<p><button onclick="start_shutdown()">Shutdown</button></p>
<form id="settings">
	<p>
		<input id="delay" type="number" max="1440" min="1" value="<?php echo($settings["delay"] / 60) ?>">
		<button onclick="update_settings(0)">Set Sample Rate (min)</button>
	</p>
	<p>
		<input id="date" type="checkbox" value="date" <?php if ( $settings["date"] == 1 ) echo("checked") ?> onclick="update_settings(1)">
		Enable date and time stamp.
	</p>
	<p>
                <input id="ntp" type="checkbox" value="ntp" <?php if ( $settings["ntp"] == 1 ) echo("checked") ?> onclick="update_settings(2)">
                Enable NTP time sync.
        </p>

</form>
</body>
</html>
