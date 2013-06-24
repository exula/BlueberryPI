<?php
/**
*   API controller for the application
*
*   @category API
*   @package  BlueberryPI
*   @author   Bradley Coudriet <brad@exula.net>
*   @license  http://opensource.org/licenses/gpl-license.php GNU General Public License, Version 3
*   @link     http://bjcpgd.cias.rit.edu
*/
require_once '../library/base.php';
header("Content-Type: text/json");

if ( ! isset($_GET['key']) || $_GET['key'] != $config->values['apikey'] ) {
    echo json_encode(array("error"=>"API Key is incorrect"));
    return false;
} else {

}

//Map a GET action type a function in the API class
$arrayMap = array(
    "bluetooth_scan"=>"bluetooth_scan",
    "service_should_run"=>"serviceShouldRun",
    "config"=>"getConfig",
    "service"=>"service",
    "update"=>"updateDeviceStatus",
    "users"=>"getUserDevices",
    "remove_device"=>"removeDevice",
    "add_device"=>"addDevice",
    "frontEndConfig"=>"frontEndConfig",
    "hcitool"=>"hcitoolScan"
);


/**
* Use the array_interesect and find the $_GET variable which IS present in the apprayMap, and then use the
* map to call the appriorate function in the API class
* @var array
*/
$diff = array_intersect_key($arrayMap, $_GET);
if (count($diff) == 1) {
    $api = new Application\API($dbh, $config);
    $apicall = array_pop($diff);
    $api->$apicall($config);
}
?>