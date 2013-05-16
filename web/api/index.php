<?php
include('../includes/config.php');
header("Content-Type: text/json");

if(!isset($_GET['key']) || $_GET['key'] != $config['apikey']) {

	die('API KEY IS NOT SET OR NOT CORRECT');
} else {

}




if(isset($_GET['bluetooth_scan'])) {

	//Sample output
	$output = 'Scanning ...
	00:26:4A:9E:3F:C8	cias-cms13
	00:1F:F3:B0:F9:68	cias-it06
	00:1E:52:EF:18:15	cias-cms04
	00:26:4A:9B:FF:BC	Bradley’s Mac Pro
	38:0A:94:B1:31:6E	Galaxy Nexus
	';

	$cmd = 'hcitool scan';

	$matches = preg_split('/\n/', $output);

	unset($matches[0]);
	unset($matches[count($matches)]);

	foreach($matches as $line) {

			$line_split = preg_split('/\t/',$line);

			$devices[] = array("address"=>$line_split[1],"devicename"=>$line_split[2]);
	
	}

	echo json_encode($devices); 
	exit();
}

if(isset($_GET['service_should_run'])) {

	if($_GET['service_should_run'] == 'true') {
		
		$config['service_should_run'] = 1;
	} elseif($_GET['service_should_run'] == 'false') {
		$config['service_should_run'] = 0;
	}

	
	save_config();
	echo json_encode($config);
	exit();
}

if(isset($_GET['config'])) {

	if(isset($config['users'])) {
		foreach($config['users'] as $user=>$value) {

			$config['users'][$user] = preg_split('/,/',$value);
			$config['users'][$user]['username'] = $config['users'][$user][0]; unset($config['users'][$user][0]);
			$config['users'][$user]['name'] = $config['users'][$user][1]; unset($config['users'][$user][1]);
			$config['users'][$user]['bluetooth'] = $config['users'][$user][2]; unset($config['users'][$user][2]);
			$config['users'][$user]['avatar'] = $config['users'][$user][3]; unset($config['users'][$user][3]);
		}
	}

	unset($config['apikey']);
	echo json_encode($config);
	exit();
}

if(isset($_GET['service'])) {
	$config['service_is_running'] = $_GET['service'];


	save_config();
	exit();
}

if(isset($_GET['update'])) {
	//Update status of a bluetooth address
	
	$sql = "INSERT into bluetooth VALUES('','".mysql_real_escape_string($_GET['username'])."','".mysql_real_escape_string($_GET['bt'])."','".time()."','".mysql_real_escape_string($_GET['status'])."')";

	mysql_query($sql) or die(mysql_error());
	
	echo json_encode(array("service_should_run"=>$config['service_should_run']));
	exit();
}

if(isset($_GET['users'])) {

	//Get information about all bluetooth addresses


	if(!is_array($config['users'])) {
		$config['users'] = array($config['users']);
	}

	foreach($config['users'] as $u) {

		$matches = preg_split('/,/',$u);

		$u = $matches[0];
		
		

		$sql = "SELECT * from bluetooth WHERE username = '".$u."' ORDER by id DESC LIMIT 1";
		
		$results = mysql_query($sql);

		$myrow = mysql_fetch_array($results);
		
		if(empty($myrow)) {
			$myrow = array();
			$myrow['status'] = 'out';
			$myrow['timestamp'] = time();
		}

		$returnarr[$u]['username'] = $u;
		$returnarr[$u]['status'] = $myrow['status'];
		$returnarr[$u]['lastseen'] = date("c",$myrow['timestamp']);
		$returnarr[$u]['bluetooth'] = $matches[2];
		$returnarr[$u]['name'] = $matches[1];
		$returnarr[$u]['avatar'] = $matches[3];

 	}

 		$returnarr['userlist'] = array_keys($returnarr);
 		$returnarr['service_should_run'] = $config['service_should_run'];

	echo json_encode($returnarr);
	save_config();
	exit();

}

save_config();
?>
