<?php
include('../library/base.php');
?>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>	In/Out Board Admin</title>
	
	<script type="text/javascript" src='/js/jquery-1.9.1.min.js'></script>
	<script type="text/javascript" src='/js/jquery-ui.js'></script>
	<script type="text/javascript" src='/js/jquery.validate.min.js'></script>

	<link rel="stylesheet" href="/css/jquery-ui.css">
	
	<style>
	body {
		font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
		font-size: 12px;
	}

		#accordion { width: 700px}

		#current_devices .ui-selecting { background: #FECA40; }
	  	#current_devices .ui-selected { background: #F39814; color: white; }
	  	#current_devices { list-style-type: none; margin: 0; padding: 0; width: 60%; }
	  	#current_devices li { margin: 3px; padding: 2px; font-size: 1em; height: 40px;}
	  	#current_devices em { font-size: .8em;}

	  	input { width: 200px; }
	  	select { width: 200px;}
	  	label { width: 200px; float: left; }
		label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }
		p { clear: both; }
		.submit { margin-left: 12em; }
		em { font-weight: bold; padding-right: 1em; vertical-align: top; }
	</style>

	<script>


	var apikey = '<?=$config->values['apikey']?>';
	var config;

	  $(function() {
	    $( "#accordion" ).accordion({
	    	heightStyle: "content"
	    });
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

	function endAddDevice() {
		$('#addNewDeviceDiv').hide();

  		$('#addNewDeviceButton').show();
		$('#addNewDeviceSelect').innerHTML = '<option value="">Select a device</option>';
	}

  	function startAddDevice() {

  		$('#addNewDeviceDiv').show();
  		$('#addNewDeviceButton').hide();


  		$.ajax("/api/?bluetooth_scan=true&key="+apikey).done(function(data) {
  			for (var i = data.length - 1; i >= 0; i--) {
  				opt = document.createElement("option");
  				opt.innerText = data[i].devicename;
  				opt.value = data[i].address;
  				$('#addNewDeviceSelect').append(opt);
  			}
  		});

  	}

  	function removeDevice(data) {

  		$(function() {
		    $( "#dialog-confirm" ).dialog({
		      resizable: false,
		      height:200,
		      modal: true,
		      buttons: {
		        "Delete all items": function() {
		          $( this ).dialog( "close" );
		        },
		        Cancel: function() {
		          $( this ).dialog( "close" );
		        }
		      }
		    });
  	});

  	}

  	function addDevice() {

  		$('#addNewDeviceForm').submit();

  	}

	var added_users = new Array();
	function update_users(data) {

		for (var i = data.users.length - 1; i >= 0; i--) {

			if($.inArray(data.users[i].username,added_users) == -1) {

				user_li_id = data.users[i].name+"_li";
			
				user_li = document.createElement("LI");
				user_li.id =  user_li_id;

				user_li.innerHTML = "<img height='40' style='float: left' src='"+data.users[i].avatar+"'>"+data.users[i].name+"<br><em>Bluetooth: "+data.users[i].bluetooth+"</em>";
				user_li.innerHTML += "<a href='#' id='remove_"+data.users[i].username+"' onClick='removeDevice(this); return false;' style='float: right'>Remove Device</a>";
				user_li.className = 'ui-widget-content';

				$('#current_devices').append(user_li);
				added_users.push(data.users[i].username);
				
			} else {
				
			}
			
		};
		
		//$('#current_devices').selectable();
		
	}

	function update_page() {
		$.ajax("/api/?config=true&key="+apikey).done(function(data) {
			
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

			update_users(data);

		});

		window.setTimeout("update_page()",5000);
	}

	

	$(function() {
		update_page();



	});

	$(document).ready(function(){
    	$("#addNewDeviceForm").validate();
  	});

  </script>

</head>
<body>

	<h1>IN/OUT Board Admin Interface</h1>

	<div id="dialog-confirm" title="Remove this device?" style='display: none'>
  		<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
	</div>

	<div id="accordion">
		<h3>Manage Devices</h3>
			<div>
				<div>
					<ol id='current_devices'></ol>
				</div>
				<div id='addNewDeviceDiv' style='display: none'>
					<h3>Add a new Device</h3>
					<fieldset>
						<form id='addNewDeviceForm'>
							<label>Name attached to the device</label><br/>
							<input type='text' id='addDevice_name' name='addDevice_name' class='required'><br/><br/>
							<label>Username</label><br/>
							<input type='text' id='addDevice_username' name='addDevice_username' class='required'><br/><br/>
							<label>Avatar</label><br/>
							<input type='text' id='addDevice_avatar' name='addDevice_avatar' class='required'><br/><br/>
	 						<select id='addNewDeviceSelect' name='addNewDeviceSelect' class='required'>
								<option value='' >Select Device...</option>
							</select><br/><br/>
							<button onClick='addDevice(); return false;'>Add User to the Board</button>
						</form>
					</fieldset>
					<br/>

					<a href='#' onClick='endAddDevice();'>Cancel</a>
				</div>
				<div id='addNewDeviceButton'>
					<a href='#' onClick='startAddDevice()'>Add New Device</a>
				</div>
			</div>

		<h3>Manage Service</h3>
			<div>
				<input type='checkbox' id='service_should_run' /><label for='service_should_run'>Toogle Bluetooth Service</label>
				<br/><br/>
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