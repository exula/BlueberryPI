<?php
/**
 * $_GET based API Class to manage all the admin and frontend work
*/
namespace Application;

class API
{
    protected $config; 		//Confif object from Application
    protected $dbh;	//PDO object from Application

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

        foreach ($matches as $line) {

            $line_split = preg_split('/\t/',$line);

            $devices[] = array("address"=>$line_split[1],"devicename"=>$line_split[2]);

        }

        echo json_encode($devices);

        return true;
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

         $returnarr['userlist'] = array_keys($returnarr['users']);
         $returnarr['service_should_run'] = $this->config->values['service_should_run'];

        echo json_encode($returnarr);
        $this->config->save_config();

        return true;

    }
}
