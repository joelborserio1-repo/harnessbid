<?php

define ( 'BASE_PATH', dirname(__FILE__) );
define ( "XRO_APP_TYPE", "Private" );
define ( "OAUTH_CALLBACK", MAIN_url.'/admin/index.php?Page=setting&Tab=xero&Do=auth');
$useragent = "XeroOAuth-PHP Private App Test";

require BASE_PATH.'/lib/XeroOAuth.php';

$signatures = array (
		'consumer_key' => $class_setting->data['xero_shared_key'],
		'shared_secret' => $class_setting->data['xero_consumer_key'],
		// API versions
		'core_version' => '2.0',
		'payroll_version' => '1.0',
		'file_version' => '1.0' 
);

if (XRO_APP_TYPE == "Private" || XRO_APP_TYPE == "Partner") {
	$signatures ['rsa_private_key'] = BASE_PATH . '/certs/' .$class_setting->data['user_id']. '/privatekey.pem';
	$signatures ['rsa_public_key'] = BASE_PATH . '/certs/' .$class_setting->data['user_id']. '/publickey.cer';
}

$XeroOAuth = new XeroOAuth ( array_merge ( array (
		'application_type' => XRO_APP_TYPE,
		'oauth_callback' => OAUTH_CALLBACK,
		'user_agent' => $useragent 
), $signatures ) );
include 'tests/testRunner.php';

$initialCheck = $XeroOAuth->diagnostics ();
$checkErrors = count ( $initialCheck );
if ($checkErrors > 0) {
	foreach ( $initialCheck as $check ) {
		//echo 'Error: ' . $check . PHP_EOL;
		// ## DISABLED
	}
} else {
	$session = persistSession ( array (
			'oauth_token' => $XeroOAuth->config ['consumer_key'],
			'oauth_token_secret' => $XeroOAuth->config ['shared_secret'],
			'oauth_session_handle' => '' 
	) );
	$oauthSession = retrieveSession ();
	
	if (isset ( $oauthSession ['oauth_token'] )) {
		$XeroOAuth->config ['access_token'] = $oauthSession ['oauth_token'];
		$XeroOAuth->config ['access_token_secret'] = $oauthSession ['oauth_token_secret'];
		
		//include 'tests/tests.php';
	}
	
//	testLinks ();
}
