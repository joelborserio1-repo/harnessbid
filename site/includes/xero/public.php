<?php
require 'lib/XeroOAuth.php';

/*if(!defined('MAIN_url')) {
	header("Location: http://dashboard.zulusys.nz/admin/index.php?Page=setting&Tab=xero");
	exit;
}*/

/**
 * Define for file includes
 */
define ( 'BASE_PATH', dirname(__FILE__) );

/**
 * Set a user agent string that matches your application name as set in the Xero developer centre
 */
$useragent = "ZuluCRM";

/**
 * Set your callback url or set 'oob' if none required
 * Make sure you've set the callback URL in the Xero Dashboard
 * Go to https://api.xero.com/Application/List and select your application
 * Under OAuth callback domain enter localhost or whatever domain you are using.
 */
define ( "OAUTH_CALLBACK", MAIN_url.'/admin/index.php?Page=setting&Tab=xero&Do=auth' );

/**
 * Application specific settings
 * Not all are required for given application types
 * consumer_key: required for all applications
 * consumer_secret: for partner applications, set to: s (cannot be blank)
 * rsa_private_key: application certificate private key - not needed for public applications
 * rsa_public_key: application certificate public cert - not needed for public applications
 */

include 'init.php';

$signatures = array (
		'consumer_key' => 'P9PGXQVAA1KMVNH34HKBPAQBBEEAOW',
		'shared_secret' => 'ABLPZZKRO4TBLRVVO5GF8WR5NAND0X',
		// API versions
		'core_version' => '2.0',
		'payroll_version' => '1.0',
		'file_version' => '1.0' 
);

if (XRO_APP_TYPE == "Private" || XRO_APP_TYPE == "Partner") {
	$signatures ['rsa_private_key'] = BASE_PATH . '/certs/privatekey.pem';
	$signatures ['rsa_public_key'] = BASE_PATH . '/certs/publickey.cer';
}
if (XRO_APP_TYPE == "Partner") {
	$signatures ['curl_ssl_cert'] = BASE_PATH . '/certs/entrust-cert-RQ3.pem';
	$signatures ['curl_ssl_password'] = '1234';
	$signatures ['curl_ssl_key'] = BASE_PATH . '/certs/entrust-private-RQ3.pem';
}

$XeroOAuth = new XeroOAuth ( array_merge ( array (
		'application_type' => XRO_APP_TYPE,
		'oauth_callback' => OAUTH_CALLBACK,
		'user_agent' => $useragent 
), $signatures ) );

$initialCheck = $XeroOAuth->diagnostics ();
$checkErrors = count ( $initialCheck );
if ($checkErrors > 0) {
	foreach ( $initialCheck as $check ) {
		//echo 'Error: ' . $check . PHP_EOL;
		// ## DISABLED
	}
} else {
	$here = XeroOAuth::php_self ();
}
