<?php
/**
 * $_GET based API Class to manage all the admin and frontend work
*/
namespace Application;

class API
{
    protected $config; 		//Config object from Application
    protected $dbh;     	//PDO object from Application

    public function __construct(\PDO $_dbh, $_config)
    {
        $this->dbh = $_dbh;
        $this->config = $_config;
    }

    /**
     * Return the results of a bluetooth scan of the system
     * @return bool always returns true
     */
    public function bluetooth_scan()
    {
        $devices = array();
        
        if(!empty($this->config->values['hcitoolscan'])) {   
            foreach ($this->config->values['hcitoolscan'] as $device) {

                $line_split = preg_split('/,/',$device);
                if($device[2] < time() ) {
                    $devices[] = array("address"=>str_replace(':', '-', $line_split[0]),"devicename"=>$line_split[1]);
                }
            }
        }

        echo json_encode($devices);

        return true;
    }

    /**
     * HCITOOL Scan results from the service
     * @return bool true
     */
    function hcitoolScan() {

        $devices = json_decode($_GET['devices'],TRUE);

        foreach($devices as $key=>$value) {
            $devices[$key]['expires'] = time()+240;
        }

        $this->config->values['hcitoolscan'] = $devices;
      

        $this->config->save_config();

    }

    /**
     * Update config with frontend options
     * @return bool true
     */
    function frontEndConfig() {
    

        $heading = filter_var($_GET['heading'],FILTER_SANITIZE_STRING);
        $subheading = filter_var($_GET['subheading'],FILTER_SANITIZE_STRING);
        $sidebar = $_POST['sidebar'];
       
        $this->config->values['heading'] = $heading;
        $this->config->values['subheading'] = $subheading;
        $this->config->values['sidebar'] = $sidebar;

        $this->config->save_config();

        $this->getConfig();

    }


    /**
     * Ask the database weather the service should be running, this is a simple config values
     * @return bool true
     */
    public function serviceShouldRun()
    {
        if ($_GET['service_should_run'] == 'true') {

            $this->config->values['service_should_run'] = 1;
        } elseif ($_GET['service_should_run'] == 'false') {
            $this->config->values['service_should_run'] = 0;
        }

        $this->config->save_config();
        echo json_encode($config->values);

        return true;
    }

    /**
     * Return the configuration of the system for the web pages
     * @return bool true
     */
    public function getConfig()
    {
        if (isset($this->config->values['users'])) {


            foreach ($this->config->values['users'] as $user=>$value) {

                $this->config->values['users'][$user] = preg_split('/,/',$value);

                $this->config->values['users'][$user]['username'] = $this->config->values['users'][$user][0];
                unset($this->config->values['users'][$user][0]);

                $this->config->values['users'][$user]['name'] = $this->config->values['users'][$user][1];
                unset($this->config->values['users'][$user][1]);

                $this->config->values['users'][$user]['bluetooth'] = $this->config->values['users'][$user][2];
                unset($this->config->values['users'][$user][2]);

                $this->config->values['users'][$user]['avatar'] = $this->config->values['users'][$user][3];
                unset($this->config->values['users'][$user][3]);
            }
        }

        unset($this->config->values['apikey']);
        echo json_encode($this->config->values);

        return true;
    }

    /**
     * Ask the system if the service is running, this values is set by the services that run on the RaspberryPI's
     * @return bool true
     */
    public function service()
    {
        $this->config->values['service_is_running'] = $_GET['service'];

        $this->save_config();

        return true;
    }

    /**
     * Remove device by Bluetooth Address from database
     */
    function removeDevice() {
        $bluetoothAddress = filter_var($_GET['remove_device'],FILTER_SANITIZE_STRING);

        //Search the array from the bluetooth address
  
        foreach ($this->config->values['users'] as $key => $value) {
            if(stristr($value, $bluetoothAddress)) {
                unset($this->config->values['users'][$key]);
            }
        }

        $this->config->save_config();
     
        $this->getConfig();  
    }

    function addDevice() {
       
        $name = filter_var($_GET['name'],FILTER_SANITIZE_STRING);
        $username = filter_var($_GET['username'],FILTER_SANITIZE_STRING);
        $avatar = filter_var($_GET['avatar'],FILTER_SANITIZE_STRING);
        $bluetoothAddress = filter_var($_GET['address'],FILTER_SANITIZE_STRING);

        $this->config->values['users'][] = $username.",".$name.",".$bluetoothAddress.",".$avatar;
        $this->config->save_config();

        $this->getConfig();

    }

    /**
     * This allows the system to update the status of a user's device from the service that runs.
     * @return bool true
     */
    public function updateDeviceStatus()
    {
        //Update status of a bluetooth address

        $sql = "INSERT into bluetooth VALUES('',:username,:bt,'".time()."',:status,'master')";

        $username = filter_var($_GET['username'],FILTER_SANITIZE_STRING);
        $bt = filter_var($_GET['bt'],FILTER_SANITIZE_STRING);
        $status = filter_var($_GET['status'],FILTER_SANITIZE_STRING);

        $statement = $this->dbh->prepare($sql);

        $statement->bindParam(":username",$username);
        $statement->bindParam(":bt",$bt);
        $statement->bindParam(":status",$status);

        $statement->execute();

        echo json_encode(array("service_should_run"=>$this->config->values['service_should_run']));

        return true;
    }

    /**
     * Return all the devices
     * @return bool true
     */
    public function getUserDevices()
    {
        if (!is_array($this->config->values['users'])) {
            $this->config->values['users'] = array($this->config->values['users']);
        }
    
        if(!empty($this->config->values['users'])) {
            foreach ($this->config->values['users'] as $u) {

                $matches = preg_split('/,/',$u);

                $username = $matches[0];

                $sql = "SELECT * from bluetooth WHERE username = :username ORDER by id DESC LIMIT 1";

                $statement = $this->dbh->prepare($sql);
                $statement->bindParam(":username",$username);
                $statement->execute();

                $myrow = $statement->fetch();

                if (empty($myrow)) {
                    $myrow = array();
                    $myrow['status'] = 'out';
                    $myrow['timestamp'] = time();
                }

                $returnarr['users'][$username]['username'] = $username;
                $returnarr['users'][$username]['status'] = $myrow['status'];
                $returnarr['users'][$username]['lastseen'] = date("c",$myrow['timestamp']);
                $returnarr['users'][$username]['bluetooth'] = $matches[2];
                $returnarr['users'][$username]['name'] = $matches[1];
                $returnarr['users'][$username]['avatar'] = $matches[3];

            }
        
        } else {
            
            $returnarr = array("error"=>"No Devices are setup!");
            $returnarr['users'] = array();
        }
        
         $returnarr['userlist'] = array_keys($returnarr['users']);
         $returnarr['service_should_run'] = $this->config->values['service_should_run'];

         $returnarr['heading'] = $this->config->values['heading'];
         $returnarr['subheading'] = $this->config->values['subheading'];
         $returnarr['sidebar'] = $this->config->values['sidebar'];

        echo json_encode($returnarr);
        $this->config->save_config();

        return true;

    }
}
