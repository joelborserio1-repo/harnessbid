<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

if(!defined('CRM_fe')) {
    $settings = Settings::where('user_id',USER_master)->get();
} else {
    $settings = Settings::where('user_id',USER_id)->get();
}
$settings = Settings::makeArray($settings);

define('MAIN_name',stripslashes($settings['name']));
define('MAIN_company',stripslashes($settings['company']));
define('MAIN_email',$settings['contact_email']);
define('MAIN_phone',$settings['contact_phone']);
define('MAIN_logo',$settings['image']);

define('CONTACT_name',$settings['contact_name']);
define('CONTACT_email',MAIN_email);
define('CONTACT_phone',MAIN_phone);

define('CRM_theme',$settings['theme']);

if(!defined('FORCE_https')) {
    define('FORCE_https',$settings['ws_site_force_https']);
}
if(!defined('DOMAIN_host')) {
    define('DOMAIN_host',$settings['ws_site_host']);
}