<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Start Session
session_start();

define('ROOT_include',dirname(__FILE__).'/');
include(ROOT_include."config.php");

if(!defined('MASTER_section')) {
    define('MASTER_section', 'front');
}

if(defined('DISPLAY_errors') && DISPLAY_errors) {
    error_reporting(E_ALL);
	ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR);
	ini_set('display_errors', 0);
}

require(ROOT_include."../../vendor/autoload.php");
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule = new Capsule;
$capsule->addConnection([
   "driver" 	=> "mysql",
   "host" 		=> DB_host,
   "database" 	=> DB_name,
   "username" 	=> DB_user,
   "password" 	=> DB_pass,
]);
//-- Set the event dispatcher used by Eloquent models... (optional)
$capsule->setEventDispatcher(new Dispatcher(new Container));
//-- Setup the Eloquent ORM.
$capsule->bootEloquent();
//--Make this Capsule instance available globally.
$capsule->setAsGlobal();

if(defined('DEBUG_mode') && DEBUG_mode) {
    //-- Include and init Debug Bar
    $debugbar = new \DebugBar\StandardDebugBar();
    $ormToPdo = [
        "Eloquent" => $capsule->getConnection()->getPdo(),
    ];
    $collector = new \DebugBar\DataCollector\PDO\PDOCollector();
    foreach($ormToPdo as $orm => $pdo) {
        $traceablePDO = new \DebugBar\DataCollector\PDO\TraceablePDO($pdo);
        $collector->addConnection($traceablePDO, $orm);
    }
    $debugbar->addCollector($collector);
    $debugbarRenderer = $debugbar->getJavascriptRenderer();
}

//-- User ID & Master Mode if Frontend loading
define('USER_master',($_SESSION['zl_user']['id']>0?$_SESSION['zl_user']['id']:5));
if(defined('CRM_fe')) {
    if(isset($_GET['dev']) && $_GET['dev']>0) {
        $_SESSION['site']['maintenance_skip'] = true;
    }
    $_SESSION['site']['user'] = USER_master;
    define('USER_id', $_SESSION['site']['user']);
}
//-- Include Main Files
include(ROOT_include."setting.php");
include(ROOT_include."define.php");
include(ROOT_include."db.php");
include(ROOT_include."class.php");
include(ROOT_include."form.php");
include(ROOT_include."menu.php");

//-- Initialise ZULU
$zulu = new zulu($MAIN_config);

//-- Class Inclusions
foreach($CLASS_inclusion as $CLASS_inclusion_class=>$CLASS_inclusion_config) {
	include(ROOT_include."class/".$CLASS_inclusion_class.".php");
	if(!isset($CLASS_inclusion_config['load'])||(isset($CLASS_inclusion_config['load'])&&$CLASS_inclusion_config['load'])) {
        $plural = (isset($CLASS_inclusion_config['plural'])&&$CLASS_inclusion_config['plural']?true:false);
        $class_name = $CLASS_inclusion_class.($plural?'s':null);
        $define_name = "class_".$CLASS_inclusion_class;
		${$define_name} = new $class_name;
	}
    unset($plural,$class_name,$define_name);
}

//-- Page Inclusions
if(!defined('CRM_fe')) {
    define('PAGE_frame',($_GET['Page']!=NULL?$_GET['Page']:'index'));
    define('PAGE_action',($_GET['Action']!=NULL?$_GET['Action']:NULL));
    define('PAGE_id',($_GET['id']!=NULL?$_GET['id']:0));
    include(ROOT_include."page/".SECTION_path.PAGE_frame.".php");
}

//-- Site Core Template
include(ROOT_include."template.php");

//-- Data Compilation
include(ROOT_include."compiler.php");

//-- Check IP Ban
if(!$class_ip->valid_access(['ip'=>$_SERVER['REMOTE_ADDR'],'type'=>'global'])) {
	include(MAIN_url."error_docs/forbidden.html");
	exit;
}

//-- Domain Host & HTTPS redirects
if(defined('CRM_fe')) {
    if(DOMAIN_host && $_SERVER['HTTP_HOST'] != DOMAIN_host) {
        $location = 'http'.(FORCE_https?'s':null).'://'.DOMAIN_host.$_SERVER['REQUEST_URI'];
        if($settings['ws_status'] == '1') {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header('Location: '.$location);
        exit;

    } elseif(FORCE_https && ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'http') || empty($_SERVER['HTTPS']) || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '80'))) {
        $location = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if($settings['ws_status'] == '1') {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header('Location: '.$location);
        exit;
    }
}
