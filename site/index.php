<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Set MASTER Definitions
define('MASTER_section',"front");

//-- Data Loader
include("includes/loader.php");

//-- Redir to admin folder OVERRIDE
if(strstr($_SERVER['HTTP_HOST'],MAIN_host) || strstr(MAIN_host, DEFAULT_host)) {
	header("Location: ".MAIN_rel."admin/");
	exit;
} else {
	header("Location: ".FE_rel);
	exit;
}

//-- Theme Template Loader
include(TPL_root."index.php");
