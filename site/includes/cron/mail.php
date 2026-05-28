<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();

//INCLUDES
include("../loader.php");

//-- Action: Read 
if($_GET['Action']=='Mark'&&trim($_GET['Serial'])!=NULL) {
	$serial = $db->escape_string($_GET['Serial']);
	$zulu->mail_log_read($serial);	
}