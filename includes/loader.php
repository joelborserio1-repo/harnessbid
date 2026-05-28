<?php

@session_start();

//-- Front End Define
define('CRM_fe',true);
define('ZSF_include_root',dirname(__FILE__)."/");
define('ZSF_include_rel',"../");

//-- Set USER ID ## OVERRIDE
$_SESSION['site']['user'] = 5;

//-- Include LOADER
include(ZSF_include_root."../site/includes/loader.php");

if(defined('AUTHORISE') && AUTHORISE>0) {
	if(!$class_client->frontend_auth()) {
		$zulu->notification_set("Please sign in to access this page.",2);
		header("Location: ".$zulu->front_link(FE_rel."members/login.php", ['query'=>['return'=>$_SERVER['REQUEST_URI']]]));
		exit;
	}
	if((!isset($_SESSION['user']['_meta']['web_verify_phone']) || !$_SESSION['user']['_meta']['web_verify_phone']) && (!defined('AUTHORISE_no_verify') || !AUTHORISE_no_verify)) {
		header("Location: ".$zulu->front_link(LINK_account_verify));
		exit;
	}
}

//-- Include Front End
include(ZSF_include_root."connect.php");

?>
