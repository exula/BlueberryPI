<?php
$env = getenv('APPLICATION_ENVIROMENT');

if ( !$env )
{
	$env = "development";
}

if ( $env == "development" ) 
{
	error_reporting(-1);
	ini_set("display_errors",1);
	ini_set("display_startup_errors",1);
}
error_reporting(-1);
ini_set("display_errors",1);
ini_set("display_startup_errors",1);

$db_server = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_database = 'inoutboard';

?>