<?php
error_reporting(-1);
ini_set("display_errors",1);
ini_set("display_startup_errors",1);

$db_server = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_database = 'inoutboard';



######################################################################################
# DO NOT EDIT BELOW THIS #############################################################
######################################################################################

$options = array(
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

try {

    $dbh = new PDO("mysql:host=$db_server;dbname=$db_database", $db_username, $db_password,$options);
    
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

//$conn = mysql_connect($db_server,$db_username,$db_password) or die(mysql_error());
//mysql_select_db($db_database);

//Get all the config values from the database
$sql = "SELECT * from config";

//Create a config array. For items that have a single entry in the config table they are string values
//Multiple items of a config name turn into an array


foreach( $dbh->query($sql) as $myrow ) {

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
	global $dbh;
	//We need to save the changes I've made the config array out to the database;

	//Find any values which need to be delete from the database
	$sql = "LOCK TABLES config WRITE";
	
	$dbh->query($sql);
	foreach($original_config as $name=>$value) {

		if(!isset($config[$name])) {
			
			$sql = "DELETE from config WHERE name = ':name'";

			$statement = $dbh->prepare($sql);
			$statement->bindParam(':name',filter_var($name,INPUT_GET));
			$statement->execute();
			
		} else {
			if($original_config[$name] != $config[$name]) {
				
				if(is_array($config[$name])) {
						
					//Delete all exists config values and replace them with the new ones;

					$sql = "DELETE from config WHERE name = ':name'";

					$statement = $dbh->prepare($sql);
					$statement->bindParam(":name",filter_var($name,FILTER_SANITIZE_STRING));
					$statement->execute();
					

					foreach($config[$name] as $value) {
						if(is_array($value)) {
							$value = implode(",", $value);
						}
						$sql = "INSERT INTO config VALUES('',':name',':value')";
						
						$statement = $dbh->prepare($sql);
						$statement->bindParam(":name",filter_var($name,FILTER_SANITIZE_STRING));
						$statement->bindParam(":value",filter_var($value,FILTER_SANITIZE_STRING));
						$statement->execute();
					}


				} else {
					if(is_array($config[$name])) {
						$config[$name] = implode(",", $config[$name]);
					}
					$sql = "UPDATE config SET value = ':newname' WHERE name = ':name'";
					$statement =$dbh->prepare($sql);
					$statement->bindParam(":newname",$config[$name]);
					$statement->bindParam(":name",$name);
					$statement->execute();

				}


			}
		}

	}

	foreach($config as $name=>$value) {
		if(!isset($original_config[$name])) {
			if(is_array($config[$name])) {
					
				//Delete all exists config values and replace them with the new ones;

				$sql = "DELETE from config WHERE name = ':name'";
				$statement = $dbh->prepare($sql);
				$statement->bindParam(":name",filter_var($name,FILTER_SANITIZE_STRING));
				$statement->execute();
				
				foreach($config[$name] as $value) {
					$sql = "INSERT INTO config VALUES('',:name,:value)";
				
					$statement = $dbh->prepare($sql);
					$statement->bindParam(':name',filter_var($name,FILTER_SANITIZE_STRING));
					$statement->bindParam(':value',filter_var($value,FILTER_SANITIZE_STRING));
					$statement->execute();	
				}
			} else {
				$sql = "INSERT into config VALUES('',:name,:configvalue)";
				
				$statement = $dbh->prepare($sql);
				$statement->bindParam(":name",filter_var($name,FILTER_SANITIZE_STRING));
				$statement->bindParam(":configvalue",$config[$name]);
				$statement->execute();
			}
	}
	}
	
	$sql = "UNLOCK TABLES";
	$dbh->query($sql);

}
?>