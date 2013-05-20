<?php
//Put API key from admin interface here
$apikey = 'aa625902eebedb7cf4fe100ada98996e';
$URL = 'http://localhost/api/';
//

###############################################
### DO NOT EDIT BELOW THIS LINE ###############
###############################################

define( 'LOCK_FILE', "/var/run/".basename( $argv[0], ".php" ).".lock" ); 
if( isLocked() ) die( "Already running.\n" ); 

function isLocked() 
{ 
    # If lock file exists, check if stale.  If exists and is not stale, return TRUE 
    # Else, create lock file and return FALSE. 

    if( file_exists( LOCK_FILE ) ) 
    { 
        # check if it's stale 
        $lockingPID = trim( file_get_contents( LOCK_FILE ) ); 

       # Get all active PIDs. 
        $pids = explode( "\n", trim( `ps -e | awk '{print $1}'` ) ); 

        # If PID is still active, return true 
        if( in_array( $lockingPID, $pids ) )  return true; 

        # Lock-file is stale, so kill it.  Then move on to re-creating it. 
        echo "Removing stale lock file.\n"; 
        unlink( LOCK_FILE ); 
    } 
    
    file_put_contents( LOCK_FILE, getmypid() . "\n" ); 
    return false; 

} 

$contents = file_get_contents($URL."?users=true&key=$apikey");


$users = json_decode($contents,TRUE);

if($users['service_should_run'] == 0) {
	file($URL."?service=false&key=$apikey");
	unlink( LOCK_FILE );
	sleep(10);
	exit("service should not run\n");
} else {
	file($URL."?service=true&key=$apikey");
	#unlink( LOCK_FILE );
	#exit("service restarting to run again");
}

$z = 1;
$sleep = 15;
$holding_array = array();

while(true) {
	foreach($users['userlist'] as $user) {
		
		$a = $users['users'][$user]['bluetooth'];
		$u = $user;
		$cmd = "l2ping -s 1 -c 2 -t 2 $a > /dev/null  ; echo $?";
		//echo $cmd."\n";
		$ret = exec($cmd);
		

		if($ret == 0) {
			if(!isset($holding_array[$u]) || $holding_array[$u] == 'out') {
				//Update the server
				$url = $URL.'?update=true&username='.$u.'&status=in&bt='.$a.'&key='.$apikey;
				//echo $url."\n";
				$return = file_get_contents($url);
				$holding_array[$u] = 'in';
			}
		} else {
			if(!isset($holding_array[$u]) || $holding_array[$u] == 'in') {
				//Update the server
				$url = $URL.'?update=true&username='.$u.'&status=out&bt='.$a.'&key='.$apikey;
				//echo $url."\n";	
				$return = file_get_contents($url);
				$holding_array[$u] = 'out';
			}
		}
		


		$retval = json_decode($return,true);


		if($retval['service_should_run'] == 0) {
			unlink( LOCK_FILE );
			exit("API told me to exit");
		}
	
	}
$z++;
sleep($sleep);

$config = file_get_contents($URL."?config=true&key=".$apikey);

$config = json_decode($config,true);


if($config['service_should_run'] == 0) {
	unlink( LOCK_FILE );
	exit("API told me to exit");
}

if($z == 100) {
 $holding_array = array();
 $z = 0;
}

}


unlink( LOCK_FILE ); 
exit(0);

?>
