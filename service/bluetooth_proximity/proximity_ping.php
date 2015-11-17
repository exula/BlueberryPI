<?php
//Put API key from admin interface here
$apikey = 'e6f08cd2a2d7c4521328bdd193fd479a';
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
$sleep = 3;
$holding_array = array();

while(true) {

	$contents = file_get_contents($URL."?users=true&key=$apikey");
	$users = json_decode($contents,TRUE);

	if(!empty($users['userlist'])) {
		foreach($users['userlist'] as $user) {
			
			$a = str_replace('-',':',$users['users'][$user]['bluetooth']);
			$u = $user;
			$cmd = "sudo hcitool name $a ";
			//$cmd = "sudo l2ping -s 1 -c 4 -t 3 $a > /dev/null  ; echo $?";
			//echo $cmd."\n";
			$ret = exec($cmd);
			echo $ret;	
			if(!empty($ret)) {
			//if($ret == 0) {
				if(!isset($holding_array[$u]) || $holding_array[$u] == 'out') {
					//Update the server
					$url = $URL.'?update=true&username='.$u.'&status=in&bt='.$a.'&key='.$apikey;
					//echo $url."\n";
					$return = file_get_contents($url);
					$holding_array[$u] = 'in';
					echo $u." in\n";
				}
			} else {
				if(!isset($holding_array[$u]) || $holding_array[$u] == 'in') {
					//Update the server
					$url = $URL.'?update=true&username='.$u.'&status=out&bt='.$a.'&key='.$apikey;
					//echo $url."\n";	
					$return = file_get_contents($url);
					$holding_array[$u] = 'out';
					echo $u." out\n";
				}
			}
			


			$retval = json_decode($return,true);

			
			if(isset($reval) && $retval['service_should_run'] == 0) {
				unlink( LOCK_FILE );
				
				exit("API told me to exit\n");
			}
		
		}
	}
	//Every 2 runs we will run the HCITOOL Scan
	if($z%5 == 0) {
		
	    exec('/usr/bin/hcitool scan | /usr/bin/tail -n +2',$devices,$returnval);

	    print_r($devices);

	    $newdevices = array();
        foreach($devices as $key=>$value) {

                $device = preg_split('/\t/',trim($value));
                $newdevices[] = array("address"=>$device[0],"name"=>$device[1]);

        }
        

       $url = $URL."?hcitool=true&devices=".urlencode(json_encode($newdevices))."&key=".$apikey;
       echo "Updating HCITOOL Scan\n";
       $return = file_get_contents($url);

       unset($devices);
       unset($device);
       unset($newdevices);


	}

	
	echo $z."\n";
	$z++;
	sleep($sleep);

	$config = file_get_contents($URL."?config=true&key=".$apikey);

	$config = json_decode($config,true);

	if($config['service_should_run'] == 0) {
		unlink( LOCK_FILE );
		exit("API told me to exit\n");
	}

	if($z == 100) {
	 //$holding_array = array();
	 $z = 0;
	}

}


unlink( LOCK_FILE ); 
exit(0);

?>
