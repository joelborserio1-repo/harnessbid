<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();
define('API_access',true);

//INCLUDES
include("../loader.php");
//error_reporting(E_ALL);ini_set('display_errors', 1);

//-- Load settings
$class_setting->default_load();

//-- Do Process
$renew_list = $class_renew->renew_data(['auto_renew'=>true,'status'=>1,'user_id'=>'skip']);
$log_output = [];
foreach($renew_list as $renew) {

	$class_user->authorised->id = $renew['user_id'];
	$class_renew->get($renew['id']);

	$log_output[] = "LOAD Subscription ID ".$renew['id']." for Client ID ".$renew['client_id'];

	$output = $class_renew->process_row(['soft_fail'=>true]);
	if(count($output['log'])>0) {
		foreach($output['log'] as $log_item) {
			$log_output[] = $log_item;
		}
	}
}

print_r($log_output);
echo "EOF";
exit;

?>
