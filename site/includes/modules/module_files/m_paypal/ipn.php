<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a 1 license per-site module built into  //
//Zulu Shopfront. It is highly illegal to resell  //
//this module.			   						  //
//************************************************//

//MODULE TYPE: Payment
//MODULE NAME: ZULU Shopfront - PayPal IPN Process
//MODULE BUILD DATE: 1.6.12

//***IPN (Instant Payment Notification Post

session_start();

//INCLUDES
$loader_file = $_SERVER['DOCUMENT_ROOT']."/web/includes/loader.php";
if(!file_exists($loader_file)) {
    $loader_file = $_SERVER['DOCUMENT_ROOT']."/includes/loader.php";
}
include($loader_file);

$sale_id = $db->escape_string($_POST['custom']);
$sale_data = $class_sale->sale_data(['id'=>$sale_id,'ovr_user_id'=>true]);
$class_user->authorised->id = $sale_data['user_id'];
$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_data['id']));

if($sale_meta['module_payment'] > 0) {
	$module_payment_row = $class_module->module_data(['id'=>$sale_meta['module_payment']]);
	require_once $class_module->include_path($module_payment_row['id']);
	$module_payment = new $module_payment_row['class']();
	
	// validate payment
	$result = $module_payment->validate_payment();
}

header("HTTP/1.1 200 OK");
exit;

?>