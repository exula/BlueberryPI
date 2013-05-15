<?php
include('../includes/config.php');
?>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>	In/Out Board Admin</title>
	<script type="text/javascript" src='/jquery-1.9.1.min.js'></script>
	<script type="text/javascript" src='/jquery-ui.js'></script>
	<link rel="stylesheet" href="/jquery-ui.css" />
	<script>


	var apikey = '<?=$config['apikey']?>';
	var config;

	  $(function() {
	    $( "#accordion" ).accordion();
	  });

	  $(function() {
    $( "button:first" ).button({
      icons: {
        primary: "ui-icon-locked"
      },
      text: true
    })
  	});

	$(function() {
    	$service_should_run_button = $( "#service_should_run" ).button();
  		$service_should_run_button.on("click",function(event) {
  			
  			console.log(config);

  			if(config.service_should_run == "1") {
  				ssr='false';
  			} else {
  				ssr='true';
  			}

  			$.ajax("/api/?service_should_run="+ssr+"&key="+apikey).done(function(data) {
  				config = data;
  				if(data.service_should_run == 0) {
					service_status_html = "<span style='color: red'>Disabled</span>";
				} else {
					service_status_html = "<span style='color: green'>Enabled</span>";
				}
				$("#service_status")[0].innerHTML = service_status_html;

  			});
  		})
  	});

	function update_page() {
		$.ajax("/api/?config=true&key="+apikey).done(function(data) {
			console.log($("#service_status"));
			config = data;
			if(data.service_should_run == 0) {
				service_status_html = "<span style='color: red'>Disabled</span>";
			} else {
				service_status_html = "<span style='color: green'>Enabled</span>";
			}
			$("#service_status")[0].innerHTML = service_status_html;
			
			if(data.service_is_running == 'false') {
				service_running_html = "<span style='color: red'>Disabled</span>";
			} else {
				service_running_html = "<span style='color: green'>Enabled</span>";
			}
			$("#service_running")[0].innerHTML = service_running_html;
		});

		window.setTimeout("update_page()",5000);
	}



	$(function() {
		update_page();

	});


  </script>

</head>
<body>
	<div id="accordion">
		<h3>Manage Devices<h3>
			<div>
				Hi there
			</div>
		<h3>Manage Service</h3>
			<div>
				<input type='checkbox' id='service_should_run' /><label for='service_should_run'>Toogle Bluetooth Service</label>
				<br/>
				<strong>Service is <span id='service_status'>...</span></strong><br/>
				<strong>Current Service Status: <span id='service_running'></span></strong>

			</div>
		<h3>Manage RaspberryPI</h3>
			<div>
				hi
			</div>
	</div>


</body>
</html>