<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Variables
define('DOC_root',$_SERVER['DOCUMENT_ROOT']);
define('MASTER_mode','web'); //web: website, main: master zulu
define('MASTER_version',3.2);
define('MAIN_name','Zulu Shopfront');
define('MAIN_copyright','Zulu Shopfront');
define('MAIN_rel','/site/');
define('MAIN_path',DOC_root.MAIN_rel);
define('MAIN_host',DOMAIN_host);
define('MAIN_host_protocol','http'.(FORCE_https?'s':null).'://');
define('MAIN_url',MAIN_host_protocol.MAIN_host.MAIN_rel);
define('MAIN_root','zulusys.nz');
define('DEFAULT_host','zulucms.co.nz');

define('SUPPORT_email','info@zulusys.co.nz');
define('SUPPORT_phone','(09) 280 4401');

define('MAIL_name','Zulu CRM');
define('MAIL_email','info@zulusys.nz');

define('FE_path','');
define('FE_rel','/'.FE_path);
define('FE_abs',DOC_root.FE_rel);
define('FE_crm','../'); //-- path external from the root folder
define('FE_host',MAIN_host_protocol.$_SERVER['HTTP_HOST']);
define('FE_url',MAIN_host_protocol.$_SERVER['HTTP_HOST'].'/'.FE_path);

//-- MISC
define('URL_infosite','https://www.zulusys.nz/');
define('URL_contactsite','https://www.zulusys.nz/contact-us/');

define('DEV_mode',false);
define('DEV_email','contactus@razorweb.co.nz');

//-- Pagination
define('MAX_per_page',25);
define('MAX_post_per_page',6);
define('MAX_to_show',9);
define('MAX_page_index',12);

//-- SECTION SYSTEM
$SECTION_inclusion = array(
	"admin"=>array("path"=>"admin/"),
	"front"=>array("path"=>NULL)
);
define('SECTION_path',$SECTION_inclusion[MASTER_section]['path']);
define('SECTION_path_admin','admin');

//-- TPL
define('TPL_folder',"default");
define('TPL_root',MAIN_path."template/".TPL_folder."/");
define('TPL_rel',MAIN_rel."template/".TPL_folder."/");
define('TPL_abs',MAIN_url."template/".TPL_folder."/");
//define('TPL_logo,'');

//-- CLASS SYSTEM
$CLASS_inclusion = array(
	"cache"=>[],
	"client"=>[],
	"user"=>[],
	"setting"=>[],
	"file"=>[],
	"product"=>[],
	"rule"=>[],
	"sale"=>[],
	"xero"=>[],
	"mod_sms"=>[],
	"post"=>[],
	"subscribe"=>[],
	"data"=>[],
	"form_post"=>[],
    "website"=>[],
	"website_menu"=>[],
	"ip_ban"=>[],
	"module"=>[],
	"support"=>[],
	"wishlist"=>[],
	"object"=>['plural'=>true],
	'renew'=>[],
	"mod_sms"=>[],
);

//-- MAIN CONFIG COMPILE
$MAIN_config = array(
	"SECTION_inclusion"=>$SECTION_inclusion,
	"CLASS_inclusion"=>$CLASS_inclusion
);

//-- WEBSITE CONFIG
define('WEBSITE',true);
define('WEBSITE_dev',false);
//define('WEBSITE_cb_force_default',false);
define('WEBSITE_template','default');
$WEBSITE_template_menu = [
	"default"	=>	"Main Menu",
	"foot_1"	=>	"Footer Left",
	"foot_2"	=>	"Footer Center",
	"foot_3"	=>	"Footer Right",
];
$WEBSITE_feature = [
	'testimonial'	=>	true,
	'faq'			=>	true,
	'news'			=>	true,
	'slider'			=>	true,
	'gallery'		=>	true,
	'dealers'		=>	true,
];

//-- DIRECTORY LEVELS
$DIRECTORY_level = 99;
$DIRECTORY_vl = 0;
$DIRECTORY_tab = "--";
$output = [];

define('GOOGLE_MAPS_API_KEY', 'AIzaSyA9uCBcPT0qnOQXimbQOJ5ScMm1OpyfScQ');

//-- DEFAULT PAGE LINKS
//-- directories
define('DIR_checkout', 'checkout/');
define('DIR_account', 'members/');
define('DIR_browse', 'browse/');
//-- browse
define('LINK_browse', FE_rel.DIR_browse);
define('LINK_product', FE_rel.DIR_browse."product.php");
define('LINK_product_reviews', FE_rel.DIR_browse."product_reviews.php");
define('LINK_product_review', FE_rel.DIR_browse."product_review.php");
define('LINK_search', FE_rel.DIR_browse."search.php");
define('LINK_brands', FE_rel.DIR_browse."brand.php");
//-- checkout
define('LINK_basket', FE_rel.DIR_checkout."basket/");
define('LINK_checkout', FE_rel.DIR_checkout);
define('LINK_checkout_shipping', FE_rel.DIR_checkout."shipping/");
define('LINK_checkout_payment', FE_rel.DIR_checkout."payment/");
define('LINK_checkout_complete', FE_rel.DIR_checkout."complete/");
//-- account
define('LINK_account', FE_rel.DIR_account);
define('LINK_account_login', FE_rel.DIR_account."login.php");
define('LINK_account_logout', FE_rel.DIR_account."logout/");
define('LINK_account_register', FE_rel.DIR_account."register.php");
define('LINK_account_forgot', FE_rel.DIR_account."forgot.php");
define('LINK_account_reset', FE_rel.DIR_account."reset.php");
define('LINK_account_update', FE_rel.DIR_account."update.php");
define('LINK_account_orders', FE_rel.DIR_account."order.php");
define('LINK_account_order_view', FE_rel.DIR_account."order_view.php");
define('LINK_account_wishlist', FE_rel.DIR_account."wishlist.php");
define('LINK_account_support', FE_rel.DIR_account."support.php");
define('LINK_account_support_new', FE_rel.DIR_account."support_new.php");
define('LINK_account_verify', FE_rel.DIR_account."verify/");
//-- custom
define('LINK_listing_new', FE_rel.DIR_account."new-listing/");
define('LINK_listing_new_type', FE_rel.DIR_account."new-listing/type/");
define('LINK_listing_new_detail', FE_rel.DIR_account."new-listing/details/");
define('LINK_listing_new_media', FE_rel.DIR_account."new-listing/media/");
define('LINK_listing_new_summary', FE_rel.DIR_account."new-listing/summary/");
define('LINK_account_listings', FE_rel.DIR_account."listings/");
define('LINK_account_listings_active', FE_rel.DIR_account."listings/active/");
define('LINK_account_listings_sold', FE_rel.DIR_account."listings/sold/");
define('LINK_account_listings_unsold', FE_rel.DIR_account."listings/unsold/");
define('LINK_account_watchlist', FE_rel.DIR_account."watchlist/");
define('LINK_account_won', FE_rel.DIR_account."purchases/");
define('LINK_account_lost', FE_rel.DIR_account."missed/");
define('LINK_account_sales_record_database', FE_rel.DIR_account."sales-record-database/");
define('LINK_listing_edit', FE_rel.DIR_account."edit-listing/");
define('LINK_listing_edit_detail', FE_rel.DIR_account."edit-listing/details/");
define('LINK_listing_edit_media', FE_rel.DIR_account."edit-listing/media/");
define('LINK_listing_edit_summary', FE_rel.DIR_account."edit-listing/summary/");
define('LINK_listing_relist', FE_rel.DIR_account."relist/");
define('LINK_listing_relist_detail', FE_rel.DIR_account."relist/details/");
define('LINK_listing_relist_media', FE_rel.DIR_account."relist/media/");
define('LINK_listing_relist_summary', FE_rel.DIR_account."relist/summary/");
define('LINK_account_membership', FE_rel.DIR_account."membership/");
define('LINK_account_membership_view', FE_rel.DIR_account."membership-view/");
define('LINK_account_membership_change', FE_rel.DIR_account."membership-change/");
define('LINK_account_membership_cancel', FE_rel.DIR_account."membership-cancel/");
define('PEDIGREE_provider', "Breeders Bible");
define('PEDIGREE_url', "http://www.breedersbible.com");
