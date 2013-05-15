<?php

$db_server = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_database = 'inoutboard';


######################################################################################
# DO NOT EDIT BELOW THIS #############################################################
######################################################################################


$conn = mysql_connect($db_server,$db_username,$db_password) or die(mysql_error());
mysql_select_db($db_database);

//Get all the config values from the database
$sql = "SELECT * from config";
$rs = mysql_query($sql);

//Create a config array. For items that have a single entry in the config table they are string values
//Multiple items of a config name turn into an array
while($myrow = mysql_fetch_array($rs)) {
	if(!isset($config[$myrow['name']])) {
		$config[$myrow['name']] = $myrow['value'];
	} else {

		if(!is_array($config[$myrow['name']])) {
			$firstvalue = $config[$myrow['name']];
			unset($config[$myrow['name']]);
			$config[$myrow['name']][] = $firstvalue;
			$config[$myrow['name']][] = $myrow['value'];
		} else {
			$config[$myrow['name']][] = $myrow['value'];		
		} 
	}
}
$original_config = $config;

//if the API key entry is empty we need to randomly genereate one!

if(empty($config['apikey'])) {

	$config['apikey'] = md5(time());

}

function save_config() {
	global $original_config;
	global $config;
	//We need to save the changes I've made the config array out to the database;

	//Find any values which need to be delete from the database
	$sql = "LOCK TABLES config";
	mysql_query($sql);
	foreach($original_config as $name=>$value) {

		if(!isset($config[$name])) {
			
			$sql = "DELETE from config WHERE name = '".mysql_real_escape_string($name)."'";
			mysql_query($sql);
		} else {
			if($original_config[$name] != $config[$name]) {
				
				if(is_array($config[$name])) {
						
					//Delete all exists config values and replace them with the new ones;

					$sql = "DELETE from config WHERE name = '".mysql_real_escape_string($name)."'";
					mysql_query($sql);
					foreach($config[$name] as $value) {
						if(is_array($value)) {
							$value = implode(",", $value);
						}
						$sql = "INSERT INTO config VALUES('','".mysql_real_escape_string($name)."','".mysql_real_escape_string($value)."')";
						mysql_query($sql);
					}


				} else {
					if(is_array($config[$name])) {
						$config[$name] = implode(",", $config[$name]);
					}
					$sql = "UPDATE config SET value = '".mysql_real_escape_string($config[$name])."' WHERE name = '$name'";

					mysql_query($sql);
				}


			}
		}

	}

	foreach($config as $name=>$value) {
		if(!isset($original_config[$name])) {
			
			
			
				
				if(is_array($config[$name])) {
						
					//Delete all exists config values and replace them with the new ones;

					$sql = "DELETE from config WHERE name = '".mysql_real_escape_string($name)."'";
					
					mysql_query($sql);
					foreach($config[$name] as $value) {
						$sql = "INSERT INTO config VALUES('','".mysql_real_escape_string($name)."','".mysql_real_escape_string($value)."')";
					
						mysql_query($sql);
					}


				} else {
					$sql = "INSERT into config VALUES('','".mysql_real_escape_string($name)."','".mysql_real_escape_string($config[$name])."')";
					
					mysql_query($sql);
				}


			
		}
	}
		$sql = "UNLOCK TABLES config";
	mysql_query($sql);

}




?>