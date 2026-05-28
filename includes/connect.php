<?php

/* LOAD USER PROFILE */
$class_user->user_session_set(['id'=>USER_id]);
$user = $class_user->user_data(['id'=>USER_id]);
$setting = $class_setting->setting_data(['user_id'=>USER_id,'set_global'=>true]);

/*DEFAULTS*/
define('FE_tpl',"template/default/");
define('FE_tpl_rel',FE_rel.FE_tpl);
define('FE_profile_rel',FE_rel."template/profile/".$setting['ws_template_path']."/");
define('FE_profile_path',FE_path."template/profile/".$setting['ws_template_path']."/");
define('FE_user_rel',FE_rel."template/user/".$user['token']."/");
define('FE_user_path',FE_path."template/user/".$user['token']."/");

define('SITE_status',($setting['ws_status']>0?$setting['ws_status']:'0'));
define('SITE_title',stripslashes(($setting['ws_site_name']!=NULL?$setting['ws_site_name']:$setting['name'])));
define('SITE_slogan',stripslashes($setting['ws_site_slogan']));
define('SITE_program',$class_website->config->program);

define('META_title',(trim($setting['ws_meta_title'])!=NULL?stripslashes($setting['ws_meta_title']):SITE_title));
define('META_keyword',stripslashes($setting['ws_meta_keyword']));
define('META_description',stripslashes($setting['ws_meta_description']));

define('KEY_google_analytic',stripslashes($setting['ws_module_google_ga_profile']));

define('TP_rel',FE_profile_rel);
define('TP_path',FE_profile_path);

define('USR_rel',FE_user_rel);
define('USR_path',FE_user_path);

define('LOCALE_currency',LOCALE_currency_symbol);

$zulu->template->css_file = $zulu->template->css = [];
$zulu->template->js_file = $zulu->template->js_code = $zulu->template->jquery_code = [];
$zulu->template->body_class = [];
$zulu->template->html_body_open = $zulu->template->html_body_close = $zulu->template->html_foot = [];
$zulu->template->script_head = $zulu->template->script_body = $zulu->template->script_foot = '';

/* MAINTENANCE MODE */
if(SITE_status!=1) {
	$site_message = stripslashes($setting['ws_status_msg']);
	$zulu->template->body_class[] = 'maintenance';
	$zulu->template->body_class[] = 'site-status-'.SITE_status;
	$zulu->template->site_status = SITE_status;
	$zulu->template->site_status_msg = $site_message;

	//-- skip?
	if(USER_id==$_SESSION['zl_user']['id']) {
		$skip_uc = true;
	} elseif($_SESSION['site']['maintenance_skip']) {
		$skip_uc = true;
	}
	if($force_uc) {
		$skip_uc = false;
	}

	//-- allow if user logged in or skip
	if($skip_uc) {
		$zulu->template->site_status = 1;
	}
} else {
	$zulu->template->site_status = SITE_status;
}

/* SHOP ACTIVE MESSAGE */
if(!$class_cache->exists('fe_shop_active')) {
	$class_cache->save('fe_shop_active',$class_website->shop_active(),strtotime('+1 minute'));
}
define('SHOP_disable_msg', ($class_setting->data['ws_shop_chk_dis_msg']!=NULL?$class_setting->data['ws_shop_chk_dis_msg']:'Ordering is disabled at present.'));
define('SHOP_active', $class_cache->load('fe_shop_active'));

/* SHOP DATA */
if(SITE_program=='ZULUSHP') {
	$global_cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'summary'=>true));
}

/* TEMPLATE CSS FOR INDIVIDUAL USER */
$tpl_profile_path = MAIN_rel.FE_crm.$class_user->authorised->file_web_path;
$tpl_profile_path_abs = $zulu->path_clean($_SERVER['DOCUMENT_ROOT'].$tpl_profile_path);
if(file_exists($tpl_profile_path_abs."style.css")) {
	$zulu->template->css_file[] = $zulu->path_clean($tpl_profile_path."style.css?11");
}

/* TEMPLATE CSS SETTING FILES */
if(file_exists($tpl_profile_path_abs."_css.txt")) {
	$css_content = file_get_contents($tpl_profile_path_abs."_css.txt");
	$zulu->template->css[] = $class_website->template_css_parse($css_content);
}
$theme_setting = $class_setting->setting_data(['key_start'=>'ws_theme_']);
foreach($theme_setting as $th_k=>$th_v) {
	$theme_setting[str_replace('ws_theme_','',$th_k)] = $th_v;
}

/* USER CSS FILE */
$usr_profile_path = MAIN_rel.$class_website->user_folder;
$usr_profile_path_abs = $zulu->path_clean($_SERVER['DOCUMENT_ROOT'].$usr_profile_path);
if(file_exists($usr_profile_path_abs."style.css")) {
	$zulu->template->css_file[] = $zulu->path_clean($usr_profile_path."style.css");
}
if(file_exists($tpl_profile_path_abs."_css.txt") && $class_setting->data['ws_logo_color'] != null) {
	$css_content = file_get_contents($tpl_profile_path_abs."_css.txt");
	$zulu->template->css[] = $class_website->template_css_parse($css_content);
}
$theme_setting = $class_setting->setting_data(['key_start'=>'ws_theme_']);
foreach($theme_setting as $th_k=>$th_v) {
	$theme_setting[str_replace('ws_theme_','',$th_k)] = $th_v;
}

/* LOGO */
$logo = $class_user->user_logo(USER_id);
if(file_exists($_SERVER["DOCUMENT_ROOT"].FE_user_rel.'images/logo.png')) {
	$zulu->template->logo_url = FE_user_rel.'images/logo.png';
} elseif($logo!=NULL) {
	$zulu->template->logo_url = MAIN_rel.$logo;
} else {
	//none
	if(file_exists($_SERVER["DOCUMENT_ROOT"].FE_profile_rel.'images/logo.png')) {
		$zulu->template->logo_url = FE_profile_rel.'images/logo.png';
	}
}
if($zulu->template->logo_url!=NULL) {
	$zulu->template->css[] = "
	.site-logo {
		background-image:url(".$zulu->template->logo_url.");
	}
	";
}
$zulu->template->website_html['head_open'] = '<meta charset="utf-8">';

/* LOAD */
$form_edit = new form;

$COUNTRY_default = 'New Zealand';
$main_URL_REL = FE_rel;

$SHIP_option = $class_sale->config->ship;
$PAY_option = $class_sale->config->payment;

$zulu->template->script_head .= html_entity_decode(stripslashes($setting['ws_tpl_script_head']));
$zulu->template->script_body .= html_entity_decode(stripslashes($setting['ws_tpl_script_body']));
$zulu->template->script_foot .= html_entity_decode(stripslashes($setting['ws_tpl_script_foot']));
$zulu->template->body_class[] = "zulu-".PAGE_file;

if(isset($_SESSION['user'])) {
	$MEMBER_data = $_SESSION['user'];
	define('CLIENT_auth',true);
	$client_in = Clients::find($_SESSION['user']['id']);
	define('CLIENT_subscribed', ($client_in->subscribed?true:false));

} else {
	define('CLIENT_auth',false);
	define('CLIENT_subscribed',false);
}

if($_SESSION['zl_setting']['data']==NULL||$_SESSION['zl_setting']['frontend']==0) {
	$data = $class_setting->setting_data(['user_id'=>$class_user->authorised->id]);
	$_SESSION['zl_setting']['data'] = $data;
	$_SESSION['zl_setting']['frontend'] = 1;
}

$BROWSE_sort_array = [
    //"0"	=> ['label'=>"Default",'sort'=>"add_feature DESC, CASE WHEN price_special>0 OR special=1 OR EXISTS (SELECT * FROM product_special ps INNER JOIN product_special_item psi ON ps.id=psi.product_special_id WHERE ps.status=1 AND psi.product_id=product.id AND ps.date_from <= ".time()." AND ps.date_to > ".time().") OR EXISTS (SELECT * FROM product p2 WHERE p2.parent_id=product.id AND p2.hide=0 AND p2.status=1 AND p2.type='product' AND p2.type_variant=2 AND EXISTS(SELECT * FROM product_special ps INNER JOIN product_special_item psi ON ps.id=psi.product_special_id WHERE psi.product_id=p2.id AND ps.status=1 AND ps.date_from <= ".time()." AND ps.date_to > ".time().")) THEN 1 ELSE 0 END DESC, product.sort ASC"],
	"0"	=> ['label'=>"Default",'sort'=>"add_feature DESC, CASE WHEN price_special>0 OR special=1 THEN 1 ELSE 0 END DESC, product.sort ASC"],
    "5"	=> ['label'=>"Relevance",'sort'=>'rel1 DESC, rel2 DESC, rel3 DESC, add_feature DESC'],
	"1"	=> ['label'=>"Title",'sort'=>'name ASC, add_feature DESC'],
	"2"	=> ['label'=>"Lowest price",'sort'=>'CASE WHEN price_special>0 THEN price_special ELSE product.price END ASC, add_feature DESC, CASE WHEN is_poa=1 THEN 1 ELSE 0 END ASC'],
	"3"	=> ['label'=>"Highest price",'sort'=>'CASE WHEN price_special>0 THEN price_special ELSE product.price END DESC, add_feature DESC, CASE WHEN is_poa=1 THEN 1 ELSE 0 END ASC'],
	"4"	=> ['label'=>"Latest",'sort'=>'new DESC, stat_add DESC, add_feature DESC'],
	"6"	=> ['label'=>"Closing Soon",'sort'=>'time_close ASC, add_feature DESC'],
	"7"	=> ['label'=>"Most Popular",'sort'=>'stat_view DESC, add_feature DESC'],
];
$BROWSE_sort_relevance_key = 5;
$WISHLIST_enabled = $class_setting->data['ws_shop_wishlist_enable'];
$SUPPORT_enabled = $class_setting->data['ws_shop_support_enable'];
$COUPON_enabled = $class_setting->data['ws_shop_coupon_enable'];
$BRAND_enabled = $class_setting->data['ws_shop_brands_enable'];
$PRODUCT_count_disabled = $class_setting->data['ws_shop_product_counts_disabled'];
define('PRODUCT_reviews_enabled', $class_setting->data['ws_shop_product_review_enable']);

/*INCLUDE*/
include_once FE_abs."includes/connect.function.php";

/** Core JS/CSS File inclusions **/
//-- Jquery
$zulu->template->js_file['jquery'] = FE_tpl_rel."assets/jquery/jquery.min.js";
//-- Jquery UI
$zulu->template->css_file['jquery-ui'] = 'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css';
$zulu->template->js_file['jquery-ui'] = 'https://code.jquery.com/ui/1.13.1/jquery-ui.min.js';

include_once FE_abs."template/js-define.php";

if(!isset($_SESSION['currencyCode']) && isset($_COOKIE['currencyCode'])) {
	$_SESSION['currencyCode'] = $_COOKIE['currencyCode'];
}

// ################################################################################################################################################

//-- Form Posted?
if(isset($_POST['action'])) {
	if($_POST['action']=='form_post_submit') {
	    $class_form_post->form_process();

	} elseif($_POST['action'] == 'store_geo') {
		// store session
		$_SESSION['LOCATION'] = '';

		// store cookie

		exit;

	} elseif($_POST['action'] == 'store_timezone') {
		//-- store session
		$_SESSION['TIMEZONE'] = $_POST['timezone'];

		//-- store cookie
		setcookie('TIMEZONE', $_SESSION['TIMEZONE'], time() + (86400 * 30), FE_rel);

		/*if(CLIENT_auth) {
			$data = $class_client->client_edit($_SESSION['user']['id'], ['timezone'=>$_SESSION['TIMEZONE']]);
		}*/

		exit;

	} elseif($_POST['action'] == 'load_region_options') {
		$location_id = $_POST['location_id'];
		$region_options = LocationRegion::optionArray($location_id);
		$html = "<option value='0'>Select...</option>";
		foreach($region_options as $key=>$val) {
			$html .= "<option value='".$key."'>".$val."</option>";
		}
		echo json_encode(['html'=>$html]);
		exit;
	}
}

// ###################
// -- Sales Record Database
// ###################

if(PAGE_file=='sales_record_database') {

	//-- subscribe check
	if(!CLIENT_subscribed) {
		header("Location: ".$zulu->front_link(LINK_account_membership));
		exit;
	}

	$query = SaleRecord::query();
	$line_table = null;

	//-- search filters
	$search_term = null;
	if(isset($_GET['search']) && $_GET['search']) {
		$search = $zulu->esc($_GET['search']);
		$query->where('horse_name', 'like', "%".strtolower($search)."%");
		$search_term = $search;
	}
	if(isset($_GET['search_sire']) && $_GET['search_sire']) {
		$query->where('spec_sire', 'like', "%".$zulu->esc($_GET['search_sire'])."%");
		if(!$search_term) {
			$search_term = $_GET['search_sire'];
		}
	}
	if(isset($_GET['search_dam']) && $_GET['search_dam']) {
		$query->where('spec_dam', 'like', "%".$zulu->esc($_GET['search_dam'])."%");
		if(!$search_term) {
			$search_term = $_GET['search_dam'];
		}
	}
	if(isset($_GET['search_source']) && $_GET['search_source']) {
		$query->where('source', 'like', "%".$zulu->esc($_GET['search_source'])."%");
		if(!$search_term) {
			$search_term = $_GET['search_source'];
		}
	}
	if(isset($_GET['search_date_from']) && $_GET['search_date_from']) {
		$query->where('sale_date', '>=', $zulu->dateEncode($zulu->esc($_GET['search_date_from'])));
	}
	if(isset($_GET['search_date_to']) && $_GET['search_date_to']) {
		$query->where('sale_date', '<=', $zulu->dateEncode($zulu->esc($_GET['search_date_to'])));
	}
	if(!isset($_GET['search_filter']) || !$_GET['search_filter']) {
		$_GET['search_filter'] = 'date_desc';
	}

	//-- pagination
	$limit = MAX_per_page;
	$page = !isset($_GET['Pg']) || !$_GET['Pg'] ? 1 : $_GET['Pg'];
	$start = $page > 1 ? $limit * ($page - 1) : 0;
	$query_all = clone $query;
	$sale_records_count = $query_all->count();

	//-- sort order
	switch ($_GET['search_filter']) {
		case 'date_asc':
			$query->orderBy('sale_date', 'ASC');
			break;
		case 'price_asc':
			$query->orderBy('sale_amount', 'ASC');
			break;
		case 'price_desc':
			$query->orderBy('sale_amount', 'DESC');
			break;
		case 'name_asc':
			$query->orderBy('horse_name', 'ASC');
			break;
		case 'date_desc':
		default:
			$query->orderBy('sale_date', 'DESC');
			break;
	}

	if($sale_records_count > 0) {
		$query->offset($start)->take($limit);
		$sale_records = $query->get();
		foreach($sale_records as $sale_record) {
			$product = $sale_record->product;
			$name = $sale_record->horse_name;
			$dam = $sale_record->spec_dam;
			$sire = $sale_record->spec_sire;
			$source = $sale_record->product_id>0 ? 'Harnessbid' : 'Other';
			$age = $product->spec_age;
			$gait = $product->spec_gait;
			$currency = $sale_record->sale_currency;
			$name_extra = [];
			if($gait) {
				$name_extra[] = "<span class='gait' title='".$gait."'>".strtolower($gait[0])."</span>";
			}
			if($age) {
				$name_extra[] = "<span class='age' title='".$age." year".$zulu->s($age)." of age'>".$age."y</span>";
			}

			$line_table .= "<tr>
				<td class='name'>".$name."".($name_extra>0?" ".implode(' ', $name_extra):null)."</td>
				<td class='breed'>".$zulu->icon('venus')." ".$dam."<br />".$zulu->icon('mars')." ".$sire."</td>
				<td class='source'>".$source."</td>
				<td class='date'>".$zulu->dateTimezone($sale_record->sale_date, 'j M Y')."</td>
				<td class='price'>$".number_format($sale_record->sale_amount)." ".$currency."</td>
			</tr>";
		}
		$line_table = "<table class=\"grid-table sale-records\">
			<thead>
				<tr><td>Horse Name</td><td>Breeding</td><td>Source</td><td>Sale Date</td><td>Sale Price</td></tr>
			</thead>
			<tbody>".$line_table."</tbody>
		</table>";
	}

	//-- pagination
	$total_count = $sale_records_count;
	$pagination = $zulu->pagination($page, ['count'=>$total_count, 'link'=>$zulu->front_link(true, ['self'=>true, 'filter'=>['Pg']]), 'page_max_page'=>$limit]);

	//Meta
	$META_title_item[] = "Sales Record Database";
	//BC
    $zulu->template->breadcrumb[] = ['label'=>'Sales Record Database'];

}

// ################
// -- Listings Lost
// ################

if(PAGE_file=='listings_lost') {
	$client = Clients::find($_SESSION['user']['id']);

	$products = $client->productsLost;
	$product_results = [];
	foreach($products as $product) {
		$product_results[] = $product->listingBannerHTML();
	}
	$product_count = count($product_results);

	$zulu->template->body_class[] = 'page-members';
    $META_title_item[] = "My Missed Listings";
	$zulu->template->breadcrumb[] = ['link'=>$zulu->front_link(LINK_account_lost),'label'=>'My Missed Listings'];

}

// ################
// -- Listings Won
// ################

if(PAGE_file=='listings_won') {
	$client = Clients::find($_SESSION['user']['id']);

	$sale_records = $client->saleRecordsBuyer;
	$product_results = [];
	foreach($sale_records as $sale_record) {
		$product = $sale_record->product;
		if($product) {
			$product_results[] = $product->listingBannerHTML();
		}
	}
	$product_count = count($product_results);

	$zulu->template->body_class[] = 'page-members';
    $META_title_item[] = "My Purchases";
	$zulu->template->breadcrumb[] = ['link'=>$zulu->front_link(LINK_account_won),'label'=>'My Purchases'];

}

// ################
// -- Watchlist
// ################

if(PAGE_file=='watchlist') {
	$client = Clients::find($_SESSION['user']['id']);

	$watchlists = $client->watchlists;
	$product_results = [];
	foreach($watchlists as $watchlist) {
		$product = $watchlist->product;
		if($product && $product->isLive()) {
			$product_results[] = $product->listingBannerHTML();
		}
	}
	$product_count = count($product_results);

	$zulu->template->body_class[] = 'page-members';
    $META_title_item[] = "My Watchlist";
	$zulu->template->breadcrumb[] = ['link'=>$zulu->front_link(LINK_account_listings),'label'=>'My Watchlist'];

}

// ################
// -- Member Listings
// ################

if(PAGE_file=='listings') {

	$client = Clients::find($_SESSION['user']['id']);
	$query = Products::query();
	$query->where('client_id', $_SESSION['user']['id']);

	if(isset($_GET['filter'])) {
		switch ($_GET['filter']) {
			case 'sold':
				$query->where('sale_record_id', '>', 0);
				$query->where('live', 0);
				$query->orderBy('sale_record_id', 'DESC');
				break;
			case 'unsold':
				$query->where('sale_record_id', '<=', 0);
				$query->where('live', 0);
				$query->orderBy('stat_update', 'DESC');
				break;
			case 'active':
				$query->where('live', 1);
				$query->orderBy('id', 'DESC');
				break;
			default:
				$query->orderBy('live', 'DESC');
				$query->orderBy('stat_update', 'DESC');
				break;
		}
	} else {
		$query->orderBy('live', 'DESC');
		$query->orderBy('stat_update', 'DESC');
	}

	$products = $query->get();
	$product_results = [];
	foreach($products as $product) {
		$product_results[] = $product->listingBannerHTML(true);
	}

	$zulu->template->body_class[] = 'page-members';
    $META_title_item[] = "My Listings";
	$zulu->template->breadcrumb[] = ['link'=>$zulu->front_link(LINK_account_listings),'label'=>'My Listings'];

	$zulu->template->jquery_code[] = "
	$('#input-filter').change(function() {
		let val = $(this).val();
		document.location.href = '".$zulu->front_link(LINK_account_listings)."&filter='+val;
	});
	";

}

// ################
// -- Listing Process
// ################

if(PAGE_file=='new_listing_type') {

	if(isset($_SESSION['LISTING']) && (isset($_SESSION['LISTING']['edit_id']) || isset($_SESSION['LISTING']['relist_id']))) {
		Products::sessionReset();
	}

	//-- Membership highlighters
	$membership_subscription = $class_renew->template_data(['id'=>1]);
	//$renew_row = $class_renew->renew_data(['client_id'=>$_SESSION['user']['id'],'sort'=>'id DESC','first'=>true,'active'=>true]);
	//$has_membership = ($renew_row['id'] > 0 ? true : false);

	//-- Post form
	if($_POST) {
		$form_edit->valid = true;

		if($form_edit->validate(['listing_type'])) {
			$form_edit->valid = false;
			$zulu->notification_set('Please select a Listing Type to proceed.', 2);
		}

		if(!CLIENT_subscribed) {
			if($_POST['premier_sub'] != '1' && $_POST['premier_sub'] != '0') {
				$form_edit->valid = false;
				$zulu->notification_set("We noticed you didn't select whether you'd like to become a Premier member. Please select either Yes or No to continue.", 2);
			}
			if($_POST['premier_sub'] == '1') {
				$zulu->notification_set("Great, please just fill-in this form then press 'Purchase Subscription'. We will then take you back here to continue listing.", 1);
				header("Location: ".$zulu->front_link(FE_rel.'members/membership/',['query'=>['return'=>'listing']]));
				exit;
			}
		}

		if($form_edit->valid) {
			if(!isset($_SESSION['LISTING'])) {
				$_SESSION['LISTING'] = [
					'meta'	=>	[],
					'images'=>	[],
					'token'	=>	$zulu->serial(),
				];
			}

			$_SESSION['LISTING']['listing_type'] = $_POST['listing_type'];
			$_SESSION['LISTING']['listing_mode'] = (isset($_POST['listing_mode']) && in_array($_POST['listing_mode'], ['horse_auction','horse_buynow','marketplace']) ? $_POST['listing_mode'] : ($_POST['listing_type'] == 'classified' ? 'horse_buynow' : 'horse_auction'));
			$_SESSION['LISTING']['meta']['listing_kind'] = (isset($_POST['listing_kind']) && $_POST['listing_kind'] == 'marketplace' ? 'marketplace' : 'horse');

			if(isset($_POST['meta'])) {
				foreach($_POST['meta'] as $key=>$val) {
					$_SESSION['LISTING']['meta'][$key] = $val;
				}
			}

			header("Location: ".$zulu->front_link(LINK_listing_new_detail));
			exit;
		}

	} else {

		foreach($_SESSION['LISTING'] as $key=>$val) {
			if($key == 'meta') {
				foreach($val as $mkey=>$mval) {
					$_POST['meta'][$mkey] = $mval;
				}
			} else {
				$_POST[$key] = $val;
			}
		}

	}
    if(!isset($_POST['listing_kind']) || !$_POST['listing_kind']) {
        $_POST['listing_kind'] = (isset($_SESSION['LISTING']['meta']['listing_kind']) && $_SESSION['LISTING']['meta']['listing_kind'] ? $_SESSION['LISTING']['meta']['listing_kind'] : 'horse');
    }

	$new_listing = true;
	$step = 1;
	$zulu->template->body_class[] = 'page-new-listing';
    $META_title_item[] = "List Your Horse";

	if($_SESSION['user']['location_id'] > 0) {
		$price_location_id = $_SESSION['user']['location_id'];
	} else {
		$price_location_id = PriceList::$default_location_id;
	}
	$price_location = Location::find($price_location_id);
	$location_currency = $price_location->currency;
	$price_list = $price_location->priceList()->where('code', 'listing')->first();
	$listing_price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;

	if(!CLIENT_subscribed) {
		$saving_value = $price_list->price-$price_list->price_subscribe;
		$listing_fee_label = "<div class=\"price-wrapper\"><u>Your listing fee: $".number_format($listing_price, 2)."</u><br /><i class=\"color-gold\">Premier Listing Fee: ".number_format($price_list->price_subscribe, 2)."</i> (To get this price, see below)</div>";
	} else {
		$listing_fee_label = "<div class=\"price-wrapper\">Your <b>discounted</b> listing fee: $".number_format($listing_price, 2)."</div>";
	}

	$zulu->template->jquery_code[] = "
	$('.listing-radio-selector input[type=radio]').hide();
	$('.listing-radio-selector').click(function() {
		let radio = $(this).find('input[type=\"radio\"]');
		$('input[name=\"' + radio.attr('name') + '\"]').closest('.listing-radio-selector').removeClass('selected');
		$(this).addClass('selected');
		radio.attr('checked', 'checked');
		radio.prop('checked', true);

		".(CLIENT_subscribed?"if(radio.attr('name') == 'listing_type') { $(this).closest('form').submit(); } //-- Automatically redirect after sale type for subscribed members":NULL)."
	});

	$(document).on('click','input.checkbox-sub-join',function() {
		var prom = confirm('You are being redirected to sign up to the Premier Membership now, once done you will be taken back here to continue.');
		if(prom) {
			$(this).closest('form').submit();
		}
	});

	$(document).on('click','input.checkbox-sub-no',function() {
		$(this).closest('form').submit();
	});
	";

} elseif(PAGE_file=='new_listing_details') {

	$new_listing = true;
	$is_edit = $is_relist = false;
	if(isset($_GET['edit'])) {
		$edit_id = $_GET['edit'];
		if($edit_id > 0) {
			$is_edit = true;
			$new_listing = false;
			$product = Products::find($edit_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canEdit()) {
				$zulu->notification_set('You can no longer edit this listing.', 2);
				header("Location: ".$zulu->front_link($product->feURL()));
				exit;
			}
			if(!isset($_SESSION['LISTING']['edit_id']) || $_SESSION['LISTING']['edit_id'] != $edit_id) {
				Products::sessionReset();
				$product->loadSessionEdit();
			}
		}

	} elseif(isset($_GET['relist'])) {
		$relist_id = $_GET['relist'];
		if($relist_id > 0) {
			$is_relist = true;
			$new_listing = false;
			$product = Products::find($relist_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canRelist()) {
				$zulu->notification_set('You cannot relist that listing.', 2);
				header("Location: ".$zulu->front_link(LINK_account_listings));
				exit;
			}
			if(!isset($_SESSION['LISTING']['relist_id']) || $_SESSION['LISTING']['relist_id'] != $relist_id) {
				Products::sessionReset();
				$product->loadSessionRelist();
			}
		}
	}

	if(!isset($_SESSION['LISTING']) || ($new_listing && isset($_SESSION['LISTING']['edit_id']))) {
		header("Location: ".$zulu->front_link(LINK_listing_new));
		exit;
	}

	if(isset($_POST['action']) && $_POST['action'] == 'load_addon_options') {
		$location_id = $_POST['location_id'];
		if($location_id <= 0) {
			$location_id = PriceList::$default_location_id;
		}
		$price_lists = PriceList::where('location_id', $location_id)->get();
		$pricing = [];
		foreach($price_lists as $price_list) {
			$price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
			$pricing[$price_list->code] = number_format($price);
		}

		echo json_encode(['pricing'=>$pricing]);
		exit;
	}

	$listing_type = $_SESSION['LISTING']['listing_type'];
    $listing_kind = (isset($_SESSION['LISTING']['meta']['listing_kind']) && $_SESSION['LISTING']['meta']['listing_kind'] ? $_SESSION['LISTING']['meta']['listing_kind'] : 'horse');
    $is_marketplace_listing = ($listing_kind == 'marketplace');
	$feature_options = PriceList::addonArray();

	if(isset($_POST['action']) && $_POST['action'] == 'submit') {
		$form_edit->valid = true;

		if($form_edit->validate(['name'])) {
			$form_edit->valid = false;
			$zulu->notification_set($is_marketplace_listing ? 'Please enter a listing title.' : 'Please enter the name of the horse.', 2);

		} elseif(!$is_marketplace_listing && $form_edit->validate(['spec_gait', 'spec_sire', 'spec_dam', 'spec_sex', 'spec_colour', 'spec_age'])) {
			$form_edit->valid = false;
			$zulu->notification_set('Please enter the gait, sire, dam, sex, color and age of the horse.', 2);

		} elseif($is_marketplace_listing && $form_edit->validate(['marketplace_category', 'marketplace_condition'], ['meta'=>true])) {
			$form_edit->valid = false;
			$zulu->notification_set('Please select a marketplace category and condition.', 2);

		} elseif($_POST['location_id'] === '' || ($_POST['location_id'] > 0 && $form_edit->validate(['region_id']))) {
			$form_edit->valid = false;
			$zulu->notification_set($is_marketplace_listing ? 'Please enter the listing location and region.' : 'Please enter the location and region for the horse.', 2);

		} elseif($_POST['location_id'] === '0' && $form_edit->validate(['location_other'],['meta'=>true])) {
			$form_edit->valid = false;
			$zulu->notification_set($is_marketplace_listing ? 'Please enter the listing location and region.' : 'Please enter the location and region for the horse.', 2);

		}

		if($form_edit->valid) {
			if($listing_type == 'auction') {
				if($form_edit->validate(['price', 'price_reserve']) || $_POST['price'] <= 0 || $_POST['price_reserve'] <= 0) {
					$form_edit->valid = false;
					$zulu->notification_set('Please enter a Start Price and Reserve Price for the auction.', 2);
				} elseif($_POST['price'] > $_POST['price_reserve']) {
					$form_edit->valid = false;
					$zulu->notification_set('Please enter a Reserve Price which <b>not</b> less than the Start Price.', 2);
				}
			} else {
				if($form_edit->validate(['price']) && $form_edit->validate(['is_poa'])) {
					$form_edit->valid = false;
					$zulu->notification_set('Please enter a Price the listing.', 2);
				}
			}
		}
		if(!$is_edit && $listing_type != 'classified' && $form_edit->valid) {
			if($form_edit->validate(['date_close_fixed', 'time_close']) || ($_POST['date_close_fixed'] == 'Custom' && $form_edit->validate(['date_close_custom']))) {
				$form_edit->valid = false;
				$zulu->notification_set('Please enter a date and time for the listing to close.', 2);
			}
		}

		/*if($form_edit->valid && $_POST['location_id'] > 0 && !$_POST['bypass_usta']) {
			$location = Location::find($_POST['location_id']);
			if($location && $location->usta_lookup) {
				$usta = new UstaApi;
				$usta_result = $usta->horseSearch($_POST['name']);
				if(!$usta_result) {
					$form_edit->valid = false;
					$zulu->notification_set('The horses name entered does not match in the USTA database. Please check the horses name is typed exactly as it is registered with the USTA. If your horse is not in this database, you can skip this by clicking <a href="#" id="bypass-usta-trigger">here</a>.', 2);
				}
			}
		}*/

		if($form_edit->valid) {

			$_SESSION['LISTING']['name'] = $_POST['name'];
			$_SESSION['LISTING']['spec_gait'] = ($is_marketplace_listing ? null : $_POST['spec_gait']);
			$_SESSION['LISTING']['spec_sire'] = ($is_marketplace_listing ? null : $_POST['spec_sire']);
			$_SESSION['LISTING']['spec_dam'] = ($is_marketplace_listing ? null : $_POST['spec_dam']);
			$_SESSION['LISTING']['spec_sex'] = ($is_marketplace_listing ? null : $_POST['spec_sex']);
			$_SESSION['LISTING']['spec_colour'] = ($is_marketplace_listing ? null : $_POST['spec_colour']);
			$_SESSION['LISTING']['spec_age'] = ($is_marketplace_listing ? null : $_POST['spec_age']);
			$_SESSION['LISTING']['description'] = $_POST['description'];
			$_SESSION['LISTING']['location_id'] = $_POST['location_id'];
			$_SESSION['LISTING']['region_id'] = $_POST['region_id'];
			$_SESSION['LISTING']['currency_id'] = $_POST['currency_id'];
			$_SESSION['LISTING']['price'] = $zulu->dollar($_POST['price']);
			if($listing_type == 'auction') {
				$_SESSION['LISTING']['price_reserve'] = $zulu->dollar($_POST['price_reserve']);
				$_SESSION['LISTING']['is_poa'] = null;
			} else {
				$_SESSION['LISTING']['is_poa'] = $_POST['is_poa'] ?? null;
				if($_SESSION['LISTING']['is_poa']) {
					$_SESSION['LISTING']['price'] = null;
				}
			}
			if(!$is_edit) {
				if($_POST['date_close_fixed'] != 'Custom') {
					$carbon = new Carbon\Carbon($_POST['time_close'].' +'.$_POST['date_close_fixed'], $_SESSION['user']['timezone']);

				} else {
					$carbon = new Carbon\Carbon($_POST['time_close']." ".$_POST['date_close_custom'], $_SESSION['user']['timezone']);
				}
				$_SESSION['LISTING']['date_close'] = $carbon->getTimestamp();
				$_SESSION['LISTING']['date_close_fixed'] = $_POST['date_close_fixed'];
				$_SESSION['LISTING']['date_close_custom'] = $_POST['date_close_custom'];
				$_SESSION['LISTING']['time_close'] = $_POST['time_close'];
			}

			foreach($feature_options as $key=>$feature_data) {
				$_SESSION['LISTING'][$key] = (isset($_POST[$key])?'1':'0');
			}

			if(isset($_POST['meta'])) {
				foreach($_POST['meta'] as $key=>$val) {
					$_SESSION['LISTING']['meta'][$key] = $val;
				}
			}
			$_SESSION['LISTING']['bypass_usta'] = $_POST['bypass_usta'];

			$url = LINK_listing_new_media;
			if($is_edit) {
				$url = LINK_listing_edit_media.$edit_id."/";
			} elseif($is_relist) {
				$url = LINK_listing_relist_media.$relist_id."/";
			}

			header("Location: ".$zulu->front_link($url));
			exit;
		}

	} else {

		foreach($_SESSION['LISTING'] as $key=>$val) {
			if($key == 'meta') {
				foreach($val as $mkey=>$mval) {
					$_POST['meta'][$mkey] = $mval;
				}
			} else {
				$_POST[$key] = $val;
			}
		}

		if(!isset($_POST['time_close']) || !$_POST['time_close']) {
			$_POST['time_close'] = date('g:ia');
		}

	}

	if(!isset($_POST['location_id'])) {
		$_POST['location_id'] = $_SESSION['user']['location_id'];
	}
	if(!isset($_POST['region_id'])) {
		$_POST['region_id'] = $_SESSION['user']['region_id'];
	}
	if(!isset($_POST['currency_id']) || !$_POST['currency_id']) {
		$currency_location = Location::find(PriceList::$default_location_id);
		$_POST['currency_id'] = $currency_location->currency_id;
	}
    if($is_marketplace_listing && (!isset($_POST['meta']['marketplace_payment_mode']) || !$_POST['meta']['marketplace_payment_mode'])) {
        $_POST['meta']['marketplace_payment_mode'] = 'contact';
    }

	if(!isset($_POST['region_id']) || $_POST['region_id'] <= 0) {
		$location_manual = true;
	} else {
		$location = Location::find($_POST['location_id']);
		$location_name = $location->name;
		$region = LocationRegion::find($_POST['region_id']);
		$region_name = $region->name;
		if(!$location || !$region) {
			$location_manual = true;
		}
	}

	$bid_increment_manual = $_POST['meta']['min_bid_increment'] > 0 ? true : false;

	$step = 2;
	$location_options = Location::optionArray() + ['0'=>'Other'];
	$region_options = LocationRegion::optionArray($_POST['location_id']);
	$currency_options = Currency::optionArray();
	$addon_html = PriceList::addonSelectHTML($_POST['location_id']);
	$currency = Currency::find($_POST['currency_id']);
	$url_back = LINK_listing_new_type;

	$zulu->template->body_class[] = 'page-new-listing';
    $META_title_item[] = "List Your Horse";

	$zulu->template->jquery_code[] = "
	let bid_increments = ".json_encode(ProductListing::$bid_increments).";
	$('.listing-checkbox-selector').click(function() {
		let checkbox = $(this).find('input[type=\"checkbox\"]'),
			icon_checker = $(this).find('.icon.checker');
		if($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			checkbox.removeAttr('checked');
			checkbox.prop('checked', false);
			icon_checker.removeClass('fa-check-circle');
			icon_checker.removeClass('fas');
			icon_checker.addClass('far');
			icon_checker.addClass('fa-circle');
		} else {
			$(this).addClass('selected');
			checkbox.attr('checked', 'checked');
			checkbox.prop('checked', true);
			icon_checker.removeClass('fa-circle');
			icon_checker.removeClass('far');
			icon_checker.addClass('fas');
			icon_checker.addClass('fa-check-circle');
		}
	});
	$('#location-manual-trigger').click(function(e) {
		e.preventDefault();
		$('#location-manual').toggleClass('hide');
	});
	$('#location-manual #location-select').change(function() {
		let location_id = $(this).val();
		if(location_id !== '0') {
			$('#location-manual #location-other-field').hide();
			$('#location-manual #region-field').show();
			if(location_id === '') {
				$('#location-manual #region-select').html('');
			} else {
				$.post('".$zulu->front_link(true)."', {
					'action': 'load_region_options',
					'location_id': location_id,
				}, function(data) {
					let response = JSON.parse(data);
					$('#location-manual #region-select').html(response.html);
				});
			}
		} else {
			$('#location-manual #region-select').html('');
			$('#location-manual #region-field').hide();
			$('#location-manual #location-other-field').show();
		}
		loadAddonBlocks();
	});
	//$('#location-manual #location-select').trigger('change');
	".(!$location_manual?"
	$('#location-manual #region-select').change(function() {
		let region_name = $(this).find('option:selected').text(),
			location_name = $('#location-manual #location-select').find('option:selected').text();
		$('#location-text').html(region_name + ', ' + location_name);
		//$('#location-manual').addClass('hide');
	});
	$(document).on('change keyup', '#location-manual #location-other', function() {
		let val = $(this).val();
		$('#location-text').html(val);
	});
	":null)."
	$('input[name=\"date_close_fixed\"]').change(function() {
		let val = $(this).val();
		if(val == 'Custom') {
			$('#date-close-custom').show();
		} else {
			$('#date-close-custom').hide();
		}
	});
	$('.datepicker').datepicker({
		dateFormat: 'd MM yy',
		minDate: 1,
		maxDate: '+7D'
	});
	$('.timepicker').timepicker({
		forceRoundTime: true,
		closeOnWindowScroll: true,
		disableTextInput: true,
	});
	$(document).on('change keyup', '#price-input', function() {
		let price = parseFloat($(this).val()),
			bid_increment,
			bid_increment_last;
		if(isNaN(price)) {
			price = 0;
		}
		for(const key in bid_increments) {
			if(price <= key) {
				bid_increment = bid_increments[key];
				break;
			}
			bid_increment_last = bid_increments[key];
		}
		if(bid_increment == undefined) {
			bid_increment = bid_increment_last;
		}
		$('#bid-increment').html(Number(bid_increment).toLocaleString());
	});
	function loadAddonBlocks() {
		let location_id = $('#location-select').val();
		$.post('".$zulu->front_link(true)."', {
			'action': 'load_addon_options',
			'location_id': location_id,
		}, function(data) {
			let response = JSON.parse(data),
				pricing = response.pricing;
			$('.listing-checkbox-selector').each(function() {
				let key = $(this).data('key');
				if(pricing[key] !== undefined) {
					$(this).find('.price').html(pricing[key]);
				}
			});
		})
	}
	$('#currency-manual-trigger').click(function(e) {
		e.preventDefault();
		$('#currency-manual').toggleClass('hide');
		$('#currency-label').addClass('hide');
	});
	$('#price-input').trigger('change');
	$('.timepicker').trigger('change');
	$('#bid-increment-manual-trigger').click(function(e) {
		e.preventDefault();
		$('#bid-increment-manual').toggleClass('hide');
	});
	$('#bypass-usta-trigger').click(function(e) {
		e.preventDefault();
		$('#bypass-usta-input').val('1');
		$('#bypass-usta-input').closest('form').submit();
	});
	$('#is-poa').change(function() {
		if($(this).is(':checked')) {
			$('#input-price').attr('disabled', true);
		} else {
			$('#input-price').attr('disabled', false);
		}
	});
	";
	$zulu->template->js_file['timepicker'] = "https://cdn.jsdelivr.net/npm/timepicker@1.13.18/jquery.timepicker.min.js";
	$zulu->template->css_file['timepicker'] = "https://cdn.jsdelivr.net/npm/timepicker@1.13.18/jquery.timepicker.min.css";

} elseif(PAGE_file=='new_listing_media') {

	$new_listing = true;
	$is_edit = $is_relist = false;
	if(isset($_GET['edit'])) {
		$edit_id = $_GET['edit'];
		if($edit_id > 0) {
			$is_edit = true;
			$new_listing = false;
			$product = Products::find($edit_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canEdit()) {
				$zulu->notification_set('You cannot edit that listing.', 2);
				header("Location: ".$zulu->front_link(LINK_account_listings));
				exit;
			}
			if(!isset($_SESSION['LISTING']['edit_id']) || $_SESSION['LISTING']['edit_id'] != $edit_id) {
				header("Location: ".$zulu->front_link($product->feURLEdit()));
				exit;
			}
		}

	} elseif(isset($_GET['relist'])) {
		$relist_id = $_GET['relist'];
		if($relist_id > 0) {
			$is_relist = true;
			$new_listing = false;
			$product = Products::find($relist_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canRelist()) {
				$zulu->notification_set('You cannot relist that listing.', 2);
				header("Location: ".$zulu->front_link(LINK_account_listings));
				exit;
			}
			if(!isset($_SESSION['LISTING']['relist_id']) || $_SESSION['LISTING']['relist_id'] != $relist_id) {
				header("Location: ".$zulu->front_link($product->feURLRelist()));
				exit;
			}
		}
	}

	if(!isset($_SESSION['LISTING']) || ($new_listing && isset($_SESSION['LISTING']['edit_id']))) {
		header("Location: ".$zulu->front_link(LINK_listing_new));
		exit;
	}

	$listing_type = $_SESSION['LISTING']['listing_type'];
	$photos_allowed = 5;
	if(CLIENT_subscribed) {
		$photos_allowed = 10;
	}
	$upload_dir = "file/temp/listing_".session_id()."/";
	$upload_dir_images = $upload_dir."images/";
	$upload_dir_pedigree = $upload_dir."pedigree/";
	$upload_path = MAIN_path.$upload_dir;
	$upload_path_images = MAIN_path.$upload_dir_images;
	$upload_path_pedigree = MAIN_path.$upload_dir_pedigree;
	@mkdir($upload_path);
	@mkdir($upload_path_images);
	@mkdir($upload_path_pedigree);

	if(isset($_GET['action']) && $_GET['action'] == 'pedigree_remove') {
		if(isset($_SESSION['LISTING']['meta']['pedigree_file']) && $_SESSION['LISTING']['meta']['pedigree_file']) {
			@unlink($upload_path_pedigree.$_SESSION['LISTING']['meta']['pedigree_file']);
			$_SESSION['LISTING']['meta']['pedigree_file'] = null;
		}
		exit;
	}

	if(isset($_FILES['images'])) {
		if(count($_SESSION['LISTING']['images']) >= $photos_allowed) {
			exit;
		}
		$filename = $_FILES['images']['name'];
		$image_file = $upload_path_images.$filename;
		move_uploaded_file($_FILES['images']['tmp_name'], $image_file);
		$_SESSION['LISTING']['images'][] = $filename;
		echo json_encode(['url'=>MAIN_rel.$upload_dir_images.$filename]);
		exit;

	} elseif(isset($_FILES['pedigree'])) {
		if(isset($_SESSION['LISTING']['meta']['pedigree_file']) && $_SESSION['LISTING']['meta']['pedigree_file']) {
			@unlink($upload_path_pedigree.$_SESSION['LISTING']['meta']['pedigree_file']);
		}

		$filename = $_FILES['pedigree']['name'];
		$pedigree_file = $upload_path_pedigree.$filename;
		move_uploaded_file($_FILES['pedigree']['tmp_name'], $pedigree_file);
		$_SESSION['LISTING']['meta']['pedigree_file'] = $filename;
		echo json_encode(['url'=>$filename]);
		exit;

	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'image_remove') {
			$index = $_POST['index'];
			$image = $_SESSION['LISTING']['images'][$index];
			@unlink($upload_path_images.$image);
			unset($_SESSION['LISTING']['images'][$index]);
			$_SESSION['LISTING']['images'] = array_values($_SESSION['LISTING']['images']);

		} elseif($_POST['action'] == 'image_sort') {
			$img_arr = [];
			foreach($_POST['images'] as $key=>$image) {
				if(!$image) {
					continue;
				}
				$filename = basename($image);
				$img_arr[] = $filename;
			}
			$_SESSION['LISTING']['images'] = $img_arr;
		}

		exit;
	}

	if($_POST) {

		$form_edit->valid = true;

		if($form_edit->valid) {

			if(isset($_POST['meta'])) {
				foreach($_POST['meta'] as $key=>$val) {
					$_SESSION['LISTING']['meta'][$key] = $val;
				}
			}

			if(isset($_FILES['pedigree_upload'])) {
				if(isset($_SESSION['LISTING']['meta']['pedigree_file']) && $_SESSION['LISTING']['meta']['pedigree_file']) {
					@unlink($upload_path_pedigree.$_SESSION['LISTING']['meta']['pedigree_file']);
				}
				$pedigree_file = $upload_path_pedigree.$_FILES['pedigree_upload']['name'];
				move_uploaded_file($_FILES['pedigree_upload']['tmp_name'], $pedigree_file);
				$_SESSION['LISTING']['meta']['pedigree_file'] = $_FILES['pedigree_upload']['name'];
			}

			if(count($_SESSION['LISTING']['images']) > $photos_allowed) {
				$i = 1;
				foreach($_SESSION['LISTING']['images'] as $key=>$val) {
					if($i > $photos_allowed) {
						@unlink($upload_path_images.$val);
						unset($_SESSION['LISTING']['images'][$key]);
					}
					$i++;
				}
			}

			$url = LINK_listing_new_summary;
			if($is_edit) {
				$url = LINK_listing_edit_summary.$edit_id."/";
			} elseif($is_relist) {
				$url = LINK_listing_relist_summary.$relist_id."/";
			}

			header("Location: ".$zulu->front_link($url));
			exit;
		}

	} else {

		foreach($_SESSION['LISTING'] as $key=>$val) {
			if($key == 'meta') {
				foreach($val as $mkey=>$mval) {
					$_POST['meta'][$mkey] = $mval;
				}
			} else {
				$_POST[$key] = $val;
			}
		}

	}

	$step = 3;
	$max_file_size = $zulu->file_upload_max_size();
	$locations = Location::where('status', 1)->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();

	$url_back = LINK_listing_new_detail;
	if($is_edit) {
		$url_back = LINK_listing_edit.$edit_id."/";
	} elseif($is_relist) {
		$url_back = LINK_listing_relist.$relist_id."/";
	}

	$zulu->template->body_class[] = 'page-new-listing';
    $META_title_item[] = "List Your Horse";

	$zulu->template->jquery_code[] = "
	$('.photo-remove').click(function(e) {
		e.preventDefault();
		let photoCol = $(this).closest('.photo-col'),
			index = photoCol.index();
		$.post('".$zulu->front_link(true)."', {
			action: 'image_remove',
			index: index,
		}, function(data) {
			photoCol.removeClass('active');
			photoCol.find('img').attr('src', '');
			imageReorder();
			initImageSort();
		});
	});
	$('.help-link').click(function(e) {
		e.preventDefault();

	});

	let uppyImages = new Uppy.Core({
		autoProceed: true,
		debug: false,
		restrictions: {
			maxNumberOfFiles: ".$photos_allowed.",
			maxFileSize: ".$max_file_size."
		}
	})
	.use(Uppy.FileInput, {
		target: '.UppyButton',
		locale: {
			strings: {
				chooseFiles: 'Select files or drag them onto me!',
			}
		},
	})
	.use(Uppy.ProgressBar, {
		target: '.UppyImageProgressBar',
		hideAfterFinish: true,
	})
	.use(Uppy.XHRUpload, {
		endpoint: '".$zulu->front_link(true)."',
		fieldName: 'images',
	})
	.use(Uppy.DropTarget, {
		target: '.UppyImages',
	})
	.on('upload-success', (file, response) => {
		const url = response.uploadURL,
			fileName = file.name;
		addImagePreview(url);
	});

	let uppyPedigree = new Uppy.Core({
		autoProceed: true,
		debug: true,
		restrictions: {
			maxNumberOfFiles: 1,
			maxFileSize: ".$max_file_size."
		}
	})
	.use(Uppy.FileInput, {
		target: '.UppyPedigree',
		locale: {
			strings: {
				chooseFiles: 'Click here to Upload',
			}
		},
	})
	.use(Uppy.ProgressBar, {
		target: '.UppyPedigreeProgressBar',
		hideAfterFinish: true,
	})
	.use(Uppy.XHRUpload, {
		endpoint: '".$zulu->front_link(true)."',
		fieldName: 'pedigree',
	})
	.use(Uppy.DropTarget, {
		target: '.UppyPedigree',
	})
	.on('upload-success', (file, response) => {
		const url = response.uploadURL,
			fileName = file.name;
		addPredigreePreview(url);
	});

	$('#pedigree-remove').click(function(e) {
		e.preventDefault();
		$.get('".$zulu->front_link(true)."', {
			action: 'pedigree_remove',
		}, function(data) {
			$('#pedigree-file-info').addClass('hide');
		});
	});

	function addImagePreview(url) {
		$('.photo-col').each(function() {
			let photoBox = $(this).find('.photo-box'),
				img = photoBox.find('img');
			if(img.attr('src') == '') {
				img.attr('src', url);
				$(this).addClass('active');
				initImageSort();
				return false;
			}
		});
	}
	function imageReorder() {
		let images = [];
		$('.photo-col').each(function() {
			let photoBox = $(this).find('.photo-box'),
				img = photoBox.find('img'),
				src = img.attr('src');
			if(src == '') {
				$('.photo-row').append($(this));
			} else {
				images.push(src);
			}
		});
		if(images.length > 0) {
			$.post('".$zulu->front_link(true)."', {
				action: 'image_sort',
				images: images,
			});
		}
	}
	function initImageSort() {
		if($('.photo-row').sortable('instance') != undefined) {
			$('.photo-row').sortable('destroy');
		}
		$('.photo-row').sortable({
	        cancel: '.photo-controls',
			items: '.photo-col.active',
			update: function(event, ui) {
				imageReorder();
			},
		});
	}
	function addPredigreePreview(url) {
		$('#pedigree-file-name').html(url);
		$('#pedigree-file-info').removeClass('hide');
	}
	initImageSort();
	";
	$zulu->template->js_file['uppy'] = "https://releases.transloadit.com/uppy/v2.7.0/uppy.min.js";
	$zulu->template->css_file['uppy'] = "https://releases.transloadit.com/uppy/v2.7.0/uppy.min.css";

	$zulu->template->js_code[] = "

	$(document).ready(function() {
		$('.pedigree-toggle-target').hide();
	});
	$(document).on('click','.pedigree-toggle',function() {
		$('.pedigree-toggle-target').toggle();
		return false;
	});

	";

} elseif(PAGE_file=='new_listing_summary') {

	$new_listing = true;
	$is_edit = $is_relist = false;
	if(isset($_GET['edit'])) {
		$edit_id = $_GET['edit'];
		if($edit_id > 0) {
			$is_edit = true;
			$new_listing = false;
			$product = Products::find($edit_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canEdit()) {
				$zulu->notification_set('You cannot edit that listing.', 2);
				header("Location: ".$zulu->front_link(LINK_account_listings));
				exit;
			}
			if(!isset($_SESSION['LISTING']['edit_id']) || $_SESSION['LISTING']['edit_id'] != $edit_id) {
				header("Location: ".$zulu->front_link($product->feURLEdit()));
				exit;
			}
		}

	} elseif(isset($_GET['relist'])) {
		$relist_id = $_GET['relist'];
		if($relist_id > 0) {
			$is_relist = true;
			$new_listing = false;
			$product = Products::find($relist_id);
			if($product && $product->client_id != $_SESSION['user']['id'] || !$product->canRelist()) {
				$zulu->notification_set('You cannot relist that listing.', 2);
				header("Location: ".$zulu->front_link(LINK_account_listings));
				exit;
			}
			if(!isset($_SESSION['LISTING']['relist_id']) || $_SESSION['LISTING']['relist_id'] != $relist_id) {
				header("Location: ".$zulu->front_link($product->feURLRelist()));
				exit;
			}
		}
	}

	if(!isset($_SESSION['LISTING']) || ($new_listing && isset($_SESSION['LISTING']['edit_id']))) {
		header("Location: ".$zulu->front_link(LINK_listing_new));
		exit;
	}

	$listing_type = $_SESSION['LISTING']['listing_type'];
    $listing_kind = (isset($_SESSION['LISTING']['meta']['listing_kind']) && $_SESSION['LISTING']['meta']['listing_kind'] ? $_SESSION['LISTING']['meta']['listing_kind'] : 'horse');
    $is_marketplace_listing = ($listing_kind == 'marketplace');
	$upload_dir = "file/temp/listing_".session_id()."/";
	$upload_dir_images = $upload_dir."images/";
	$upload_dir_pedigree = $upload_dir."pedigree/";
	$upload_path = MAIN_path.$upload_dir;
	$upload_path_images = MAIN_path.$upload_dir_images;
	$upload_path_pedigree = MAIN_path.$upload_dir_pedigree;
	$feature_options = PriceList::addonArray();
	$payment_row = $class_module->module_data(['class'=>'m_stripe', 'first'=>true]);

	if($_POST) {
		$form_edit->valid = true;

		if(!$is_edit && $form_edit->valid) {
            $module_payment_row = $class_module->module_data(['id'=>$payment_row['id']]);
            require_once $class_module->include_path($module_payment_row['id']);
            $module_payment = new $module_payment_row['class']();
            if(method_exists($module_payment,'verify_payment')) {
                $result = $module_payment->verify_payment();
                if(!$result['success']) {
                    $zulu->notification_set($result['msg'],2);
                    $form_edit->valid = false;
                }
            }
        }

		if($form_edit->valid) {

			//-- pricing
			if($_SESSION['LISTING']['location_id'] > 0) {
				$price_location_id = $_SESSION['LISTING']['location_id'];
			} else {
				$price_location_id = PriceList::$default_location_id;
			}

			$price_location = Location::find($price_location_id);
			$location_currency = $price_location->currency;
			$price_list = $price_location->priceList()->where('code', 'listing')->first();
			$fee = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
			$fee_addon = 0;

			if(!$is_edit) {
				//-- create sale
				$client_id = $_SESSION['user']['id'];
				$client_row = $class_client->client_data(['id'=>$client_id]);
				$sale_data = [
					'name'      =>  $zulu->esc($client_row['name_first']." ".$client_row['name_last']),
					'client_id' =>  $client_id,
					'user_id'   =>  $class_user->authorised->id,
					'date'      =>  $zulu->dateDecode(time()),
					'date_due'  =>  $zulu->dateDecode(strtotime("+7 days")),
					'email'     =>  $client_row['email'],
					'pay_method'=>	$module_payment_row['name_client'],
					'currency_id'	=>	$_SESSION['LISTING']['currency_id'],
					'meta'      =>  [
						'name_first'	=>	addslashes($client_row['name_first']),
						'name_last'		=>	addslashes($client_row['name_last']),
						'web_order'		=>	1,
						'ip_address'	=>	$_SERVER['REMOTE_ADDR'],
						'listing'		=>	1,
						'module_payment'=>	$module_payment_row['id'],
						'currency'		=>	$location_currency->code,
					],
					'line'      =>  [
						[
							'sku'			=>	'listing',
							'object'		=>	'listing',
							'description'	=>	'Listing Fee',
							'quantity'		=>	1,
							'price'			=>	$fee,
						]
					],
				];

				foreach($feature_options as $key=>$feature_data) {
					if($_SESSION['LISTING'][$key]) {
						$price_list = $price_location->priceList()->where('code', $key)->first();
						$price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
						$fee_addon += $price;

						$sale_data['line'][] = [
							'sku'			=>	$key,
							'object'		=>	'listing_addon',
							'description'	=>	$feature_data['title'],
							'quantity'		=>	1,
							'price'			=>	$price,
						];
					}
				}
				$fee_total = $fee + $fee_addon;

				foreach($_POST['meta'] as $key=>$val) {
					$sale_data['meta'][$key] = addslashes($val);
				}

				if(!isset($_SESSION['LISTING']['sale_id']) || $_SESSION['LISTING']['sale_id'] <= 0) {
					$result = $class_sale->sale_generate($sale_data);
					$_SESSION['LISTING']['sale_id'] = $result['id'];

				} else {
					$sale_line = $class_sale->sale_line_data(['sale_id'=>$_SESSION['LISTING']['sale_id'], 'sku'=>'listing', 'first'=>true]);
					if($sale_line['id'] > 0) {
						$sale_data['line'][0]['line_id'] = $sale_line['id'];
					}
					$sale_row = $class_sale->sale_data(['id'=>$_SESSION['LISTING']['sale_id']]);
					$result = $class_sale->sale_edit($sale_row['id'], $sale_data);
				}

				//-- charge stripe
				$result = $module_payment->process_payment($_SESSION['LISTING']['sale_id']);
				if(!$result['success']) {
					$zulu->notification_set($result['msg'], 2);
					header("Location: ".$zulu->front_link(true));
					exit;
				}
			}

			$product_id = 0;
			if(!$new_listing) {
				$product_id = $product->id;
			}

			//-- create product
			$product_data = [
				'location_id'	=>	$_SESSION['LISTING']['location_id'],
				'region_id'		=>	$_SESSION['LISTING']['region_id'],
				'currency_id'	=>	$_SESSION['LISTING']['currency_id'],
				'name'			=>	$_SESSION['LISTING']['name'],
				'spec_age'		=>	$_SESSION['LISTING']['spec_age'],
				'spec_sex'		=>	$_SESSION['LISTING']['spec_sex'],
				'spec_dam'		=>	$_SESSION['LISTING']['spec_dam'],
				'spec_sire'		=>	$_SESSION['LISTING']['spec_sire'],
				'spec_colour'	=>	$_SESSION['LISTING']['spec_colour'],
				'spec_gait'		=>	$_SESSION['LISTING']['spec_gait'],
				'description'	=>	$_SESSION['LISTING']['description'],
				'price'			=>	$_SESSION['LISTING']['price'],
				'is_poa'		=>	$_SESSION['LISTING']['is_poa'] ?? null,
			];
			$product_meta = [
				'pedigree_link'		=>	(isset($_SESSION['LISTING']['meta']['pedigree_link']) ? $_SESSION['LISTING']['meta']['pedigree_link'] : null),
			];

			if(!$is_edit) {
				$product_data['client_id']			= $client_id;
				$product_data['type'] 				= 'product';
				$product_data['listing_type'] 		= $_SESSION['LISTING']['listing_type'];
				$product_data['listing_mode'] 		= (isset($_SESSION['LISTING']['listing_mode']) ? $_SESSION['LISTING']['listing_mode'] : ($_SESSION['LISTING']['listing_type'] == 'classified' ? 'horse_buynow' : 'horse_auction'));
				$product_meta['date_close'] 		= $_SESSION['LISTING']['date_close'];
				$product_meta['date_close_fixed'] 	= $_SESSION['LISTING']['date_close_fixed'];
			}
			if(isset($_SESSION['LISTING']['meta']['marketplace_condition']) && $_SESSION['LISTING']['meta']['marketplace_condition']) {
				$product_data['condition'] = $_SESSION['LISTING']['meta']['marketplace_condition'];
			}
			if(isset($_SESSION['LISTING']['meta']['marketplace_brand']) && $_SESSION['LISTING']['meta']['marketplace_brand']) {
				$product_data['brand'] = $_SESSION['LISTING']['meta']['marketplace_brand'];
			}
			if(isset($_SESSION['LISTING']['meta']['marketplace_year']) && $_SESSION['LISTING']['meta']['marketplace_year']) {
				$product_data['year_manufactured'] = $_SESSION['LISTING']['meta']['marketplace_year'];
			}
			if(isset($_SESSION['LISTING']['meta']['contact_method']) && $_SESSION['LISTING']['meta']['contact_method']) {
				$product_data['contact_method'] = $_SESSION['LISTING']['meta']['contact_method'];
			}
			if(isset($_SESSION['LISTING']['meta']['listing_tier']) && $_SESSION['LISTING']['meta']['listing_tier']) {
				$product_data['listing_tier'] = $_SESSION['LISTING']['meta']['listing_tier'];
			}
			if(isset($_SESSION['LISTING']['meta']['marketplace_category']) && $_SESSION['LISTING']['meta']['marketplace_category']) {
				$marketplace_category_slug = $zulu->esc($_SESSION['LISTING']['meta']['marketplace_category']);
				$marketplace_category_row = $zulu->table_data('marketplace_category', 0, ['first'=>true, 'where'=>["slug = '".$marketplace_category_slug."'"]]);
				if(isset($marketplace_category_row['id']) && $marketplace_category_row['id'] > 0) {
					$product_data['marketplace_cat_id'] = $marketplace_category_row['id'];
				}
			}

			foreach($_SESSION['LISTING']['meta'] as $key=>$val) {
				$product_meta[$key] = $zulu->esc($val);
			}
			$result = $class_product->product_edit($product_id, $product_data, $product_meta);
			$product_id = $result['id'];
			$product = Products::find($product_id);

			//-- files
			$product_dir = MAIN_path."file/product/".$product_id."/";
			if($is_edit) {
				//-- remove all files on product to re-add
				foreach(glob($product_dir."*") as $file) {
					if(is_file($file)) {
						@unlink($file);
					}
				}
				foreach(glob($product_dir."pedigree/*") as $file) {
					if(is_file($file)) {
						@unlink($file);
					}
				}
			}
			@mkdir($product_dir);
			$i = 1;
			foreach($_SESSION['LISTING']['images'] as $image) {
				copy($upload_path_images.$image, $product_dir.$i."-".$image);
				$i++;
			}
			if(isset($_SESSION['LISTING']['meta']['pedigree_file']) && $_SESSION['LISTING']['meta']['pedigree_file']) {
				$product_dir .= "pedigree/";
				@mkdir($product_dir);
				$filename = $_SESSION['LISTING']['meta']['pedigree_file'];
				copy($upload_path_pedigree.$filename, $product_dir.$filename);
			}

			if(!$is_edit) {
				//-- create listing
				$listing = new ProductListing;
				$listing_data = [
					'client_id'		=>	$client_id,
					'product_id'	=>	$product_id,
					'sale_id'		=>	$_SESSION['LISTING']['sale_id'],
					'time_start'	=>	time(),
					'time_close'	=>	$_SESSION['LISTING']['date_close'],
					'time_close_schedule'	=>	$_SESSION['LISTING']['date_close'],
					'fee_listing'	=>	$fee,
					'fee_addon'		=>	$fee_addon,
					'price'			=>	$_SESSION['LISTING']['price'],
					'client_subscribed'		=>	0,
				];
				foreach($feature_options as $key=>$feature_data) {
					$listing_data[$key] = $_SESSION['LISTING'][$key];
				}

			} else {
				//-- update listing
				$listing = $product->listing;
				$listing_data = [
					'price'			=>	$_SESSION['LISTING']['price'],
					'client_subscribed'		=>	0,
				];
			}

			if($listing_type == 'auction') {
				$listing_data['price_reserve'] = $_SESSION['LISTING']['price_reserve'];
				$listing_data['price_bid'] = $_SESSION['LISTING']['price'];
			}

			foreach($listing_data as $key=>$val) {
				$listing->{$key} = $val;
			}
			$listing->save();

			if(!$is_edit) {
				$class_product->product_edit($product_id, ['live'=>1, 'listing_id'=>$listing->id]);
			}

			//-- send notify email
			if($new_listing) {
				$product->listingNewEmail();
				//Job::newJob('listing_parents', 'product', $product_id);

			} elseif($is_relist) {
				$product->listingRelistEmail();
			}

			Products::sessionReset();

			header("Location: ".$zulu->front_link($listing->feURL()));
			exit;
		}

	} else {

		foreach($_SESSION['LISTING'] as $key=>$val) {
			if($key == 'meta') {
				foreach($val as $mkey=>$mval) {
					$_POST['meta'][$mkey] = $mval;
				}
			} else {
				$_POST[$key] = $val;
			}
		}

	}

	$listing_type_title = $listing_type == 'auction' ? 'Auction' : 'Classified';
	$currency = Currency::find($_POST['currency_id']);
	$currency_code = $currency->code;

	if($_POST['location_id'] > 0 && $_POST['region_id'] > 0) {
		$location = Location::find($_POST['location_id']);
		$region = LocationRegion::find($_POST['region_id']);
		$location_name = $region->name.", ".$location->name;
	} else {
		$location_name = $_POST['meta']['location_other'];
	}

	if($_POST['location_id'] > 0) {
		$price_location_id = $_POST['location_id'];
	} else {
		$price_location_id = PriceList::$default_location_id;
	}
	$price_location = Location::find($price_location_id);
	$location_currency = $price_location->currency;
	$price_list = $price_location->priceList()->where('code', 'listing')->first();
	$listing_price_default = $price_list->price;
	$listing_price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
	$total_price = $listing_price;
	$listing_currency_code = $location_currency->code;

	$addons = [];
	foreach($feature_options as $key=>$feature_data) {
		if(isset($_POST[$key]) && $_POST[$key]) {
			$price_list = $price_location->priceList()->where('code', $key)->first();
			$price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
			$feature_data['price'] = "$".$zulu->dollar($price);
			if($price <= 0) {
				$feature_data['price'] = "FREE";
			}
			$addons[$key] = $feature_data;
			$total_price += $price;
		}
	}

	$step = 4;

	$url_back = LINK_listing_new_media;
	if($is_edit) {
		$url_back = LINK_listing_edit_media.$edit_id."/";
	} elseif($is_relist) {
		$url_back = LINK_listing_relist_media.$relist_id."/";
	}

	$zulu->template->body_class[] = 'page-new-listing';
    $META_title_item[] = "List Your Horse";

	if(!$is_edit) {
		require_once $class_module->include_path($payment_row['id']);
		$module_payment = new $payment_row['class']();
	}

}

// ################
// -- Product Review
// ################

if(PAGE_file=='product_review') {

    if(!PRODUCT_reviews_enabled) {
        header("Location: ".$zulu->front_link(FE_rel."browse/"));
        exit;
    }

    $redirect = false;
	if(!isset($_GET['ProductID']) || $_GET['ProductID']=="") {
        $redirect = true;
	} else {
        $pid = $zulu->esc($_GET['ProductID']);
        $product = Products::find($pid);
        if($product == null || $product->hide) {
            $redirect = true;
        }
    }
    if($redirect) {
        $zulu->notification_set("Product is currently unavailable.",2);
		header("Location: ".$zulu->front_link(FE_rel."browse/"));
		exit;
    }

    $prod_title = $product->name;
    $main_link = $product->feURL();
    $reviews_link = $product->feURLReviews();

    if($_POST['action'] == 'submit') {
        $form_edit->valid = true;
        if($form_edit->validate(['rating'])) {
            $form_edit->valid = false;
            $zulu->notification_set("Please select a rating for this product.", 2);
        }
        if($form_edit->validate(['title','content']) && (!$form_edit->validate(['title']) || !$form_edit->validate(['content']))) {
            $form_edit->valid = false;
            $zulu->notification_set("To post a review you must enter both a title and a review.", 2);
        }
        if($form_edit->valid) {
            $approval = $class_setting->data['ws_shop_product_review_approval'];
            $review = ProductReview::where([['user_id',$product->user_id],['product_id',$product->id],['client_id',$_SESSION['user']['id']]])->first();
            if($review == null) {
                $new = true;
                $review = new ProductReview;
                $client = Clients::find($_SESSION['user']['id']);
                if($client != null) {
                    $verified = $client->hasPurchased($product);
                } else {
                    $verified = false;
                }
                $review->user_id    = $product->user_id;
                $review->product_id = $product->id;
                $review->client_id  = $_SESSION['user']['id'];
                $review->verified   = $verified;
                $review->status     = 'live';
            } else {
                $new = false;
                $review_old = $review->replicate();
            }

            $review->rating     = $_POST['rating'];
            $review->title      = $_POST['title'];
            $review->content    = $_POST['content'];
            if($review->title != null && ($new || $review->title != $review_old->title || $review->content != $review_old->content)) {
                $changed = true;
            } else {
                $changed = false;
            }
            if($approval && $changed) {
                $review->status = 'pending';
            }
            $review->save();
            $product->setReviewRating();

            // notify admin
            if($class_setting->data['ws_shop_product_review_notify'] && $changed) {
                $mess = "
                <p>Hi ".$class_setting->data['ws_contact_name'].",</p>
                <p>A review has been ".($new?"placed":"updated")." on the product <b>".$prod_title."</b>.".($approval?" The review is pending your approval before it shows online.":null)."</p>
                <p>
                    Rating: ".$review->rating." / ".ProductReview::$stars."
                    ".($review->title!=null?"
                    <br>Title: ".$review->title."<br>Review: ".$review->content."
                    ":null)."
                </p>
                ".(!$new?"
                <p>
                    <b>Previous Review:</b><br>
                    Rating: ".$review_old->rating." / ".ProductReview::$stars."
                    ".($review_old->title!=null?"
                    <br>Title: ".$review_old->title."<br>Review: ".$review_old->content."
                    ":null)."
                </p>
                ":null)."
                ";
                $to = $class_setting->data['ws_contact_email'];
                $zulu->mail_send($to,"Product Review ".($new?"Placed":"Updated"),$mess,'',false,['client'=>true]);
            }

            $zulu->notification_set("Thanks for submitting your review.", 1);
            header("Location: ".$zulu->front_link($product->feURL()));
            exit;
        }
    }

    $review = ProductReview::where(['product_id'=>$pid, 'client_id'=>$_SESSION['user']['id']])->first();
    if(!$_POST) {
        $_POST['rating'] = $review->rating;
        $_POST['title'] = $review->title;
        $_POST['content'] = $review->content;
    }

    // breadcrumbs
    $categories = $product->categories();
    $breadcrumb = null;
    $breadcrumbs = [];
    if(count($categories) > 0) {
        $breadcrumbs = [$product->breadcrumbLinks(true)];
    }
    $breadcrumbs[] = $product->breadcrumbLink(true);
    $breadcrumbs[] = $product->reviewsBreadcrumbLink(true);
    $breadcrumbs[] = $product->reviewBreadcrumbLink(false);
    $breadcrumb = Products::breadcrumbsBuild($breadcrumbs, true);

    $META_title_item = ["Write a Review - ".$prod_title];

    $zulu->template->jquery_code[] = "
    $('.review-write .review-stars .review-star').mouseover(function() {
        setReviewStars($(this));
    });
    $('.review-write .review-stars .review-star').mouseout(function() {
        clearReviewStars();
    });
    $('.review-write .review-stars .review-star').click(function() {
        $('input[name=\"rating\"]').val($(this).data('rating'));
        $('.review-write .review-stars .review-star.selected').removeClass('selected');
        $('.review-write .review-stars .review-star .fa-star').removeClass('fas');
        $(this).addClass('selected');
        setReviewStars($(this));
    });
    $('.review-write .review-stars').mouseout(function() {
        if($('.review-write .review-stars .review-star.selected').length) {
            $('.review-write .review-stars .review-star.selected').trigger('click');
        }
    });
    function clearReviewStars() {
        $('.review-write .review-stars .review-star .fa-star').removeClass('fas');
        $('.review-write .review-stars .review-star .fa-star').addClass('far');
    }
    function setReviewStars(element) {
        clearReviewStars();
        element.find('.fa-star').removeClass('far');
        element.find('.fa-star').addClass('fas');
        element.prevAll('.review-star').find('.fa-star').removeClass('far');
        element.prevAll('.review-star').find('.fa-star').addClass('fas');
    }
    ";

}

// ################
// -- Product Reviews
// ################

if(PAGE_file=='product_reviews') {

    if(!PRODUCT_reviews_enabled) {
        header("Location: ".$zulu->front_link(FE_rel."browse/"));
        exit;
    }

    $redirect = false;
	if(!isset($_GET['ProductID']) || $_GET['ProductID']=="") {
        $redirect = true;
	} else {
        $pid = $zulu->esc($_GET['ProductID']);
        $product = Products::find($pid);
        if($product == null || $product->hide) {
            $redirect = true;
        }
    }
    if($redirect) {
        $zulu->notification_set("Product is currently unavailable.",2);
		header("Location: ".$zulu->front_link(FE_rel."browse/"));
		exit;
    }

    $prod_title = $product->name;
    $main_link = $product->feURL();
    $reviews_link = $product->feURLReviews();
    $review_link = $product->feURLReview();

    $review_summary = $product->reviewSummary();
    if($review_summary != null) {
        $has_rating = true;
        $reviews = $product->reviews()->where([['status','live'],['title','!=','']])->orderBy('feature','DESC')->orderBy('verified','DESC')->orderBy('id','DESC')->get();
        if(count($reviews) > 0) {
            $has_reviews = true;
        } else {
            $has_reviews = false;
        }
    } else {
        $has_rating = false;
    }

    // breadcrumbs
    $categories = $product->categories();
    $breadcrumb = null;
    $breadcrumbs = [];
    if(count($categories) > 0) {
        $breadcrumbs = [$product->breadcrumbLinks(true)];
    }
    $breadcrumbs[] = $product->breadcrumbLink(true);
    $breadcrumbs[] = $product->reviewsBreadcrumbLink(false);
    $breadcrumb = Products::breadcrumbsBuild($breadcrumbs, true);

    $META_title_item = [$prod_title." Reviews"];

}

// ################
// -- Post search
// ################

if(PAGE_file=='post_search') {
	$search_string = trim($_GET['search_string']);

	if(strlen($search_string)){
		//If types to search
		$types_to_search = [];
		if(strlen(trim($_POST['post_types']))){
			$types_to_search = explode(',', $types_to_search);
		}
		//Pageination
		if($_GET['Pg'] <= 0){
			$_GET['Pg'] = 1;
		}
		$page_max = 5;
		$page_max_page = 6;
		$limit_start = ($_GET['Pg'] == 1?0:($page_max*($_GET['Pg']-1)));
		$limit_end = ($limit_start+$page_max);

		$return = $class_post->post_search($search_string ,$types_to_search, $limit_start, $page_max);
		$search_results = $return['posts'];

		$pagination = $zulu->pagination($_GET['Pg'],['page_max_page'=>$page_max_page,'page_max'=>$page_max,'count'=>$return['count_all'],'link'=>$zulu->front_link(true,['self'=>true,'filter'=>['category','Pg']])]);

		//$zulu->notification_set("Search results for '".$search_string."'.", 1);
	}else{
		$zulu->notification_set("Please enter a search term.",2);
	}
}

// ###################
// -- Membership Switch
// ###################

if(PAGE_file=='form_post') {
	$slug = $db->escape_string($_GET['slug']);
	$user_token = $db->escape_string($_GET['user']);

	$user_data = $class_user->user_data(['token'=>$user_token]);
	$user_id = $user_data['id'];

	$class_setting->construct(['user_id'=>$user_id,'cache_clear'=>true]);

	$form = new form_post(['slug'=>$slug,'user_id'=>$user_id]);
	$form_id = $zulu->vars->form_post->form_id;

	if($form_id<=0) {
		$zulu->notification_set("This form does not exist.",2);
	}

	//-- Submit Form
	if($_POST['action']=='form_post_submit') {
		$form->form_process();
	}

	//-- Build Form
	if($form_id>0) {
		$zulu->template->body = $class_website->form_build();
	}
}

// ###################
// -- Members Index
// ###################

if(PAGE_file=='member') {
	$client_meta = $zulu->meta_array($class_client->client_meta($_SESSION['user']['id']));

	$recently_viewed = false;
	if(isset($_SESSION['RECENT_VIEWED']) && count($_SESSION['RECENT_VIEWED']) > 0) {
		$recently_viewed = true;
		$reversed = array_reverse($_SESSION['RECENT_VIEWED']);
		$max_to_show = 4;
		$product_blocks = [];
		foreach($reversed as $product_id) {
			if(!isset($product_blocks[$product_id])) {
				$product_blocks[$product_id] = $class_product->product_block($product_id);
			}
			if(count($product_blocks) >= $max_to_show) {
				break;
			}
		}
	}

	//Meta
	$META_title_item[] = "My Account";
	$zulu->template->body_class[] = 'page-members';

	//Greet
    $greet = "Good Morning";
    if(date('H')>12) {
        $greet = "Good Afternoon";
    }
    if(date('H')>17) {
        $greet = "Good Evening";
    }
}

// ###################
// -- Wishlist
// ###################

if(PAGE_file=='wishlist') {

    if(!$WISHLIST_enabled) {
        header("Location: ".FE_rel."members/");
        exit;
    }

	if($_POST['action'] == 'remove_item' && $_POST['product_id'] > 0 && $_SESSION['user']['id']>0){
		$pid = $db->escape_string($_POST['product_id']);
		$class_wishlist->wishlist_delete($pid, 'product_id', $_SESSION['user']['id']);
		$zulu->notification_set("Product removed from Wish List.",1);
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}

	$wishlist_data = $class_wishlist->wishlist_data(['client_id'=>$_SESSION['user']['id']]);
	$wishlist_count = count($wishlist_data);
	foreach($wishlist_data as $item) {
		$product_data = $class_product->product_data(['id'=>$item['product_id']]);
		$url = $class_product->product_url($product_data['id'],$slug,true);
		$image_data = $class_product->image_data($product_data['id']);
		$image_main = $zulu->thumb($image_data['main'],'w=250&h=150&far=1&bg=ffffff');
		$image_main_big = MAIN_rel.$image_data['main'];
		if($image_data['main']==NULL) {
			$image_main = $zulu->thumb(FE_crm.FE_path.FE_tpl."images/placeholder.png",'w=250&h=150&far=1&bg=ffffff');
		}
		$image_main = $zulu->path_clean($image_main);
		$link = "<a href='".$url."'>".$product_data['name']."</a>";
		$image_html = "<img alt='Image of ".$product_data['name']."' src='".$image_main."' />";
		$price_data = $class_product->price($product_data['id']);
		/*$line_table .= "
		<tr>
			<td>".$image_html."</td>
			<td>".$link."</td>
			<td>".($price_data['special']?"<span class=\"price-highlight\"><b>".($price_data['price_count']>1?"From ":NULL)."</b> ":NULL).LOCALE_currency.$zulu->dollar($price_data['price'],true).($price_data['special']?"</span><br><s>".LOCALE_currency.$zulu->dollar($price_data['rrp'],true)."</s>":NULL)."</td>
			<td align='right'><button type='submit' name='product_id' value='".$item['product_id']."'><i class='far fa-trash-alt' aria-hidden='true'></i> Remove</button></td>
		</tr>";*/
		$line_table .= "
		<div class=\"coltable col4 wishlist vmiddle\">
			<div class='col image'>".$image_html."</div>
			<div class='col title'>".$link."</div>
			<div class='col price'>".($price_data['special']?"<span class=\"price-highlight\"><b>".($price_data['price_count']>1?"From ":NULL)."</b> ":NULL).LOCALE_currency.$zulu->dollar($price_data['price'],true).($price_data['special']?"</span><br><s>".LOCALE_currency.$zulu->dollar($price_data['rrp'],true)."</s>":NULL)."</div>
			<div class='col remove text-right'><button type='submit' name='product_id' value='".$item['product_id']."'><i class='far fa-trash-alt' aria-hidden='true'></i> Remove</button></div>
		</div>
		";
	}
	//$line_table = "<table class=\"grid-table\"><tbody>".$line_table."</tbody></table>";

	//Meta
	$META_title_item[] = "Wish List";
	//BC
    $zulu->template->breadcrumb[] = ['label'=>'Wish List'];

}

// ###################
// -- Order
// ###################

if(PAGE_file=='order') {

	$line_table = null;
	$sale_config = [
		'client_id'			=>	$_SESSION['user']['id'],
		'status_exclude'	=>	'0',
	];

	//-- pagination
	$limit = MAX_per_page;
	$page = !isset($_GET['Pg']) || !$_GET['Pg'] ? 1 : $_GET['Pg'];
	$start = $page > 1 ? $limit * ($page - 1) : 0;
	$sale_data_all = $class_sale->sale_data($sale_config);
	$total_count = count($sale_data_all);

	if($total_count > 0) {
		$sale_config['row_limit'] = $limit;
		$sale_config['row_start'] = $start;
		$sale_row = $class_sale->sale_data($sale_config);
		$sale_count = count($sale_row);
		foreach($sale_row as $data) {
			$sale_id = $data['id'];
			$status_data = $class_sale->sale_status_info($data['status'],array('id'=>$data['id']));
			$sale_total = $class_sale->sale_total($sale_id);
			$sale_status = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';
			$line_total = $class_sale->sale_line_count($sale_id);
			$currency_code = $class_sale->currencyCode($sale_id);

			$line_table .= "<tr>
				<td>".$zulu->dateTimezone($data['date'], 'jS M Y')."</td>
				<td>#".$data['reference']."</td>
				<td>$".$sale_total." ".$currency_code."</td>
				<td>".$sale_status."</td>
				<td align='right'><a href=\"".FE_rel."members/order_view.php?token=".$data['token']."\" class=\"button\">View Invoice</a></td>
			</tr>";
		}
		$line_table = "<table class=\"grid-table\">
			<thead>
				<tr><td>Date</td><td>Reference</td><td>Total</td><td>Status</td><td></td></tr>
			</thead>
			<tbody>".$line_table."</tbody>
		</table>";
	}

	//-- pagination
	$pagination = $zulu->pagination($page, ['count'=>$total_count, 'link'=>$zulu->front_link(true, ['self'=>true, 'filter'=>['Pg']]), 'page_max_page'=>$limit]);

	//Meta
	$META_title_item[] = "Billing Summary";
	//BC
    $zulu->template->breadcrumb[] = ['label'=>'Billing Summary'];

}

// ###################
// -- Order View
// ###################

if(PAGE_file=='order_view') {

	//Vars
	$token = $db->escape_string($_GET['token']);
	if($token==NULL) $token = $db->escape_string($_SESSION['SALE_TOKEN']);
    if($_GET['token_o']!=NULL) $token = $db->escape_string($_GET['token_o']);

    if($token == NULL) {
        $ipn_result = $class_module->ipn_check();
        if($ipn_result['success']) {
            $reset_config = [];
            $sale_data = $class_sale->sale_data(['id'=>$ipn_result['sale_id']]);
            if($sale_data['client_id'] > 0) {
                $reset_config = ['client_id'=>$sale_data['client_id']];
            }
            checkout_reset($reset_config);
            echo "IPN SUCCESS";
            exit;
        }
    }

	$sale_data = $class_sale->sale_data(['token'=>$token]);
	$sale_id = $sale_data['id'];
	$sale_ref = $sale_data['reference'];
	$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_id));

	if($sale_data['email'] == $_GET['Email']) {
		$_SESSION['ORDER_VIEW'][$sale_data['token']] = true;
	}

	if($sale_meta['module_payment'] > 0) {	// Load payment module
		$module_payment_row = $class_module->module_data(['id'=>$sale_meta['module_payment']]);
		require_once $class_module->include_path($module_payment_row['id']);
		$module_payment = new $module_payment_row['class']();
	}

	if($_POST['action'] == 'manual_payment_form') {
		$manual_payment_die = false;
		if(method_exists($module_payment,'verify_payment')) {
			$result = $module_payment->verify_payment();
			if(!$result['success']) {
				$zulu->notification_set($result['msg'],2);
				$manual_payment_die = true;
			}
		}
		if(!$manual_payment_die) {
			if($module_payment->online) $_SESSION['Pay_Now'] = true;
		}
	}

	if($sale_meta['module_payment'] > 0) {
		$result = $module_payment->validate_payment();	// validate payment
		if($result['success']) {
			if($token == $_SESSION['CHECKOUT']['TOKEN']) checkout_reset();
			$zulu->notification_set("Your order has been paid successfully.",1);
			header("Location: ".FE_rel."members/order_view.php?token=".$result['token']);
			exit;
		} elseif($result['msg'] != NULL) {
			$zulu->notification_set($result['msg'],2);
            if($result['token'] != NULL) {
                header("Location: ".FE_rel."members/order_view.php?token=".$result['token']);
                exit;
            }
		}
	}

	if($sale_data['client_id']!=$_SESSION['user']['id'] && !$_SESSION['ORDER_VIEW'][$sale_data['token']]) {
		$zulu->notification_set("Sorry, you do not have permission to view this order.",2);
		header("Location: ".FE_rel."members/order.php");
		exit;
	}

	$sale_line = $class_sale->sale_line($sale_id);
    if($sale_data['client_id'] > 0) {
        $user_data = $class_client->client_data(array('id'=>$sale_data['client_id']));
    }
	$sale_balance = $class_sale->sale_balance($sale_id);
	$sale_total = $class_sale->sale_total($sale_id);
	$sale_discount = $class_sale->sale_total_discount($sale_id);
	$currency_code = $class_sale->currencyCode($sale_id);

	if($_GET['Action'] == 'Pay' || $_SESSION['Pay_Now'] || $_GET['Action'] == 'ManualPay') {
		unset($_SESSION['Pay_Now']);

		// Run process payment function
		$_SESSION['SALE_TOKEN'] = $token;
		if($_GET['Action'] == 'ManualPay' && $_GET['Module'] != NULL) {
			$module_payment_row = $class_module->module_data(['token'=>$db->escape_string($_GET['Module'])]);
			if($module_payment_row['id'] > 0) {
				require_once $class_module->include_path($module_payment_row['id']);
				$module_payment = new $module_payment_row['class']();
				$class_sale->sale_edit($sale_id,['pay_method'=>$module_payment_row['name_client'],'meta'=>['module_payment'=>$module_payment_row['id']]]);
			}
		}
		$result = $module_payment->process_payment($sale_id,['amount'=>$zulu->dollar($sale_balance)]);
		if($result['success']) {
			checkout_reset();
		}
		if($result['msg'] != NULL) {
			$zulu->notification_set($result['msg'],($result['success']?1:2));
		}
		header("Location: ".$zulu->front_link(true, ['query'=>['token'=>$token]]));
		exit;
	}

	if($_GET['Action'] == 'ManualPayForm') {
		if($sale_balance <= 0) {
			$zulu->notification_set("This order has already been paid in full.",2);
			header("Location: ".$zulu->front_link(true,['self'=>true,'filter'=>['Action','Module']]));
			exit;
		}
		if($_GET['Module'] != NULL) {
			$module_payment_row = $class_module->module_data(['token'=>$db->escape_string($_GET['Module'])]);
			if($module_payment_row['id'] > 0) {
				require_once $class_module->include_path($module_payment_row['id']);
				$module_payment = new $module_payment_row['class']();
				$class_sale->sale_edit($sale_id,['pay_method'=>$module_payment_row['name_client'],'meta'=>['module_payment'=>$module_payment_row['id']]]);
			}
		}
		if(method_exists($module_payment,'form_payment')) $manual_payment_form = true;
	}

	if($_GET['co']==1) { //-- send mail and redir to same page w/o CO
		$class_sale->sale_receipt_mail($sale_id);
		$class_sale->sale_receipt_mail($sale_id,['admin'=>true]);
		checkout_reset();

		$_SESSION['zl_sale_conversion']['complete'] = true;
		$_SESSION['zl_sale_conversion']['complete_value'] = $class_sale->sale_total($sale_id);

		header("Location: order_view.php?token=".$token);
		exit;
	}

	$status_data = $class_sale->sale_status_info($sale_data['status'],array('id'=>$sale_data['id']));
	$sale_status = '<span class="'.$status_data['class'].'"><span class="fas '.$status_data['icon'].'"></span> '.$status_data['label'].'</span>';

	$sale_payment = $sale_data['pay_method'];
	if($sale_meta['ship_method']) {
		$sale_shipping = $sale_meta['ship_method'];
	}

	foreach($sale_line as $line) {
		$prod = $class_product->product_data(array('id'=>$line['product_id']));
		$prod_data = $prod;
		$prod_meta = $zulu->meta_array($class_product->product_meta($prod_data['id']));

		$title = $line['description'];
		if($prod_data['id']>0 && $prod_data['id']!=$class_product->ticket_id) {
			$title = "<a target=\"_blank\" href=\"".$class_product->product_url($prod_data['id'],$prod_data['slug'],true)."\">{$title}</a>";
		}

		//Digital?
		if(!isset($prod_meta['digital'])||$prod_meta['digital']=='0') {
			$is_tangible = true;
		}

		$sale_line_sub = $class_sale->sale_line_sub($line);
		if(trim($sale_line_sub)!=NULL) {
			$sale_line_sub = "<br><small class=\"opt opt-grey\">".$sale_line_sub."</small>";
		}

		$line_table .= "<tr><td>".$title.$sale_line_sub."</td><td align='center'>".number_format($line['quantity'],0)."</td><td align='right'>$".$line['price']."</td><td align='right'>$".$line['total']."</td></tr>";

	}
	$line_table = "<table class=\"grid-table\"><thead><tr><td>Product</td><td align='center'>Quantity</td><td align='right'>Price</td><td align='right'>Subtotal</td></tr></thead><tbody>".$line_table."</tbody></table>";

	$taxttlinfo = "includes ".$class_setting->data['tax_label']." of";

	$cart_summary = $class_sale->payment_summary($sale_total,['sale_id'=>$sale_id,'placed'=>true]);

	if($sale_data['coupon_id'] > 0) {
		$coupon_data = $class_sale->coupon_data(['id'=>$sale_data['coupon_id']]);
		$coupon_code = $coupon_data['code'];
	}

	//-- User info
	$bill_arr = [
		$sale_meta['bill_to'],
		$sale_meta['bill_address'],
		$sale_meta['bill_suburb'],
		$sale_meta['bill_city']." ".$sale_meta['bill_post'],
		$sale_meta['bill_country'],
	];
	$ship_arr = [
		$sale_meta['ship_to'],
		$sale_meta['ship_address'],
		$sale_meta['ship_suburb'],
		$sale_meta['ship_city']." ".$sale_meta['ship_post'],
		$sale_meta['ship_country'],
	];
	$bill = $zulu->compile("<br>",$bill_arr);
	$ship = $zulu->compile("<br>",$ship_arr);

	if($_SESSION['ORDER_COMPLETE']) {
		$show_fb = true;
	}

	//Order updates
	/*$order_updates_html_table = '<table class="grid-table"><thead><tr><td>Date</td><td>Information</td></tr></thead><tbody>';
	$order_updates = $zulu->log_data(['object'=>'sale','object_id'=>$sale_id]);

	if(count($order_updates)>0){
		foreach($order_updates as $order_update) {
			$order_updates_html_table .= '<tr><td>'.$zulu->time_fancy($order_update['stat_add']).'</td><td><strong>'.$order_update['title'].'</strong> '.$order_update['data'].'</td></tr>';
		}
	}else{
		$order_updates_html_table .= '<tr><td>No updates to view. Please check back later.</td><td></td></tr>';
	}
	$order_updates_html_table .= '</tbody></table>';*/

	//Support tickets
    /*if($SUPPORT_enabled) {
        $support_tickets_html_table = '<table class="grid-table"><thead><tr><td>Date</td><td>Subject</td><td>Status</td></tr></thead><tbody>';
        $client_id = $_SESSION['user']['id'];
        $ticket_sql_config['client_id'] = $client_id;
        $ticket_sql_config['object_id'] = $sale_id;
        $ticket_sql_config['object'] = 'sale';
        $ticket_data_row = $class_support->support_data($ticket_sql_config);
        if(count($ticket_data_row)>0){
            foreach($ticket_data_row as $ticket){
                $status_indicator = 'success';
                switch ($ticket['status']) {
                    case 'Open':
                        $status_indicator = 'success';
                    break;
                    case 'Hold':
                        $status_indicator = 'warning';
                    break;
                    case 'Closed':
                        $status_indicator = 'danger';
                    break;
                }
                $has_new = $class_support->support_unread($ticket['id'],false);
                $support_tickets_html_table .= '<tr><td> '.$zulu->time_fancy($ticket['stat_add']).'</td><td><a href="'.$zulu->front_link(FE_rel."members/support.php",array('query'=>array('id'=>$ticket['id']))).'"'.($ticket['id'] == $_GET['id'] ? "active": "").'">'.$ticket['subject'].'</a></td><td><span class="pull-right opt opt-fill opt-'.$status_indicator.'"> '.$ticket['status'].'</span>'.($has_new?'<span class=" text-mini" style="padding-top:3px"><span class="opt opt-warning opt-fill"><i class="fas fa-envelope"></i> NEW</span>':NULL).'</span></span></td></tr>';
            }
        } else {
            $support_tickets_html_table .= '<tr><td>No tickets to view.</td><td></td><td></td></tr>';
        }
        $support_tickets_html_table .= '</tbody></table>';
    }*/

	//-- Google Tag Mgr
	if($_SESSION['zl_sale_conversion']['complete']&&$setting['ws_module_google_gatag_profile']!=NULL&&$setting['ws_module_google_gatag_conv_checkout']!=NULL) {
		$zulu->template->website_html['head_close'] .= "
		<!-- ZULU - Purchase Action -->
		<script>
		gtag('event', 'conversion', {
		  'send_to': '".$setting['ws_module_google_gatag_profile']."/".$setting['ws_module_google_gatag_conv_checkout']."',
		  'value': ".$_SESSION['zl_sale_conversion']['complete_value'].",
		  'currency': '".$setting['currency_code']."'
		});
		</script>
		";
		unset($_SESSION['zl_sale_conversion']);
	}

	//-- Meta
	$META_title_item[] = "Invoice Details";
	//BC
    $zulu->template->breadcrumb[] = ['link'=>'order.php','label'=>'Billing Summary'];
    $zulu->template->breadcrumb[] = ['label'=>'Invoice Details'];

}

// ###################
// -- Basket Actions
// ###################

if(PAGE_file=='basket') {

	//User info
	if($_SESSION['user']['id']>0) {
		$user_info = $class_client->client_data(['id'=>$_SESSION['user']['id']]);
		$user_meta = $zulu->meta_array($class_client->client_meta($_SESSION['user']['id']));

		if(!$_POST) {
			foreach($user_meta as $key=>$val) {
				$_POST[$key] = $val;
			}
			foreach($user_info as $key=>$val) {
				$_POST[$key] = $val;
			}
		}
	}

    if(isset($_GET['Action'])) {

        if($_GET['Action'] == "Empty") {
            checkout_reset();
            $zulu->notification_set("Your basket has been emptied.",1);

        } elseif($_GET['Action'] == "Remove") {
            if(isset($_GET['BasketID'])) {
                $bid = $_GET['BasketID'];
                $cart_data = $class_sale->cart_data(array('id'=>$bid));
                if(($_SESSION['user']['id'] > 0 && $cart_data['client_id'] == $_SESSION['user']['id']) || $cart_data['session'] == session_id()) {
                    $class_sale->cart_delete($bid);
                    if($cart_data['sale_line_id'] > 0) {
                        $class_sale->sale_line_delete($cart_data['sale_line_id']);
                    }
                } else {
                    $zulu->notification_set("Unable to remove the product from your basket. Please try again.",2);
                }
                $cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id()));
            }
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;

        } elseif($_GET['Action'] == 'QtyAdjust') {
            $newqty = $db->escape_string($_GET['qty']);
            $cart_id = $db->escape_string($_GET['id']);
            $cart_data = $class_sale->cart_data(['id'=>$cart_id]);
            $all_product_data = $class_sale->cart_data(['product_id'=>$cart_data['product_id'],'client_id'=>$_SESSION['user']['id'],'session'=>session_id()]);

            $qty_ovr = 0;
            foreach($all_product_data as $apd) {
                if($apd['id']==$cart_id) {
                    continue;
                }
                $qty_ovr += $apd['quantity'];
            }
            if($cart_data['id']<=0) {
                $zulu->notification_set("No basket item was specified.",2);
                $die = true;
            }
            if(!is_numeric($newqty)) {
                $zulu->notification_set("No quantity specified.",2);
                $die = true;
            }

            $product_meta = $zulu->meta_array($class_product->product_meta($cart_data['product_id']));
            if(isset($setting['ws_shop_stock_backorder'])&&$setting['ws_shop_stock_backorder']==0&&$product_meta['stock']<($newqty+$qty_ovr)) {
                $zulu->notification_set("This product isn't currently available in the quantity requested. Please contact us to order additional quantities.",2);
                $die = true;
            }
            if(!$die) {
                if($newqty<=0) {
                    $class_sale->cart_delete($cart_id);
                }
                $class_sale->cart_edit($cart_id,['quantity'=>$newqty]);
                if($cart_data['sale_line_id'] > 0) {
                    $line_row = $class_sale->sale_line_data(['id'=>$cart_data['sale_line_id']]);
                    $class_sale->sale_line_edit($line_row['id'],['quantity'=>$newqty,'price'=>$cart_data['price'],'discount'=>$line_row['discount'],'extra'=>$line_row['extra']]);
                }
            }

        } elseif($_GET['Action'] == "Unlink") {
            if($_GET['Cart'] > 0 && $_GET['id'] > 0) {
                $cart_id = $db->escape_string($_GET['Cart']);
                $renew_id = $db->escape_string($_GET['id']);
                $method = $db->escape_string($_GET['Method']);
                $cart_data = $class_sale->cart_data(array('id'=>$cart_id));
                $custom = unserialize($cart_data['custom']);
                unset($custom[$method.'_id'][$renew_id]);
                $class_sale->cart_edit($cart_id,['custom'=>serialize($custom)]);
                exit;
            }

        } elseif($_GET['Action'] == "Link") {
            if($_GET['Cart'] > 0 && $_GET['id'] > 0) {
                $cart_id = $db->escape_string($_GET['Cart']);
                $renew_id = $db->escape_string($_GET['id']);
                $method = $db->escape_string($_GET['Method']);
                $cart_data = $class_sale->cart_data(array('id'=>$cart_id));
                $custom = unserialize($cart_data['custom']);
                $custom[$method.'_id'][$renew_id] = $renew_id;
                $class_sale->cart_edit($cart_id,['custom'=>serialize($custom)]);
                exit;
            }

        }

        header("Location: ".$zulu->front_link(LINK_basket));
        exit;

    } elseif(isset($_GET['Do'])) {
        if($_GET['Do'] == "UnlinkCoupon") {
            unset($_SESSION['CHECKOUT']['COUPON']);
            exit;

        } elseif($_GET['Do'] == "Coupon") {
            $code = $db->escape_string($_GET['Code']);
            $cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'sort'=>'title ASC'));
            foreach($cart_data as $cart_row) {
                $custom = unserialize($cart_row['custom']);
                if(count($custom['ticket_temp']) > 0) {
                    foreach($custom['ticket_temp'] as $ticket_temp_id) {
                        $temp_row = $class_book->event_ticket_temp_data(['id'=>$ticket_temp_id]);
                        $ticket_row = $class_book->event_date_ticket_data(['id'=>$temp_row['event_ticket_id']]);
                        $prod_ids[$ticket_row['ticket_type_id']]['quan'] += $cart_row['quantity'];
                        $prod_ids[$ticket_row['ticket_type_id']]['object'] = 'event_ticket';
                    }
                } else {
                    $prod_ids[$cart_row['product_id']]['quan'] += $cart_row['quantity'];
                    $prod_ids[$cart_row['product_id']]['object'] = 'product';
                }
            }
            $check = $class_sale->coupon_check($code,0,$_SESSION['user']['id'],['object_check'=>$prod_ids]);
            if($check['success']) {
                $_SESSION['CHECKOUT']['COUPON'] = $code;
            } else {
                unset($_SESSION['CHECKOUT']['COUPON']);
            }
            echo $check['err']."|".$check['err_class'];
            exit;

        } elseif($_GET['Do'] == 'RefreshCart') {
            $result = checkout_summary(['simple'=>true]);
            echo json_encode(['success'=>($result['html']?true:false),'html'=>$result['html']]);
            exit;

        } elseif($_GET['Do'] == 'PaymentMethod') {
            $result = checkout_summary(['simple'=>true]);
            echo $result['total'];
            exit;

        }

        exit;
    }

	//Clear ABC
	$class_cache->dump('abc_token');
	$class_cache->dump('abc_initial');
	$class_cache->dump('abc_initial_end');

	//-- Load Checkout Summary
    cart_update();
	$checkout_summary = checkout_summary();
	$cart_count = $checkout_summary['cart_count'];
	$is_tangible = $checkout_summary['is_tangible'];
	$product_HTML = $checkout_summary['html'];

    if(isset($_SESSION['CHECKOUT']['TOKEN']) && $_SESSION['CHECKOUT']['TOKEN'] != NULL) {
		$sale_row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN']]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_row['id']));
        $zulu->meta_update('sale',$sale_row['id'],'checkout_step','0');
        unset($_SESSION['CHECKOUT']['STEP']);
	}

	if($_SESSION['Product_Added'] > 0) {
		$prod_data = $class_product->product_data(array('id'=>$_SESSION['Product_Added']));
		$cat_data = $class_product->product_data(array('id'=>$prod_data['parent_id']));
		$show_fb = true;
		unset($_SESSION['Product_Added']);
	}

    $show_related = false;
    if(strstr($class_setting->data['ws_shop_related_products'],'basket')) {
        $related_product_html = related_product_html(0, ['basket'=>true]);
        if($related_product_html != NULL) {
            $show_related = true;
        }
    }

    //Vars
	$META_title_item[] = "My Basket";
	$zulu->template->body_class[] = 'page-checkout';
	$zulu->template->body_class[] = 'page-basket';

}

// ###################
// -- Product Actions
// ###################

if(PAGE_file=='product') {

	//-- In shop mode or enquiry mode?
	$shop_mode = $class_website->shop_mode();

	//Add to Cart
	/*if($_POST['action']=="cartadd"||$_GET['Action']=="AddCart") {

		//Bulk Table Actions
		if(count($_POST['bulk'])>0) {
			$add_count = 0;
			foreach($_POST['bulk'] as $product=>$quan) {
				if(!is_numeric($quan)&&trim($quan)!=NULL) {
					$error_log[] = $class_product->name($product)." invalid number entered.";
				}
				if($quan<=0||!is_numeric($quan)) {
					continue;
				}
				$all_product_data = $class_sale->cart_data(['product_id'=>$product,'client_id'=>$_SESSION['user']['id'],'session'=>session_id()]);
				$qty_ovr = 0;
				foreach($all_product_data as $apd) {
					$qty_ovr += $apd['quantity'];
				}
				$product_meta = $zulu->meta_array($class_product->product_meta($product));
				if(isset($setting['ws_shop_stock_backorder'])&&$setting['ws_shop_stock_backorder']==0&&$product_meta['stock']<($quan+$qty_ovr)) {
					$zulu->notification_set("This product isn't currently available in the quantity requested. Please contact us to order additional quantities.",2);
					if($product_meta['stock']>0) {
						$error_log[] = $class_product->name($product)." has only ".$product_meta['stock']." in stock, you requested ".($quan+$qty_ovr)." in your order.";
					} else {
						$error_log[] = $class_product->name($product)." is out of stock.";
					}
					continue;
				}
				$add = $class_sale->cart_add(['product'=>$product,'qty'=>$quan]);
				$add_count++;
			}

			if(count($error_log)>0) {
				$zulu->notification_set("Some errors occurred:<br>".implode("<br>",$error_log),2);
			} else {
				$zulu->notification_set("The selected products were added to your basket. ".$suffix,1);
				$_SESSION['Product_Added'] = $pid;
			}
			if($add_count>0) {
				if($_GET['Return']=='basket_popdown') {
					header("Location: ".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'popup_basket']]));
					exit;
				}
				if($_POST['js']>0) {
					exit;
				}
				if($_GET['QuickCO']>0) {
					header("Location: ".FE_rel."checkout/index/");
				} else {
					header("Location: ".FE_rel."checkout/basket/");
				}
				exit;
			} else {
				if($_POST['js']>0) {
					exit;
				}
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}
			if($_POST['js']>0) {
				exit;
			}
		}
		//-- Other Actions Follow Separate

		$pid = ($_POST['pid']>0?$_POST['pid']:$_GET['ProductID']);
		$quan = ($_POST['quantity']>0?$_POST['quantity']:($_GET['Quantity']>0?$_GET['Quantity']:1));

		$post = ['product'=>$pid,'qty'=>$quan];

		//Renew
		if($_GET['Renew']!=NULL) {
			$post['renew'] = $_GET['Renew'];
		}

		//Switch
		if($_GET['Switch']!=NULL) {
			$post['switch'] = $_GET['Switch'];
		}

		//Credit
		if($_GET['Credit']!=NULL) {
			$post['credit'] = $_GET['Credit'];
		}

		//Gift
		if($_POST['gift']!=NULL) {
			$post['gift'] = true;
		}

		//Attributes
		if($_POST['attribute']!=NULL) {
			$post['attribute'] = $_POST['attribute'];
		}

		//Product ID specified
		if($post['product']<=0) {
			$zulu->notification_set("No product was specified.",2);
			header("Location: ".$_SERVER['HTTP_REFERER']);
			exit;
		}

		//--Check Stock
		if(isset($post['attribute'])) {
			$data_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>$post['attribute']]);
			$pid = $data_option[0];
			if($data_option[0]<=0) {
				$zulu->notification_set("This product is not available.",2);

				if($_POST['js']>0) {
					exit;
				}
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			}
		}
		$all_product_data = $class_sale->cart_data(['product_id'=>$pid,'client_id'=>$_SESSION['user']['id'],'session'=>session_id()]);

		$qty_ovr = 0;
		foreach($all_product_data as $apd) {
			$qty_ovr += $apd['quantity'];
		}
		if(!is_numeric($quan)) {
			$zulu->notification_set("No quantity specified.",2);
			$die = true;
		}

        $product_row = $class_product->product_data(['id'=>$pid]);
		$product_meta = $zulu->meta_array($class_product->product_meta($pid));
        if($product_row['hide']) {
            $zulu->notification_set("This product is currently unavailable.",2);
			$die = true;
        }
		if(isset($setting['ws_shop_stock_backorder'])&&$setting['ws_shop_stock_backorder']==0&&$product_meta['stock']<($quan+$qty_ovr)) {
			$zulu->notification_set("This product isn't currently available in the quantity requested. Please contact us to order additional quantities.",2);
			$die = true;
		}

		if(isset($_POST['attribute_option'])) {
			$post['custom']['attribute_option'] = $_POST['attribute_option'];
		}

		if(!$die) {
			$add = $class_sale->cart_add($post);


			if($add['success']) {
				if(count($class_sale->vars->cart_add_nfc)) {
					$suffix = "<br><i class=\"fas fa-chevron-right\"></i> ".implode("<br><i class=\"fas fa-chevron-right\"></i> ",$class_sale->vars->cart_add_nfc);
				}
				$zulu->notification_set("Product was added to your basket. ".$suffix,1);
				$_SESSION['Product_Added'] = $pid;

				if($_GET['Return']=='basket_popdown') {
					header("Location: ".$zulu->front_link(true,['self'=>true,'query'=>['Action'=>'popup_basket']]));
					exit;
				}

				if($_POST['js']>0) {
					exit;
				}

				header("Location: ".$zulu->front_link(FE_rel."checkout/basket/"));
				exit;
			} else {
				$zulu->notification_set($add['msg'],2);
				if($_POST['js']>0) {
					exit;
				}
			}
		} elseif($_POST['js']>0) {
			exit;
		}
	}*/

	//Check PID
	if(!isset($_GET['ProductID']) || $_GET['ProductID']=="") {
		$zulu->notification_set("Product ID cannot be identified.",2);
		header("Location: ".FE_rel."browse/");
		exit;
	}
	$pid = $db->escape_string($_GET['ProductID']);

	// Popup for basket
	/*if($_GET['Action']=='popup_basket') {
		$summary = checkout_summary(['popup'=>true]);
		echo "
		<div class=\"popdown-wrapper\">
			<div class=\"popdown-head\">
				<h1>You have ".$summary['cart_count']." item".$zulu->s($summary['cart_count'])." in your basket</h1>
			</div>
            ".$zulu->notification()."
			<div class=\"popdown-body\">
				".$summary['html']."
				<p class=\"popdown-total\">Total: <b>".$class_sale->currency_symbol.$summary['total']."</b></p>
			</div>
			<div class=\"popdown-foot text-center\">
                <div class=\"coltable vmiddle\">
                <div class=\"col text-left\">
				<a type=\"button\" class=\"button bt-outline close-popdown\" href=\"#\"><i class=\"fas fa-times\"></i> Dismiss</a>
                </div>
                <div class=\"col text-right\">
                <a type=\"button\" class=\"button bt-green\" href=\" ".FE_rel."checkout/basket.php\"><i class=\"fas fa-shopping-cart\"></i> My Basket</a>
				".$summary['html_button']."
                </div>
                </div>
			</div>
		</div>
		";
		exit;
	}*/

    // Add to wish list
    /*if($_GET['Action'] == "WishlistAdd" && $WISHLIST_enabled) {
        $ajax_request = $_GET['ajax'];
        $ajax_result = true;
        $redir_link = $class_product->product_url($pid);

        if($_SESSION['user']['id'] > 0) {
			$wishlist_row = $class_wishlist->wishlist_data(['client_id'=>$_SESSION['user']['id'], 'product_id'=>$pid, 'first'=>true]);
            $product_name = stripslashes($class_product->name($pid));
			if($wishlist_row['id'] <= 0) {
                $class_wishlist->wishlist_edit(0,['client_id'=>$_SESSION['user']['id'], 'product_id'=>$pid]);
                if(!$ajax_request) {
                    $zulu->notification_set($product_name." has been added to your Wish List.",1);
                }
			} else {
                if(!$ajax_request) {
                    $zulu->notification_set($product_name." is already on your Wish List.",2);
                }
			}
		} else {
            if(!$ajax_request) {
                $redir_link = FE_rel."members/login.php?return=".$_SERVER['REQUEST_URI'];
                $zulu->notification_set("To add items to your Wish List, please log in or create an account.",2);
            } else {
                $ajax_result = false;
            }
		}

        if($ajax_request) {
            echo $ajax_result;
        } else {
            header("Location: ".$redir_link);
        }
        exit;
	}*/

	//-- AJAX Get Attribute combo, overrides default load info and sets new PID
	/*if($_GET['Action']=='price_label'||$_GET['Action']=='image_url') {
		$imgload = ($_GET['Action']=='image_url'?true:false);

		//-- Load attribute select info
		if(count($_POST['attribute_option'])>0) {
			foreach($_POST['attribute_option'] as $key=>$val_arr) {
				$attr_row = $class_product->attribute_data(['product_id'=>$pid,'slug'=>$key,'first'=>true]);
				$options = explode(',',$attr_row['options']);
				foreach($val_arr as $val) {
					$opt_key = array_search($val,$options);
					if($opt_key !== false) {
						$options_price = explode(',',$attr_row['options_price']);
						$attr_additional_price += $options_price[$opt_key];
					}
				}
			}
		}
		if(count($_POST['attribute'])>0) {
			foreach($_POST['attribute'] as $atc) {
				if(trim($atc)==NULL && !is_array($atc)) {
					echo "<i class=\"fas fa-info-circle\"></i> Please select all options.";
					exit;
				}
			}
			unset($atc);
			$data_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>$_POST['attribute']]);
			if(count($data_option)<=0) {
				$price_val = "<i class=\"fas fa-times\"></i> This variant is not available.";
			} elseif(count($data_option)>1) {
				$price_val = "<i class=\"fas fa-info-circle\"></i> Please select all options.";
			} else {
				$pid = $data_option[0];
			}
		}

		if($imgload) {
			$image_data = $class_product->image_data($pid);
			if(trim($image_data['main'])==NULL) {
				$image_data['main'] = FE_crm.FE_path.FE_tpl.'images/placeholder-product.png';
			} else {
				$image_data['main'] = FE_crm.MAIN_rel.$image_data['main'];
			}
			echo $zulu->thumb($zulu->path_clean($image_data['main']),'w=496&h=360&far=1&bg=ffffff');
			exit;
		}
	}*/

	//-- Load Base Product data
	$row_PROD = $class_product->product_data(['id'=>$pid]);
	$pid = $row_PROD['id'];
	$id = $row_PROD['id'];
	$prod_meta_raw = $class_product->product_meta($row_PROD['id']);
	$prod_meta = $zulu->meta_array($prod_meta_raw);

    $product = Products::find($id);
	$client = $product->client;
	$listing = $product->listing;
	$owned_listing = $pedigree_file = $pedigree_file_img = false;
	$is_auction = $product->isAuction();

	if(CLIENT_auth && $client->id == $_SESSION['user']['id']) {
		$owned_listing = true;
		$can_edit = $product->canEdit();
	}

	if(!$owned_listing && $product->hide) {
		$zulu->notification_set("That listing has been disabled and is not viewable.", 2);
		header("Location: ".$zulu->front_link(LINK_browse));
		exit;
	}

	if(isset($_GET['Action'])) {
		if($_GET['Action'] == 'withdraw') {
			if(!$owned_listing) {
				header("Location: ".$zulu->front_link($product->feURL()));
				exit;

			} elseif($listing->hasBids()) {
				$zulu->notification_set("You cannot withdraw a listing once it has bids.", 2);
				header("Location: ".$zulu->front_link($product->feURL()));
				exit;
			}

			$listing->withdraw();
			$zulu->notification_set("Your listing has been withdrawn.", 1);
			header("Location: ".$zulu->front_link(LINK_account_listings));
			exit;

		} elseif($_GET['Action'] == 'watchlist_add') {
			if(!CLIENT_auth) {
				if(isset($_GET['ajax'])) {
					echo 'login';
					exit;
				} else {
					header("Location: ".$zulu->front_link(LINK_account_login));
					exit;
				}
			}
			if($owned_listing) {
				header("Location: ".$zulu->front_link($product->feURL()));
				exit;

			}

			$product->addWatchlist($_SESSION['user']['id']);
			if(isset($_GET['ajax'])) {
				echo '1';
				exit;
			} else {
				header("Location: ".$zulu->front_link(true));
				exit;
			}

		} elseif($_GET['Action'] == 'watchlist_remove') {
			if(!CLIENT_auth) {
				if(isset($_GET['ajax'])) {
					echo 'login';
					exit;
				} else {
					header("Location: ".$zulu->front_link(LINK_account_login));
					exit;
				}
			}
			if($listing->clientHasBid($_SESSION['user']['id'])) {
				if(isset($_GET['ajax'])) {
					echo '0';
					exit;
				} else {
					$zulu->notification_set("You've bid on this listing so you cannot remove it from your watchlist.", 2);
					header("Location: ".$zulu->front_link(LINK_account_login));
					exit;
				}
			}

			$product->removeWatchlist($_SESSION['user']['id']);
			if(isset($_GET['ajax'])) {
				echo '1';
				exit;
			} else {
				header("Location: ".$zulu->front_link(true));
				exit;
			}
		}
	}

	if(isset($_POST['action'])) {
		if($_POST['action'] == 'listing_enquiry') {
			$form_edit->valid = true;
			if($form_edit->validate(['name', 'phone', 'message'])) {
				$zulu->notification_set("Please enter all your details.", 2);
				$form_edit->valid = false;

			} elseif($form_edit->valid) {
				$recap_result = form::validate_recaptcha($class_setting->data['ws_module_google_captcha_api_secret']);
				if(!$recap_result['success']) {
					$zulu->notification_set("We're unable to process your request due to suspected spam. Please try again.", 2);
					$form_edit->valid = false;
				}
			}

			if($form_edit->valid) {
				$to = $client->email;
				$sub = "Listing Enquiry";
				$mess = "<p>Hi ".$client->name_first.",</p>
				<p>You've received an enquiry on one of your listings. See the details of the enquiry below.</p>
				<p>
					<b>Listing:</b> ".stripslashes($product->name)."<br />
					<b>Name:</b> ".$_POST['name']."<br />
					<b>Phone:</b> ".$_POST['phone']."<br />
					<b>Email:</b> ".$_POST['email']."<br />
					<b>Message:</b> ".$_POST['message']."<br />
				</p>";
				$zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'reply'=>$_POST['email']]);

				$zulu->notification_set("Your message has been sent to the seller.", 1);
				header("Location: ".$zulu->front_link(true));
				exit;
			}

		} elseif($_POST['action'] == 'submit_bid') {
			if(!CLIENT_auth) {
				$zulu->notification_set("You must be logged in to place a bid.", 2);
				header("Location: ".$zulu->front_link(LINK_account_login, ['query'=>['return'=>$zulu->front_link(true)]]));
				exit;
			}/* elseif(!$client_in->isVerified()) {
				$zulu->notification_set("You must verify your account to place a bid.", 2);
				header("Location: ".$zulu->front_link(LINK_account_verify, ['query'=>['return'=>$zulu->front_link(true), 'action'=>'resend']]));
				exit;
			}*/
			$min_next_bid = $listing->minNextBid();

			$form_edit->valid = true;
			if($_POST['bid_amount'] <= 0) {
				$zulu->notification_set("You must enter an amount to bid.", 2);
				$form_edit->valid = false;
			} elseif($_POST['bid_amount'] <= $listing->price_bid) {
				$zulu->notification_set("Your bid must be greater than the current bid.", 2);
				$form_edit->valid = false;
			} elseif($_POST['bid_amount'] < $min_next_bid) {
				$zulu->notification_set("The minimum bid is at least $".number_format($min_next_bid).".", 2);
				$form_edit->valid = false;
			}

			if($form_edit->valid) {
				$bid_amount = str_replace(['$',','], '', $_POST['bid_amount']);
				$listing->placeBid($bid_amount, $_SESSION['user']['id']);

				$zulu->notification_set("Your bid has been placed.", 1);
				header("Location: ".$zulu->front_link(true));
				exit;
			}

		} elseif($_POST['action'] == 'set_currency') {
			$currencyCode = $_POST['currency_code'];
			setcookie('currency_code', $currencyCode, time() + (86400 * 30), "/");
			$_SESSION['currencyCode'] = $currencyCode;
			header("Location: ".$zulu->front_link(true));
			exit;
		}
	}

	if(isset($prod_meta['pedigree_file']) && $prod_meta['pedigree_file']) {
		$pedigree_file = MAIN_rel."file/product/".$id."/pedigree/".$prod_meta['pedigree_file'];
		$ext = $class_file->extension($prod_meta['pedigree_file']);
		if(in_array($ext, $class_file->image_extensions)) {
			$pedigree_file_img = true;
		}
	} elseif(isset($prod_meta['pedigree_link']) && $prod_meta['pedigree_link']) {
		$pedigree_file = $prod_meta['pedigree_link'];
	}

	//Admin button
	/*if($_SESSION['zl_user']['auth_type']=='user'&&$_SESSION['zl_user']['id']==$row_PROD['user_id']) {
		$admin_button .= "<p><div class=\"cb plain cb-menu\"><span class=\"cb-link\"><a href=\"".$zulu->link_page('product',['query'=>['id'=>$row_PROD['id'],'Action'=>'edit']],SECTION_path_admin)."\" target=\"".(defined('CRM_fe')?'_blank':'_parent')."\" class=\"button bt-outline\"><i class=\"fas fa-pencil\"></i> Edit Product</a></span></div></p>";
	}*/

	//Options
	/*if($row_PROD['type_variant']==1) {
		$has_options = true;
		$display_mode = ($prod_meta['option_display']!=NULL?$prod_meta['option_display']:$setting['ws_shop_display_mode']);
        $img_hide = [];

		switch($display_mode) { //-- setting to show EITHER attribute selections OR options from list
			case 'select':
				$attribute_items = $class_product->attribute_data(['product_id'=>$pid]);
				foreach($attribute_items as $attribute_row) {
					if($attribute_row['input_hide']==1 || $attribute_row['input']=='checkbox') {
						continue;
					}
					$class_product->vars->attribute_data = $attribute_row;
					$attribute_input[] = [
						'id'		=>	$attribute_row['id'],
						'slug'		=>	$attribute_row['slug'],
						'label'		=>	$attribute_row['name'],
						'type'		=>	($attribute_row['input']!=NULL?$attribute_row['input']:"select"),
						'option'	=>	$class_product->attribute_option_array(),
						'options_price'	=>	explode(',',$attribute_row['options_price']),
						'options'	=>	explode(',',$attribute_row['options']),
					];
				}
				if(count($attribute_input)<=0) {
					unset($display_mode);
				}

			break;
			case 'bulk':
				$bulk_price_data = $class_product->price($pid);
				if($bulk_price_data['price_count']>1&&$bulk_price_data['price_low']<$bulk_price_data['price_high']) {
					$bulk_price = "Priced from ".LOCALE_currency.$zulu->dollar($bulk_price_data['price_low'],true);
				} else {
					$bulk_price = LOCALE_currency.$zulu->dollar($bulk_price_data['price'],true);
				}
				$aio = $class_product->attribute_data(['product_id'=>$pid]);
				$attribute_items = $aio[0];
				$slug = $attribute_items['slug'];
				$option_array = explode(",",$attribute_items['options']);
				foreach($option_array as $oa) {
					$product_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>[$slug=>$oa]]);
					$col_head[] = "<th>".$oa."</th>";
					$x_opts[] = $oa;
				}
				if(count($aio)==1) {
					$x_slug = $slug;

					foreach($option_array as $oa) {
						$product_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>[$x_slug=>$oa]]);
						$col_body[] = "<td><input type=\"text\" name=\"bulk[".$product_option[0]."]\" value=\"".$_POST['bulk'][$product_option[0]]."\" placeholder=\"0\" /></td>";
					}

					$bulk_table = "
					<thead>
						<th>".stripslashes($attribute_items['name'])."</th>".implode("",$col_head)."
					</thead>
					<tbody>
						<tr><td class=\"label\">Quantity</td>".implode("",$col_body)."</tr>
					</tbody>";
				} elseif(count($aio)==2) {
					foreach($aio as $attr_row) {
						if($slug==$attr_row['slug']) {
							continue;
						}
						$attribute_items = $attr_row;
						$x_slug = $attribute_items['slug'];
						$option_array = explode(",",$attribute_items['options']);
						foreach($option_array as $oa) {
							foreach($x_opts as $xopt) {
								$product_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>[$slug=>$xopt,$x_slug=>$oa]]);
								$col_body[] = "<td><input type=\"text\" name=\"bulk[".$product_option[0]."]\" value=\"".$_POST['bulk'][$product_option[0]]."\" placeholder=\"0\" /></td>";
							}
							$row_table[] = "<tr><td>".$oa."</td>".implode("",$col_body)."</tr>";
							unset($col_body);
						}
					}
					$bulk_table = "
					<thead>
						<th></th>".implode("",$col_head)."
					</thead>
					<tbody>
						".implode("",$row_table)."
					</tbody>";
				} else {
					$force_default_selector = true;
				}
			break;
			case 'list':
				$option_data = $class_product->product_data(['parent_id'=>$row_PROD['id']]);
				foreach($option_data as $option_row) {
					$class_product->vars->data = $option_row;
                    $option_meta = $zulu->meta_array($class_product->product_meta($option_row['id']));
                    if($option_row['hide']) {
                        $img_hide[$option_row['id']] = $option_row['image'];
                        continue;
                    }
					$option_price = $class_product->price();
					$option_input[$option_row['id']] = $class_product->name()." (".LOCALE_currency.$zulu->dollar($option_price['price'],true).")";
				}
				if(count($option_input)<=0) {
					unset($display_mode);
				}

			break;
		}

		if($force_default_selector) {
			$display_mode = 'list';
			$option_data = $class_product->product_data(['parent_id'=>$row_PROD['id']]);
			foreach($option_data as $option_row) {
				$class_product->vars->data = $option_row;
				$option_price = $class_product->price();
				$option_input[$option_row['id']] = $class_product->name()." (".LOCALE_currency.$zulu->dollar($option_price['price'],true).")";
			}
			if(count($option_input)<=0) {
				unset($display_mode);
			}
		}

	}

	if(!isset($display_mode)&&$has_options) {
		//if display mode stopped
	}*/

	// get checkbox attributes
	/*$attr_data = $class_product->attribute_data(['product_id'=>$id,'input'=>'checkbox']);
	foreach($attr_data as $attr_row) {
		if($attribute_row['input_hide']==1) continue;
		$attribute_checkbox[] = [
			'id'		=>	$attr_row['id'],
			'slug'		=>	$attr_row['slug'],
			'label'		=>	$attr_row['name'],
			'type'		=>	$attr_row['input'],
			'options_price'	=>	explode(',',$attr_row['options_price']),
			'options'	=>	explode(',',$attr_row['options']),
		];
		if(!isset($display_mode)) $display_mode = 'select';
	}*/

	//Image Data
	$image_data = $class_product->image_data($pid);
	$image_main = $zulu->thumb($image_data['main'],'w=496&h=360&far=1&bg=ffffff');
	$image_main_big = MAIN_rel.$image_data['main'];
	if($image_data['main']==NULL) {
		$image_main = $zulu->thumb(FE_crm.FE_path.FE_tpl."images/placeholder-product.png",'w=496&h=360&far=1&bg=ffffff');
		$image_main_big = FE_rel.FE_tpl."images/placeholder-product.png";
	}
	$image_main = $zulu->path_clean($image_main);
	$image_main_big = $zulu->path_clean($image_main_big);

	if(count($image_data['gallery'])>0) {
		foreach($image_data['gallery'] as $img) {
            if(!in_array(basename($img),$img_hide)) {
                $gallery[] = $img;
            }
		}
	}

	//Video data
	/*if(isset($prod_meta_raw['video'])){
		$video_url_array = explode(",",$prod_meta_raw['video']['value']);
		foreach($video_url_array as $url){
			$video_embed_url = $zulu->video_url($url);
			$url_array =parse_url($video_embed_url);
			if(strpos($url_array['host'], 'you') !== false){
				$video_id = str_replace("/embed/","", $url_array['path']);
				$video_html[] = '<a href="'.$video_embed_url.'" rel="iframe"><div class="coltable col1 vmiddle"><div class="col relative">
								<p class="text-center text-large video-overlay"><span class="fas fa-play"></span></p>
								<img src="https://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg" >
								</div></div></a>';
			}
		}
	}*/

	//Check Exists
	if($row_PROD['id']<=0) {
		$zulu->notification_set("Product does not exist.",2);
		header("Location: ".FE_rel."browse/");
		exit;
	}

	//Rows
	$main_title = stripslashes($row_PROD['name']);
	$description = stripslashes(str_replace(['\r\n',chr(10),chr(13)], '<br />', $row_PROD['description']));
	$price_data = $class_product->price($row_PROD['id']);
	$price = number_format($price_data['price']);
	$images = $class_product->image_data($row_PROD['id']);
	$image = $class_product->image_url($row_PROD['id']).$images['main'];
    $brand = $product->brand;
	$is_live = $product->isLive();

	//Price data
	$price_val = ($price_data['special']?"<span class=\"price-highlight\"><b>Just</b> ":NULL).LOCALE_currency.$zulu->dollar(($price_data['price']+$attr_additional_price),true).($price_data['special']?"</span>":NULL).($price_data['special']?" Was ".LOCALE_currency.$zulu->dollar(($price_data['rrp']+$attr_additional_price),true):NULL);

	//Stock data
	/*$stock_data = $class_product->stock(['meta'=>$prod_meta,'id'=>$id]);
	if($stock_data['show']) {
		$price_val .= " <span class=\"opt opt-".$stock_data['class']."\"><i class=\"fas fa-".$stock_data['icon']."\"></i> ".$stock_data['label'].($stock_data['label_count']?" <b>(".$stock_data['level'].")</b>":NULL)."</span>";
	}*/

    /*$price_extra_html = "";
    $popup_html = "";
    $pay_mod_data = $class_module->module_data(['type'=>'1','status'=>'1']);
    foreach($pay_mod_data as $pay_mod_row) {
        require_once $class_module->include_path($pay_mod_row['id']);
		$pay_mod = new $pay_mod_row['class']();
        if(method_exists($pay_mod,'product_view')) {
            $pay_mod_result = $pay_mod->product_view($pid);
            if(isset($pay_mod_result['price_extra']) && $pay_mod_result['price_extra'] != null) {
                $price_extra_html .= "<div class='price-extra-row'>".$pay_mod_result['price_extra']."</div>";
            }
            if(isset($pay_mod_result['popup_html']) && $pay_mod_result['popup_html'] != null) {
                $popup_html .= $pay_mod_result['popup_html'];
            }
        }
    }
    if($price_extra_html != null) {
        $price_val .= "<div class='price-extra'>".$price_extra_html."</div>";
    }*/

    /*if($row_PROD['hide']) {
        $price_val = "Currently Unavailable";
    }*/

    //-- Price label exit
	/*if($_GET['Action']=='price_label') {
		echo $price_val;
		exit;
	}*/

	$PROD_des_pos = $setting['ws_shop_product_position_info'];

	if($is_auction) {
		$_POST['bid_amount'] = $zulu->dollar($listing->minNextBid());
		$has_bids = $listing->hasBids();
	}

	//-- Meta Data
    if(trim($prod_meta['meta_title']) != null) {
        $meta_title = trim(stripslashes($prod_meta['meta_title']));
	} else {
		$meta_title = $product->spec_gait." For Sale: ".$main_title." (".$product->spec_sire." x ".$product->spec_dam.")";
	}
	$META_title_item[] = $meta_title;

	/*if(trim($prod_meta['meta_description']) != null) {
        $META_description = trim(strip_tags($prod_meta['meta_description']));
    } elseif(trim($description) != null) {
        $META_description = trim(strip_tags($description));
    } else {
    }*/

	$META_description = "Standardbred ".$product->spec_gait." '".$main_title."' (".$product->spec_sire." x ".$product->spec_dam.") listed for sale on ".MAIN_name.". Located in ".$product->regionText().". ";
    //$META_description .= $zulu->shorten($META_description,200);

    $META_keyword = '';
	if(trim($prod_meta['meta_keywords']) != null) {
        $META_keyword = trim(strip_tags(stripslashes($prod_meta['meta_keywords'])));
	}

    //OG Tags
	$image_main_info = getimagesize(MAIN_path.$image_data['main']);
    $rich_stock = 'http://schema.org/InStock';
	if($stock_data['show'] && $stock_data['level'] <= 0) {
		$rich_stock = 'http://schema.org/OutOfStock';
	}
	if(!empty($prod_meta['category'])) {
		$category_row = $class_product->product_data(['id'=>$prod_meta['category']]);
	}

	$image_slider = $product->imageSlider();
	$featured_slider = Products::featuredListingSlider();

    /*if(PRODUCT_reviews_enabled) {
        $show_reviews = true;
        $review_summary = $product->reviewSummary();
        if($review_summary != null) {
            $has_rating = true;
            $reviews = $product->featuredReviews();
            $rating_schema = $product->ratingSchema();
            if(count($reviews) > 0) {
                $has_reviews = true;
                $review_schema = $product->reviewSchema();
            } else {
                $has_reviews = false;
            }
        } else {
            $has_rating = false;
        }
    } else {
        $show_reviews = false;
    }*/

	$zulu->template->website_html['head_close'] .= '
    <link rel="canonical" href="'.$class_product->product_url($pid,NULL,true,true).'">
    <meta property="og:locale" content="en_NZ" />
    <meta property="og:type" content="product" />
    <meta property="og:title" content="'.preg_replace('/[^A-Za-z0-9\ -]/', '', $meta_title).'" />
    <meta property="og:description" content="'.$META_description.'" />
    <meta property="og:url" content="'.$class_product->product_url($pid,NULL,true,true).'" />
    <meta property="og:site_name" content="'.$class_setting->data['ws_site_name'].'" />
    <meta property="og:image" content="'.MAIN_url.$image_data['main'].'" />
    <meta property="og:image:width" content="'.$image_main_info[0].'" />
    <meta property="og:image:height" content="'.$image_main_info[1].'" />
    <meta property="og:product:price:amount" content="'.($price_data['price']+$attr_additional_price).'"/>
    <meta property="og:product:price:currency" content="'.LOCALE_currency_code.'"/>
    '.($category_row['id']>0?'<meta property="og:product:category" content="'.preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($category_row['name'])).'"/>':null).'
    '.($price_data['special']?'
    <meta property="og:product:sale_price:amount" content="'.($price_data['price']+$attr_additional_price).'"/>
    <meta property="og:product:sale_price:currency" content="'.LOCALE_currency_code.'"/>
    ':null).'
    <meta property="og:product:availability" content="'.($stock_data['level']>0?'instock':'oos').'"/>
	';

	//Rich snippets
	$zulu->template->website_html['head_close'] .= '
    <script type="application/ld+json">
        {
            "@context": "http://schema.org/",
            "@type": "Product",
            "name": "'.preg_replace('/[^A-Za-z0-9\ -]/', '', $meta_title).'",
            "image": "'.MAIN_url.$image_data['main'].'",
            "description": "'.$META_description.'",
            '.($category_row['id']>0?'"brand": {
            "@type": "Thing",
                "name": "'.preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($category_row['name'])).'"
            },':null).'
            "offers": {
                "@type": "Offer",
                "priceCurrency": "'.LOCALE_currency_code.'",
                "price": '.$zulu->dollar(($price_data['price']+$attr_additional_price)).',
                "availability": "'.$rich_stock.'",
                "seller": {
                    "@type": "Organization",
                    "name": "'.$class_setting->data['ws_site_name'].'"
                }
            }
            '.($show_reviews&&$has_rating?',"aggregateRating": '.$rating_schema.($has_reviews?',"review": '.$review_schema:null):null).'
        }
    </script>';

	//-- JS: DataLayer
	$zulu->template->website_html['head_open'] .= "
	<!-- ZULU - Google DataLayer - ProductDetail View -->
    <script>
	window.dataLayer = window.dataLayer || [];
	dataLayer.push({
	   \"ecommerce\": {
		 \"currencyCode\":\"".strtoupper(LOCALE_currency_code)."\",
		 \"detail\": {
		   //\"actionField\": {\"list\":\"Boss Clothing\"},
		   \"products\": [{
			 \"id\":\"".$row_PROD['id']."\",
			 \"name\":\"".preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($row_PROD['name']))."\",
			 \"price\":\"".$zulu->dollar(($price_data['price']+$attr_additional_price))."\",
			 \"brand\":\"".($brand!=null?$brand->title:null)."\",
			 \"category\":\"".preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($category_row['name']))."\",
			 //\"variant\":\"black\"
		  }]
		}
	  }
	});
	</script>
	";

	//-- Add FB Pixel Tracking
	if($setting['ws_module_fb_pixel_convert']>0) {
		$zulu->template->website_html['head_close'] .= "
		<!-- ZULU - FB Pixel - ViewContent -->
		<script>
		fbq('track', 'ViewContent', {
			content_name: '".preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($row_PROD['name']))."',
			content_type: 'product',
			value: ".$zulu->dollar(($price_data['price']+$attr_additional_price)).",
			currency: '".strtoupper(LOCALE_currency_code)."',
			content_ids: [".$row_PROD['id']."],
		 });

		 $(document).on('mousedown','.bt-add',function() {
		 	fbq('track', 'AddToCart', {
				content_name: '".preg_replace('/[^A-Za-z0-9\ -]/', '', stripslashes($row_PROD['name']))."',
				content_ids: [".$row_PROD['id']."],
				content_type: 'product',
				value: ".$zulu->dollar(($price_data['price']+$attr_additional_price)).",
				currency: '".strtoupper(LOCALE_currency_code)."',
			});
			return false;
		 });

		</script>
		";
	}

	//Popdown for basket popup
	/*$zulu->template->css_file[] = FE_rel."template/default/assets/popdown/css/jquery.popdown.css";
	$zulu->template->js_file[] = FE_rel."template/default/assets/popdown/lib/jquery.popdown.js";
	$zulu->template->jquery_code[] = "
	$('.bt-order-modal').popdown();
	";
	$zulu->template->js_code[] = "
		$(document).on('click','.bt-order-modal',function() {
			var post_info = $('#form-add-cart').serializeArray();
    		post_info.push({ name: \"js\", value: \"1\" });
			$.ajax({
				url: '".$zulu->front_link(true,['self'=>true])."',
				type: 'POST',
				data: post_info,
				success: function(data) { console.log('Response: ' + data); }
			});

			return false;
		});
		";*/

    // breadcrumbs
    $categories = $product->categories();
    $category_breadcrumb = null;
    $extra_category_breadcrumbs = [];
    if(count($categories) > 0) {
        $category_breadcrumbs = [$product->breadcrumbLinks(true)];
        $category_breadcrumb = Products::breadcrumbsBuild($category_breadcrumbs, true);
        foreach($categories as $key=>$category) {
            $category_breadcrumbs = [$category->breadcrumbLinks(true)];
            $extra_category_breadcrumbs[] = Products::breadcrumbsBuild($category_breadcrumbs, false);
        }
    }

	/*$feature_data = $class_product->feature_data(['product_id'=>$pid,'has_image'=>true]);
	if(count($feature_data)>0) {
		foreach($feature_data as $feature_row) {
			$image = $class_product->image_rel.$id."/feature/".$feature_row['id']."/".$feature_row['image'];
			$feature_image_html .= "
			<div class='col c-img'><a href='".$image."' rel='image' title='".stripslashes($feature_row['description'])."'><img src='".$zulu->thumb($image,"bg=ffffff")."' alt='".stripslashes($feature_row['title'])." image' class='responsive'></a></div>
			<div class='col c-des'><h3>".stripslashes($feature_row['title'])."</h3><p>".($feature_row['description']!=NULL?"<span class='text-small'>".stripslashes($feature_row['description'])."</span>":NULL)."</p></div>";
			$feature_html[] = "<div class='coltable padcol vmiddle feature-rich'>".$feature_image_html."</div>";
			unset($feature_image_html);
		}
		$feature_html = implode("",$feature_html);
	}

	$feature_data = $class_product->feature_data(['product_id'=>$pid,'no_image'=>true]);
	if(count($feature_data)>0) {
		foreach($feature_data as $feature_row) {
			$feature_list_html .= "<li><p>".stripslashes($feature_row['title']).($feature_row['description']!=NULL?"<br><span class='text-small'>".stripslashes($feature_row['description'])."</span>":NULL)."</p></li>";
		}
		$feature_html .= "<ul class='bullet-list'>".$feature_list_html."</ul>";
	}
	if($feature_html != NULL) {
		$feature_html = "<h4>Features</h4>".$feature_html;
	}*/

    $show_related = false;
    if(strstr($class_setting->data['ws_shop_related_products'],'product')) {
        $related_product_html = related_product_html($pid);
        if($related_product_html != NULL) {
            $show_related = true;
        }
    }

	//-- log views
	if(!$owned_listing && $is_live) {
		$listing->addStatView();

		if(!isset($_SESSION['RECENT_VIEWED'])) {
			$_SESSION['RECENT_VIEWED'] = [];
		}
		$_SESSION['RECENT_VIEWED'][] = $id;
	}
	$current_views = $product->statViews();
	$current_watchers = $product->statWatchers();
	$bids = $listing->bids;
	$current_bids = count($bids);
	$has_sold = $product->hasSold();
	$currency_code = $product->currencyCode();

	if($has_sold) {
		$sale_record = $product->saleRecord;
		$client_buyer = $sale_record->clientBuyer;
		if(!$owned_listing && CLIENT_auth && $client_buyer->id == $_SESSION['user']['id']) {
			$is_client_buyer = true;
		} else {
			$is_client_buyer = false;
		}
		if($owned_listing) {
			$contact_label = 'Buyer';
			$contact_name = $client_buyer->nameFull();
			$contact_email = $client_buyer->email;
		} elseif($is_client_buyer) {
			$contact_label = 'Seller';
			$contact_name = $client->nameFull();
			$contact_email = $client->email;
		}
	}

    /*if($WISHLIST_enabled) {
        $wishlist_added = false;
        if($_SESSION['user']['id'] > 0) {
            $wishlist_data = $class_wishlist->wishlist_data(['client_id'=>$_SESSION['user']['id'], 'product_id'=>$pid, 'first'=>true]);
            if($wishlist_data['id'] > 0) {
                $wishlist_added = true;
            }
        }
        if(!$wishlist_added) {
            $wishlist_add_link = $class_wishlist->wishlist_add_url($pid);
        }

        $zulu->template->jquery_code[] = "
        // Wish list code
        ".(!$wishlist_added&&$_SESSION['user']['id']>0?"
        $(document).on('click', '.bt-wishlist-add', function() {
            $.get('".$wishlist_add_link."?ajax=1', function(result) {
                if(result == true) {
                    $('.bt-wishlist-add').hide();
                    $('.wishlist-on').show();
                } else {
                    window.location.href = '".$wishlist_add_link."';
                }
            });
            return false;
        });
        ":null)."
        ".($wishlist_added?"
        $('.wishlist-on').show();
        ":null)."
        ";
    }*/

	//Javascript
	$zulu->template->jquery_code[] = "
	$.ajaxSetup({
		beforeSend: function() {
			$(\"#attribute-label\").html('');
			$('#loader').show();
		},
		complete: function() {
			$('#loader').hide();
		}
	});

	//-- Images
	$(\"a[rel='image']\").fancybox();

	$('#open-images').click(function(e) {
		e.preventDefault();
		//$('.main-link').trigger('click');
		$('.listing-image-slider .slider .slide.tns-slide-active a').trigger('click');
	});
	let bidConfirmed = false;
	$('form#bid-form').on('submit', function(e) {
		if(!bidConfirmed) {
			e.preventDefault();
			$('.popup-overlay#popup-bid').popup({
				autoopen: true,
				scrolllock: true,
			});
		}
	});
	$('.popup-overlay#popup-bid #bid-confirm').click(function(e) {
		e.preventDefault();
		bidConfirmed = true;
		$('.popup-overlay#popup-bid').hide();
		$('form#bid-form').submit();
	});
	";

	$zulu->include_select2();

	/*if($display_mode=='select') {
		$zulu->template->jquery_code[] = "
		//-- Price Data
		$(\".select-attribute\").change(function() {
			var attr_arr = {};
			var attr_opt = {};
			$('.select-attribute').each(function(index) {
				var f_val = $(this).val();
				var f_name = $(this).data('slug');

				if($(this).is(':radio') || $(this).is(':checkbox')) {
					if($(this).is(':checked')) {
						if($(this).is(':checkbox')) {
							if(attr_opt[f_name] == null) {
								attr_opt[f_name] = [];
							}
							attr_opt[f_name].push(f_val);
						} else {
							attr_arr[f_name] = f_val;
						}
					}
				} else {
					attr_arr[f_name] = f_val;
				}
			});
			var post_info = {};
			post_info['attribute'] = attr_arr;
			post_info['attribute_option'] = attr_opt;
			$.ajax({
			  url: \"".FE_rel."browse/product.php?Action=price_label&ProductID=".$id."\",
				type: 'POST',
				data: post_info,
				beforeSend: function() { $('#loader').show(); },
				complete: function(data) { $(\"#attribute-label\").html(data.responseText); $('#loader').hide(); }
			});
			image_load(post_info);
			return false;
		});
		$(\".select-attribute\").first().trigger('change');

		function image_load(post_info) {
			$.ajax({
			  url: \"".FE_rel."browse/product.php?Action=image_url&ProductID=".$id."\",
				type: 'POST',
				data: post_info,
				complete: function(data) {
					$(\"#product-image\").attr('src',data.responseText);
					var resptext = data.responseText;
					var split_init = resptext.split('//');
					var split = split_init[1].split('&');
					var new_src = split[0];
					$(\"#product-image\").parent('a').attr('href','/' + new_src);
				}
			});
		}
		";
	}
	if($display_mode=='list') {
		$zulu->template->jquery_code[] = "
		//-- Price Data
		$(\".input-option\").change(function() {
			var id = $(this).val();
			$.get(\"".FE_rel."browse/product.php?Action=price_label&ProductID=\" + id,function(data) {
				$(\"#attribute-label\").html(data);
			});
			image_load(id);
			return false;
		});
		$(\".input-option\").trigger('change');

		function image_load(id) {
			$.ajax({
			  url: \"".FE_rel."browse/product.php?Action=image_url&ProductID=\" + id,
				type: 'POST',
				complete: function(data) {
					$(\"#product-image\").attr('src',data.responseText);
					var resptext = data.responseText;
					var split_init = resptext.split('//');
					var split = split_init[1].split('&');
					var new_src = split[0];
					$(\"#product-image\").parent('a').attr('href','/' + new_src);
				}
			});
		}
		";
	}*/

	$zulu->include_popup_overlay();

	$bbURL = "https://breedersbible.com/wwp-api/go.aspx?horse=".urlencode($main_title)."&code=A019&token=845EB9A0-A344-4A7D-907E-956409258C00&page=sale";

}

// ###################
// -- Search Brand Actions
// ###################

if(PAGE_file=='search_brand') {

    if(!$BRAND_enabled) {
        header("Location: ".FE_rel."browse/");
        exit;
    }

    $brand_view = ($class_setting->data['ws_shop_brands_view']!=null?$class_setting->data['ws_shop_brands_view']:'tile');

    if($brand_view == 'list') {
        $unique_chars = ProductBrand::selectRaw('SUBSTRING(title, 1, 1) as title')->distinct()->where([['hide','0']])->orderBy('title','ASC')->get()->toArray();
        $alpha_list = $num_list = [];
        foreach($unique_chars as $unique_char) {
            if(ctype_alpha($unique_char['title'])) {
                $alpha_list[] = strtoupper($unique_char['title']);
            } else {
                $num_list[] = $unique_char['title'];
            }
        }
        $char_list = array_merge($alpha_list, $num_list);
    } else {
        $brands = ProductBrand::where([['hide','0']])->orderBy('title','ASC')->get();
    }

    $META_title_item[] = "Our Brands";

    $zulu->template->jquery_code[] = "
    $('.brand-list-quick-link').click(function() {
        var char = $(this).data('char');
        $('html, body').animate({
            scrollTop: ($('.brand-list-block[data-char=\"'+char+'\"]').offset().top)
        }, 100);
        return false;
    });
    ";

}

// ###################
// -- Search Actions
// ###################

if(PAGE_file=='search') {

	//-- Action: SetView
	if($_GET['Action']=='SetView') {
		$_SESSION['SH_CatView'] = $_GET['Value'];
		exit;
	}

	if(isset($_POST['sort'])) {
		$_SESSION['ProductSort'] = $_POST['sort'];
		header("Location: ".$zulu->front_link(true, ['self'=>true, 'filter'=>['sort']]));
		exit;

	} elseif(isset($_GET['sort'])) {
		$_SESSION['ProductSort'] = $_GET['sort'];
		header("Location: ".$zulu->front_link(true, ['self'=>true, 'filter'=>['sort']]));
		exit;
	}

	if(isset($_GET['location_set'])) {
		$_SESSION['BrowseLocation'] = $zulu->esc($_GET['location_set']);
		header("Location: ".$zulu->front_link(true, ['filter'=>['location_set']]));
		exit;
	}
	if(!isset($_SESSION['BrowseLocation'])) {
		$_SESSION['BrowseLocation'] = '-1';
	}

    $DEFAULT_browse_view = ($class_setting->data['ws_shop_browse_default_view']!=null?$class_setting->data['ws_shop_browse_default_view']:'product');
    $ROOT_browse_view = ($class_setting->data['ws_shop_browse_root_view']!=null?$class_setting->data['ws_shop_browse_root_view']:$DEFAULT_browse_view);
    $view_modes = $class_product->config->view_modes;
    $cat_root = ($setting['ws_shop_category']>0?$setting['ws_shop_category']:'0');

    //MySQL Start & Join Setup
	$shop_mode = $class_website->shop_mode();
	$category_join = "product_meta pmc ON product.id = pmc.identifier";
    $join = null;
	$start = ($_GET['Pg']>1?$class_website->config->shop_result_perpage*($_GET['Pg']-1):0);
	$limit = $class_website->config->shop_result_perpage;
    $sql_where = [
        "product.status = 1",
        "product.hide = 0",
        "product.user_id = '".$class_user->authorised->id."'",
        "product.type = 'product'",
        "product.sys = 0",
        "(product.type_variant = 1 OR product.type_variant = 0)",
    ];

	//NEW / SPECIAL?
    if(isset($_GET['view']) && isset($view_modes[$_GET['view']])) {
        $view_type = $_GET['view'];
        $view_title = $view_modes[$view_type]['title'];
        $META_title_item[] = $view_modes[$view_type]['meta_title'];
        $sql_where['view_type'] = $view_modes[$view_type]['sql_where'];
        $view_title_default = $view_modes[$view_type]['title_default'];
        $view_title_category = $view_modes[$view_type]['title_category'];
        $view_title_search = $view_modes[$view_type]['title_search'];
	} else {
        $view_type = null;
    }

    $has_cat = $has_search = $has_brand = false;
    if(isset($_GET['category'])) {
        $has_cat = true;
    }
    if(isset($_GET['search']) && $_GET['search'] != "") {
        $has_search = true;
    }
    if($BRAND_enabled && isset($_GET['brand'])) {
        $has_brand = true;
    }
    if(!$has_cat) {
        $root_view = true;
    } else {
        $root_view = false;
    }

	//OPT SQL (CATEGORY)
	if($has_cat) {
		$slug = $db->escape_string($_GET['category']);
        $category_data = $class_product->product_data(['slug'=>$slug]);
        $category_name = stripslashes($category_data['name']);
        $category_meta = $zulu->meta_array($class_product->product_meta($category_data['id']));
        $category = Products::find($category_data['id']);
        $curr_cat_id = $category->id;

        //-- Active?
		if($category_data['status']!=1) {
			$zulu->notification_set("This category is not available.",2);
			header("Location: ".FE_rel."browse/");
			exit;
		}

        if($curr_cat_id == $cat_root) {
            $root_view = true;
        } else {
            $category_browse_view = ($category_meta['browse_view']!=null&&$category_meta['browse_view']!='default'?$category_meta['browse_view']:$DEFAULT_browse_view);
        }

		$META_title_item[] = $category_name;
		if($shop_mode) {
			$META_title_item[] = "Browse Horses For Sale";
			if(!isset($main_info))
				$main_info = stripslashes($category_data['description']);
			if(!isset($main_title))
				$main_title = "Browse Horse Listings for ".$view_title_category.$category_name;
		} else {
			$META_title_item[] = "Horse Listing Catalogue";
			if(!isset($main_info))
				$main_info = stripslashes($category_data['description']);
			if(!isset($main_title))
				$main_title = "Browse for ".$view_title_category.$category_name;
		}

        if(trim($category_meta['meta_description']) != null) {
            $META_description = trim(strip_tags($category_meta['meta_description']));
        } elseif(trim($category_data['description']) != null) {
            $META_description = trim(strip_tags($category_data['description']));
        } else {
            $META_description = "Browse ".$category_name." online now.";
        }
        if(strlen($META_description) < 100) {
            $META_description = $META_description." Browse ".$category_name." - ".$class_setting->data['ws_site_name']." - Products.";
        } else {
            $META_description = $zulu->shorten($META_description,150);
        }

        $META_keyword = '';
        if(trim($category_meta['meta_keywords']) != null) {
            $META_keyword = trim(strip_tags(stripslashes($category_meta['meta_keywords'])));
        }

		//-- Child Categories
		$class_product->category_children($category_data['id']);
		$child_category_array = $class_product->category_child;
		$child_category_array[$category_data['id']] = $category_data['id'];
		if(count($child_category_array)>0) {
			$sql_where['category1'] = "pmc.field = 'category'";
			$sql_where['category2'] = "pmc.value IN(".implode(",",$child_category_array).")";
		}
	}

	//OPT SQL (SEARCH)
	if($has_search) {

		$searchpre = urldecode($db->escape_string($_GET['search']));
		$search = $searchpre;
		if($has_cat) {
			$main_title = "Searching for \"".$search."\" in ".$category_name.($view_title!=null?", ".$view_title:null);
		} else {
			$main_title = "Searching for \"".$search."\" ".$view_title_search;
		}
		$sql_where['search'] = "((name LIKE '%".strtolower($search)."%' OR sku LIKE '%".strtolower($search)."%') OR MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) OR MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE))";
		$rel_field['search1'] = "((name LIKE '%".strtolower($search)."%') + (sku LIKE '%".strtolower($search)."%')) AS rel1";
		$rel_field['search2'] = "MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel2";
		$rel_field['search3'] = "MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel3";

        if($_GET['search'] != $_SESSION['ProductSearch']) {
            $_SESSION['ProductSort'] = $BROWSE_sort_relevance_key;
		}

		unset($META_title_item);
		$META_title_item[] = "Searching keywords '".stripslashes($searchpre)."'";
		if(!$has_cat) {
			if($shop_mode) {
				$META_title_item[] = "Horses For Sale";
			} else {
				$META_title_item[] = "Product Catalogue";
			}
		} else {
			$META_title_item[] = $category_name;
		}
		$_SESSION['ProductSearch'] = $_GET['search'];

	} else {
        unset($BROWSE_sort_array[$BROWSE_sort_relevance_key]);
    }

    $brand_view = (isset($_GET['brand_view'])&&$_GET['brand_view']);
    if($has_brand) {
        $brand_redirect = false;
        if($_GET['brand'] == null) {
            $brand_redirect = true;
        } else {
            $brand = ProductBrand::where([['slug',$_GET['brand']],['hide','0']])->first();
            if($brand == null) {
                $brand_redirect = true;
            } else {
                $sql_where['brand'] = "brand_id = ".$brand->id;
                if($brand_view) {
                    $main_title = "Shop for ".$brand->title;
                    $main_info = $brand->description;
                    if(trim($brand->meta_title) != null) {
                        $META_title_item = [$brand->meta_title];
                        $META_title_hide = true;
                    } else {
                        $META_title_item[] = "Shop for ".$brand->title;
                    }
                    if(trim($brand->meta_keywords) != null) {
                        $META_keyword = $brand->meta_keywords;
                    }
                    if(trim($brand->meta_description) != null) {
                        $META_description = $brand->meta_description;
                    }
                    if(trim($brand->meta_description) != null) {
                        $META_description = $brand->meta_description;
                    } elseif(trim($brand->description) != null) {
                        $META_description = htmlentities(strip_tags($brand->description));
                    } else {
                        $META_description = "Shop for ".$brand->meta_title." online now.";
                    }
                    if(strlen($META_description) < 100) {
                        $META_description = $META_description." ".$main_title." - ".$class_setting->data['ws_site_name']." - Products.";
                    } else {
                        $META_description = $zulu->shorten($META_description,300);
                    }

                    $brand_link = $brand->feURL();
                }
            }
        }
        if($brand_redirect) {
            header("Location: ".$zulu->front_link(FE_rel."brands/"));
            exit;
        }
    }

    if(isset($_SESSION['ProductSort']) && $_SESSION['ProductSort'] > 0 && isset($BROWSE_sort_array[$_SESSION['ProductSort']])) {
        $sql_sort = $BROWSE_sort_array[$_SESSION['ProductSort']]['sort'];
    } else {
        $sql_sort = $BROWSE_sort_array[0]['sort'];
    }
    if($has_cat) {
        $join = $category_join;
    }

    // determine whether to show products and/or subcategories
    if($root_view) {
        $browse_view = $ROOT_browse_view;
    } elseif($has_cat) {
        $browse_view = $category_browse_view;
    } else {
        $browse_view = $DEFAULT_browse_view;
    }
    if($browse_view == 'category' || $browse_view == 'both') {
        $show_category_results = true;
    } else {
        $show_category_results = false;
    }
    if(!$show_category_results || $browse_view == 'product' || $browse_view == 'both') {
        $show_product_results = true;
    } else {
        $show_product_results = false;
    }
    if($has_search || $has_brand || $view_type != null) {
        $show_product_results = true;
    }

	if($_SESSION['BrowseLocation'] != '-1') {
		$sql_where['location_id'] = "location_id=".$_SESSION['BrowseLocation'];
	}
	$sql_where['live'] = "live=1";
	$sql_where['listing_kind'] = "NOT EXISTS(select * from product_meta pm_listing_kind where pm_listing_kind.identifier=product.id and pm_listing_kind.field='listing_kind' and pm_listing_kind.value='marketplace')";
	/*if($_SERVER['REMOTE_ADDR'] == '101.98.195.134') {
		unset($sql_where['live']);
	}*/

	$hasFilter = false;

	if(isset($_GET['search']) && $_GET['search']) {
		$search = $zulu->esc($_GET['search']);
		$sql_where['search'] = "((name LIKE '%".strtolower($search)."%' OR sku LIKE '%".strtolower($search)."%') OR MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) OR MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE))";
		$rel_field['search1'] = "((name LIKE '%".strtolower($search)."%') + (sku LIKE '%".strtolower($search)."%')) AS rel1";
		$rel_field['search2'] = "MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel2";
		$rel_field['search3'] = "MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel3";
		$hasFilter = true;
	}
	if(isset($_GET['search_gait']) && $_GET['search_gait']) {
		$sql_where['search_gait'] = "spec_gait='".$zulu->esc($_GET['search_gait'])."'";
		$hasFilter = true;
	}
	if(isset($_GET['search_sire']) && $_GET['search_sire']) {
		$sql_where['search_sire'] = "(spec_sire LIKE '%".$zulu->esc($_GET['search_sire'])."%' OR MATCH(spec_sire) AGAINST ('".strtolower($zulu->esc($_GET['search_sire']))."' IN BOOLEAN MODE))";
		$hasFilter = true;
	}
	if(isset($_GET['search_dam']) && $_GET['search_dam']) {
		$sql_where['search_dam'] = "(spec_dam LIKE '%".$zulu->esc($_GET['search_dam'])."%' OR MATCH(spec_dam) AGAINST ('".strtolower($zulu->esc($_GET['search_dam']))."' IN BOOLEAN MODE))";
		$hasFilter = true;
	}
	if(isset($_GET['search_sex']) && $_GET['search_sex']) {
		if($_GET['search_sex'] == 'Male') {
			$sql_where['search_sex'] = "spec_sex IN ('Gelding', 'Colt', 'Stallion')";
		} elseif($_GET['search_sex'] == 'Female') {
			$sql_where['search_sex'] = "spec_sex IN ('Broodmare', 'Filly', 'Mare')";
		} else {
			$sql_where['search_sex'] = "spec_sex='".$zulu->esc($_GET['search_sex'])."'";
		}
		$hasFilter = true;
	}
	if(isset($_GET['search_age_min']) && $_GET['search_age_min']) {
		$sql_where['search_age_min'] = "spec_age >= ".$zulu->esc($_GET['search_age_min']);
		$hasFilter = true;
	}
	if(isset($_GET['search_age_max']) && $_GET['search_age_max']) {
		$sql_where['search_age_max'] = "spec_age <= ".$zulu->esc($_GET['search_age_max']);
		$hasFilter = true;
	}
	if(isset($_GET['search_age_select']) && $_GET['search_age_select']) {
		unset($sql_where['search_age_min'], $sql_where['search_age_max']);
		if($_GET['search_age_select'] == '+') {
			$sql_where['search_age_select'] = "spec_age > 2";
		} else {
			$sql_where['search_age_select'] = "spec_age=".$zulu->esc($_GET['search_age_select']);
		}
		$hasFilter = true;
	}
	if(isset($_GET['search_age']) && $_GET['search_age']) {
		unset($sql_where['search_age_min'], $sql_where['search_age_max']);
		$sql_where['search_age'] = "spec_age=".$zulu->esc($_GET['search_age']);
		$hasFilter = true;
	}
	if(isset($_GET['search_region']) && $_GET['search_region']) {
		$region_check = LocationRegion::where('name', $zulu->esc($_GET['search_age']))->where('status', 1)->first();
		if($region_check->id > 0) {
			$sql_where['search_region'] = "region_id=".$region_check->id;
		}
		$hasFilter = true;
	}
	if(isset($_GET['search_price_min']) && $_GET['search_price_min']) {
		$sql_where['search_price_min'] = "product.price >= ".$zulu->esc($_GET['search_price_min'])."";
		$hasFilter = true;
	}
	if(isset($_GET['search_price_max']) && $_GET['search_price_max']) {
		$sql_where['search_price_max'] = "product.price <= ".$zulu->esc($_GET['search_price_max'])."";
		$hasFilter = true;
	}

	$join = "product_listing pl ON product.listing_id=pl.id";

	//Query FULL
	$row_PRODF = $zulu->table_data($class_product->SQL_table_product, 0, [
		'join'	=>	$join,
		'field'	=>	['DISTINCT(product.id)'],
		'where'	=>	$sql_where,
	]);
	$nrow_PRODF = count($row_PRODF);

	//Query LIMITED
	$sql_field = ['product.*','pl.time_close','pl.stat_view','pl.add_feature'];
	if($_GET['search']!="") {
		$sql_field = array_merge($sql_field, $rel_field);
	}
	$row_PROD = $zulu->table_data($class_product->SQL_table_product, 0, [
		'join'	=>	$join,
		'field'	=>	$sql_field,
		'where'	=>	$sql_where,
		'sort'	=>	$sql_sort,
		'start'	=>	$start,
		'limit'	=>	$limit,
		'group'	=>	'product.id',
		//'test'	=>	$_SERVER['REMOTE_ADDR'] == '101.98.195.134' ? true : false,
	]);
	$nrow_PROD = count($row_PROD);

	//Check for 0 prod
	if($nrow_PROD>0 && $start==0) {
		$start_txt = 1;
	} else {
		$start_txt = $start;
	}
	$startto_txt = $start+($nrow_PROD<$limit?$nrow_PROD:$limit);

	//Rows
	foreach($row_PROD as $row_PROD) {
		if($first_redir) {
			header("Location: ".$class_product->product_url($row_PROD['id'],$row_PROD['slug']));
			exit;
		}
		$class_product->vars->data = $row_PROD;
		$product_HTML .= "<li>".$class_product->product_block()."</li>";
	}

	//Tiled
	$product_HTML = "<ul class=\"product-box row".$class_website->config->shop_result_row_count." ls-master\">{$product_HTML}</ul>";

	//Sub cat
    $subcategory_item_sidebox = [];
    $subcategory_up_level = true;
	if($has_cat) {
		//-- Children
		$sidebox_cat_data = $class_product->product_data(['parent_id'=>$category_data['id'],'type'=>'category','hide'=>'0','sort'=>'sort ASC, name ASC']);
        if(count($sidebox_cat_data) <= 0) {
            $show_category_results = false;
            $show_product_results = true;
            $sidebox_cat_data = $class_product->product_data(['parent_id'=>$category_data['parent_id'],'type'=>'category','hide'=>'0','sort'=>'sort ASC, name ASC']);
        }
	} else {
        $sidebox_cat_data = $class_product->product_data(['type'=>'category','parent_id'=>$cat_root,'hide'=>'0','sort'=>'sort ASC, name ASC']);
        $subcategory_up_level = false;
    }

    // link filter arrays
    $link_filter = ['Pg','category','brand_view'];
    if($brand_view) {
        $link_filter[] = 'brand';
    }
    $link_filter_view_mode = $link_filter_view_mode_all = $link_filter_search = $link_filter_category = $link_filter_brand = $link_filter_brand_remove = $link_filter;
    $link_filter_view_mode_all[] = 'view';
    $link_filter_search[] = 'search';
    $link_filter_brand_remove[] = 'brand';

    $sql_where_category = $sql_where;
    $sql_where_category['category1'] = "pmc.field = 'category'";
    $category_result_item = [];
    if($show_category_results) {
        $category_block_config = [
            'display'       =>  'category',
            'image'         =>  true,
            'link_filter'   =>  $link_filter_category,
        ];
    }
    foreach($sidebox_cat_data as $cat_row) {
        $class_product->vars->data = $cat_row;

        // get product count for category
        if(!$PRODUCT_count_disabled) {
            $class_product->category_child = [];
            $class_product->category_children($cat_row['id']);
            $child_category_array = $class_product->category_child;
            $child_category_array[$cat_row['id']] = $cat_row['id'];
            $sql_where_category['category2'] = "pmc.value IN(".implode(",",$child_category_array).")";
            $row_PRODC = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$category_join,'field'=>['COUNT(DISTINCT(product.id)) count'],'where'=>$sql_where_category,'sort'=>'product.id ASC','first'=>true]);
            $sidebox_category_count = $row_PRODC['count'];
        }

        // if category has products then show in sidebox
        if($PRODUCT_count_disabled || $sidebox_category_count > 0) {
            $cat_link = $class_product->product_url($cat_row['id'],$cat_row['slug']);
            if($brand_view) {
                $cat_link = $brand_link.str_replace(FE_rel."category/", '', $cat_link);
            }
            $subcategory_item_sidebox[] = [
                'link'      =>  $zulu->front_link($cat_link, ['self'=>true, 'filter'=>$link_filter_category]),
                'name'      =>  $cat_row['name'],
                'selected'  =>  ($has_cat&&$cat_row['id']==$category_data['id']?true:false),
                'count'     =>  $sidebox_category_count,
            ];

            if($show_category_results) {
                $category_block_config['prod_count'] = (!$PRODUCT_count_disabled?$sidebox_category_count:null);
                $category_result_item[] = "<li>".$class_product->product_block(0,$category_block_config)."</li>";
            }
        }
    }

    if($subcategory_up_level) {
        if($category_data['parent_id'] > 0) {
            if($brand_view) {
                $subcategory_up_level_link = $brand_link.str_replace(FE_rel."category/", '', $class_product->category_url_front($category_data['parent_id']));
            } else {
                $subcategory_up_level_link = $class_product->category_url_front($category_data['parent_id']);
            }
        } elseif($brand_view) {
            $subcategory_up_level_link = $brand_link;
        } else {
            $subcategory_up_level_link = FE_rel."browse/";
        }
    }

	//Pageination
	$pagination = $zulu->pagination($_GET['Pg'],['page_max_page'=>$class_website->config->shop_result_perpage,'page_max'=>$class_website->config->shop_result_pageindex,'count'=>$nrow_PRODF,'link'=>$zulu->front_link(true,['self'=>true,'filter'=>['category','Pg','brand_view']])]);

    // Query View Types
    $sql_where_view = $sql_where;
    foreach($view_modes as $view_key=>$view_mode) {
        $sql_where_view['view_type'] = $view_mode['sql_where'];
        $row_PRODV = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$join,'field'=>['COUNT(DISTINCT(product.id)) count'],'where'=>$sql_where_view,'sort'=>'product.id ASC','first'=>true]);
        if($row_PRODV['count'] <= 0 && $view_type != $view_key) {
            unset($view_modes[$view_key]);
        } else {
            $view_modes[$view_key]['count'] = $row_PRODV['count'];
        }
    }
    // if has view modes then get the count for all products
    if(count($view_modes) > 0) {
        unset($sql_where_view['view_type']);
        $row_PRODV = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$join,'field'=>['COUNT(DISTINCT(product.id)) count'],'where'=>$sql_where_view,'sort'=>'product.id ASC','first'=>true]);
        $view_mode_all_count = $row_PRODV['count'];
    }

    if($BRAND_enabled && !$brand_view) {
        $sidebox_brands = ProductBrand::where([['hide','0']])->orderBy('title','ASC')->get();
        $sql_where_brand = $sql_where;
        foreach($sidebox_brands as $key=>$sidebox_brand) {
            $sql_where_brand['brand'] = "brand_id = ".$sidebox_brand->id;
            $row_PRODB = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$join,'field'=>['COUNT(DISTINCT(product.id)) count'],'where'=>$sql_where_brand,'sort'=>'product.id ASC','first'=>true]);
            $nrow_PRODB = $row_PRODB['count'];
            if($nrow_PRODB <= 0 && (!$has_brand || $has_brand && $brand->id != $sidebox_brand->id)) {
                unset($sidebox_brands[$key]);
            } else {
                $sidebox_brands[$key]->prod_count = $nrow_PRODB;
            }
        }
    }

    // breadcrumbs
    $crumbs = [];
    if($has_brand && $brand_view) {
        $crumbs[] = $brand::homeBreadcrumbLink();
        $crumbs[] = $brand->breadcrumbLink();
    } elseif($has_search) {
        $crumbs[] = Products::searchBreadcrumbLink(($has_cat||$view_type!=null||($has_brand&&!$brand_view?true:false)));
    }
    if($has_cat) {
        $crumbs[] = $category->breadcrumbLinks(($view_type!=null||($has_brand&&!$brand_view)?true:false), ($has_brand&&$brand_view?$brand:null));
    }
    if($has_brand && !$brand_view) {
        $crumbs[] = $brand->breadcrumbLink(($view_type!=null?true:false));
    }
    if($view_type != null) {
        $crumbs[] = Products::viewModeBreadcrumbLink($view_type);
    }
    if(count($crumbs) > 0) {
        $breadcrumbs = Products::breadcrumbsBuild($crumbs, true);
    } else {
        $breadcrumbs = null;
    }

    // removeable filter
    $removeable_filters = [];
    if($has_brand) {
        $link = $zulu->front_link(true, ['self'=>true, 'filter'=>$link_filter_brand_remove]);
        if($brand_view) {
            $link = str_replace($brand_link, FE_rel.($has_cat?'category/':'browse/'), $link);
        }
        $removeable_filters[] = [
            'label' =>  $brand->title,
            'link'  =>  $link,
        ];
    }
    if($has_search) {
        $removeable_filters[] = [
            'label' =>  "\"".$_GET['search']."\"",
            'link'  =>  $zulu->front_link(true, ['self'=>true, 'filter'=>$link_filter_search]),
        ];
    }

	//Meta
		if(!isset($META_title_item) || count($META_title_item)<=0) {
		if($shop_mode) {
			$META_title_item[] = "Horses For Sale";
		} else {
			$META_title_item[] = "Product Catalogue";
		}
	}

    $search_sidebox_disabled = $class_setting->data['ws_shop_search_sidebox_disable'];

	$zulu->template->js_file[] = MAIN_rel."template/default/assets/smart.find.js";

	//Popdown for basket popup
	if(SHOP_active&&$class_setting->data['ws_shop_catalog_purchase']>0) {
		$zulu->template->css_file[] = FE_rel."template/default/assets/popdown/css/jquery.popdown.css";
		$zulu->template->js_file[] = FE_rel."template/default/assets/popdown/lib/jquery.popdown.js";
		$zulu->template->jquery_code[] = "
		$('.bt-order-modal').popdown();
		";
	}

	$locations = Location::where('status',1)->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();

	if($_SESSION['BrowseLocation'] > 0) {
		$browse_location = Location::find($_SESSION['BrowseLocation']);
		$location_name = $browse_location->name;
	} else {
		$location_name = '';
	}

	$region_options = [];
	if($_SESSION['BrowseLocation'] > 0) {
		$regions = LocationRegion::where('status',1)->where('location_id',$_SESSION['BrowseLocation'])->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();
		foreach($regions as $region) {
			$region_options[$region->id] = $region->name;
		}
	}

    $zulu->template->jquery_code[] = "
    $('.sort-box .filter select[name=\"sort\"]').change(function() {
        $(this).closest('form').submit();
    });

	/*$('.listing-filter .listing-filter-nav .filter-option.filter-dropdown a').click(function(e) {
		e.preventDefault();
		let type = $(this).data('type'),
			filterOption = $(this).closest('.filter-option');
		if(filterOption.hasClass('active')) {
			$('.listing-filter .listing-filter-nav .filter-option.active').removeClass('active');
			$('.listing-filter-dropdown.active').removeClass('active');
		} else {
			$('.listing-filter .listing-filter-nav .filter-option.active').removeClass('active');
			$('.listing-filter-dropdown.active').removeClass('active');
			filterOption.addClass('active');
			$('.listing-filter-dropdown[data-type=\"'+type+'\"]').addClass('active');
		}
	});
	$('.listing-filter .listing-filter-dropdown a').click(function(e) {
		e.preventDefault();
		let val = $(this).html(),
			container = $(this).closest('.listing-filter-dropdown');
		if(val == 'All') {
			val = '';
		}
		container.find('input').val(val);
		$(this).closest('form').submit();
	});*/

	var ageSlider = document.getElementById('age-slider');
	noUiSlider.create(ageSlider, {
	    start: [".(isset($_GET['search_age_min']) && $_GET['search_age_min'] ? $_GET['search_age_min'] : '0').", ".(isset($_GET['search_age_max']) && $_GET['search_age_max'] ? $_GET['search_age_max'] : '15')."],
	    connect: true,
	    range: {
	        'min': 0,
	        'max': 15
	    },
		step: 1,
		tooltips: [
			wNumb({decimals: 0}),
			wNumb({decimals: 0})
		],
	})
	.on('change', function(val) {
		console.log(val);
		$('#age-min').val(val[0]);
		$('#age-max').val(val[1]);
	});
    ";

	$zulu->template->css_file['nouislider'] = FE_tpl_rel."assets/noUiSlider/dist/nouislider.min.css";
	$zulu->template->js_file['nouislider'] = FE_tpl_rel."assets/noUiSlider/dist/nouislider.min.js";
	$zulu->template->js_file['wnumb'] = FE_tpl_rel."assets/wnumb/wNumb.min.js";
}

// ################
// -- Update Actions
// ################

if(PAGE_file=='update') {
	$client_data = $class_client->client_data(['id'=>$_SESSION['user']['id']]);
	$client_meta = $zulu->meta_array($class_client->client_meta($client_data['id']));

	$no_post = false;
	if(!$_POST) {
		$no_post = true;
		foreach($client_meta as $key=>$val) {
			$_POST[$key] = stripslashes($val);
		}
		foreach($client_data as $key=>$val) {
			$_POST[$key] = $val;
		}
	} else {
		//print_r($_POST);exit;
		$form_edit->valid = true;
		if($form_edit->validate(['name_first','name_last','email','timezone','phone_ext','phone_number'])) {
			$zulu->notification_set("Please enter all fields denoted *.",2);
			$form_edit->valid = false;

		} elseif($_POST['location_id'] === '' || ($_POST['location_id'] > 0 && $_POST['region_id'] == '0')) {
			$zulu->notification_set("Please enter all fields denoted *.",2);
			$form_edit->valid = false;

		} elseif($_POST['location_id'] === '0' && $form_edit->validate(['location_other'])) {
			$zulu->notification_set("Please enter all fields denoted *.",2);
			$form_edit->valid = false;

		} elseif(!$form_edit->validate(['password'])&&strlen($_POST['password'])<8) {
			$zulu->notification_set("Please make sure your password contains at least 8 characters.",2);
			$form_edit->valid = false;

		} elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
			$zulu->notification_set("Please enter a valid email address. Check there are <b>no spaces</b>.",2);
			$form_edit->valid = false;

		} elseif($_POST['email']!=NULL) {
			$check_exist = $class_client->client_data(['email'=>$_POST['email']]);
			if(count($check_exist)>0&&$_POST['email']!=$client_data['email']) {
				$zulu->notification_set("Sorry ".$client_data['name_first'].", you cannot change your email, that email is already used on another account.",2);
				$form_edit->valid = false;
			}
		}

		//-- SAVE
		if($form_edit->valid) {

			$data['name_first'] = $_POST['name_first'];
			$data['name_last'] = $_POST['name_last'];
			$data['email'] = $_POST['email'];
			//$data['phone'] = $_POST['phone'];
			$data['company'] = $_POST['company'];
			$data['location_id'] = (int)$_POST['location_id'];
			$data['region_id'] = (int)$_POST['region_id'];
			$data['timezone'] = $_POST['timezone'];
			$data['phone'] = ($_POST['phone_ext'] != 'Other' ? $_POST['phone_ext'] : null).$_POST['phone_number'];

			if(trim($_POST['password'])!=NULL) {
				$data['password'] = $class_user->password_hash($_POST['password']);
				$pw = true;
			}

			$meta = array(
				/*"ship_address"=>addslashes($_POST['ship_address']),
				"ship_suburb"=>addslashes($_POST['ship_suburb']),
				"ship_city"=>addslashes($_POST['ship_city']),
				"ship_post"=>$_POST['ship_post'],
				"ship_country"=>($class_setting->data['ws_shop_country_lock']!=NULL?$class_setting->data['ws_shop_country_lock']:$_POST['ship_country']),*/
				"web_update_last"	=>	time(),
				"web_update_ip"		=>	$_SERVER['REMOTE_ADDR'],
				'location_other'	=>	$_POST['location_id'] === '0' ? $_POST['location_other'] : '',
				'phone_ext'			=>	$_POST['phone_ext'],
				'phone_number'		=>	$_POST['phone_number'],
			);

			$data = $class_client->client_edit($client_data['id'],$data,$meta);
			$id = $data['id'];

			if($data['success']) {

				if($_POST['timezone'] != $_SESSION['TIMEZONE']) {
					//-- store session
					$_SESSION['TIMEZONE'] = $_POST['timezone'];
					//-- store cookie
					setcookie('TIMEZONE', $_SESSION['TIMEZONE'], time() + (86400 * 30), FE_rel);
				}

				$zulu->notification_set("Thank you ".$client_data['name_first'].", your account details have been saved.".($pw?" Your password was updated.":NULL),1);

				if(isset($_GET['return']) && $_GET['return']) {
					header("Location: {$_GET['return']}");
					exit;
				}

				header("Location: ".FE_rel."members/update.php");
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}

	$locations = Location::where('status','1')->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();
	$location_options = Location::optionArray();
	$location_options[0] = 'Other';
	$region_options = LocationRegion::optionArray($_POST['location_id']);

	//BC
    $zulu->template->breadcrumb[] = ['link'=>'update.php','label'=>'Accounts settings'];

	$zulu->template->jquery_code[] = "
	$('#location-select').change(function() {
		let location_id = $(this).val();
		if(location_id !== '0') {
			$('#region-field').show();
			$('#location-other-field').hide();
			if(location_id === '') {
				$('#region-select').html('');
			} else {
				$.post('".$zulu->front_link(true)."', {
					'action': 'load_region_options',
					'location_id': location_id,
				}, function(data) {
					let response = JSON.parse(data);
					$('#region-select').html(response.html);
				});
			}
		} else {
			$('#region-select').html('');
			$('#region-field').hide();
			$('#location-other-field').show();
		}
	});
	";

}

// ################
// -- Reset password Actions
// ################

if(PAGE_file=='reset') {

    if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) {
		header("Location: ".$zulu->front_link(FE_rel."members/"));
		exit;
	}

    $redirect = false;
    if(!isset($_GET['token']) || $_GET['token'] == null) {
        $redirect = true;
    } else {
        $token = $_GET['token'];
        $cpr = ClientPasswordReset::where('uuid', $token)->first();
        if($cpr == null) {
            $redirect = true;
        } else {
            if(!$cpr->canReset()) {
                $redirect = true;
                $zulu->notification_set("This password reset link is no longer usable. Please reset your password again.", 2);
            }
        }
    }
    if($redirect) {
        header("Location: ".$zulu->front_link(FE_rel."members/forgot.php"));
        exit;
    }

    if($_POST['action'] == 'reset') {
        $form_edit->valid = true;

        if($form_edit->validate(['password','password_confirm'])) {
            $zulu->notification_set("You must enter a new password and confirm it.", 2);
            $form_edit->valid = false;

        } elseif($_POST['password'] != $_POST['password_confirm']) {
            $zulu->notification_set("The passwords entered did not match, please try again.", 2);
            $form_edit->valid = false;

        } elseif(strlen($_POST['password']) < 8) {
            $zulu->notification_set("Your password must contain at least 8 characters.", 2);
            $form_edit->valid = false;
        }

        if($form_edit->valid) {

            $client = $cpr->client;
            $client->password = $class_user->password_hash($_POST['password']);
            $client->save();

            $cpr->date_complete = time();
            $cpr->ip_complete   = $_SERVER['REMOTE_ADDR'];
            $cpr->save();

            // login actions
            $client_meta = $client->metaArray($client->meta);
            $zulu->meta_update('client', $client->id, 'web_logins', $client_meta['web_logins']+1);
			$zulu->meta_update('client', $client->id, 'web_last', time());
            $class_sale->cart_move('client', $client->id);
			$class_client->load_session($client->id);

            $zulu->notification_set("Your password has been updated and you have been signed in.", 1);
            header("Location: ".$zulu->front_link(FE_rel."members/"));
            exit;
        }
    }

}

// ################
// -- Forgot password Actions
// ################

if(PAGE_file=='forgot') {

	if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) {
		header("Location: ".$zulu->front_link(FE_rel."members/"));
		exit;
	}

	if($_POST['action'] == 'forgot') {
		$form_edit->valid = true;

        if($form_edit->validate(['email'])) {
            $zulu->notification_set("Please enter an email address to reset your password.",2);
            $form_edit->valid = false;

        } elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $zulu->notification_set("Please enter a valid email address. Check there are no spaces.",2);
            $form_edit->valid = false;

        }  else {
            $client = Clients::where([['email',$_POST['email']],['status',1],['type',1]])->first();

            if($client != null) {
                $cpr = new ClientPasswordReset();
                $cpr->user_id 	    = $class_user->authorised->id;
                $cpr->client_id 	= $client->id;
                $cpr->email 		= $client->email;
                $cpr->ip_add        = $_SERVER['REMOTE_ADDR'];
                $cpr->save();
                $cpr->sendEmail();
            }
            $_SESSION['forgot-password-sent'] = $_POST['email'];

            header("Location: ".$zulu->front_link(FE_rel."members/forgot.php"));
            exit;
        }
	}

    if(isset($_SESSION['forgot-password-sent'])) {
        $sent = true;
        $sent_email = $_SESSION['forgot-password-sent'];
        unset($_SESSION['forgot-password-sent']);
    } else {
        $sent = false;
    }

    $META_title_item[] = "Forgotten password";

    //-- CSS
	$content_class[] = 'page-forgot';

}

// ################
// -- Login Actions
// ################

if(PAGE_file=='login') {

    //-- SIGNOUT
	if($_GET['action'] == 'signout') {
		unset($_SESSION['user']);
		$zulu->notification_set("You were signed out, see you next time.",2);
		header("Location: ".$zulu->front_link(LINK_account_login));
		exit;
	}

	if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) {
		header("Location: ".$zulu->front_link(FE_rel."members/"));
		exit;
	}

	//-- SET RETURN
	if($_GET['return']!=NULL) {
		$_SESSION['SH_Login_Goto'] = $_GET['return'];
	}

	//-- ACTIVATE
	if($_GET['action']=='activate'&&$_GET['token']!=NULL) {
		$token = $db->escape_string($_GET['token']);
		$data = $zulu->table_data('client',0,['first'=>true,'where'=>["token = '".$token."'"]]);
		$client_meta = $zulu->meta_array($class_client->client_meta($data['id']));

		if($client_meta['web_access']==1) {
			if($client_meta['web_verify']!=1) {
				$zulu->meta_update('client',$data['id'],'web_verify',1);
				$zulu->notification_set("Thanks, your account has been activated, please sign in below!",1);
				header("Location: ".$zulu->front_link(LINK_account_login, ['query'=>['activated'=>'1']]));
				exit;

			} else {
				$zulu->notification_set("This account has already been activated.",2);
			}

		} else {
			$zulu->notification_set("This account is not valid for website sign in access.",2);
		}
	}

	//-- LOGIN
	if($_POST['action'] == 'signin') {
		$form_edit->valid = true;

		//-- FIELDS?
		if($form_edit->validate(['email','password'])) {
			$zulu->notification_set("Please enter an email and password.",2);
			$form_edit->valid = false;
		}

		//-- LOGIN
		if($form_edit->valid) {
			$validate = $class_client->login(['email'=>$_POST['email'],'password'=>$_POST['password']]);
			if($validate['msg']!=NULL) {
				$zulu->notification_set($validate['msg'],2);
			}
			/*if(isset($_GET['activated'])) {
				header("Location: ".$zulu->front_link(LINK_account_verify, ['query'=>['action'=>'resend','notify'=>'0']]));
				exit;
			}*/
			if($validate['url'] != NULL) {
				header("Location: ".$validate['url']);
				exit;
			}
		}
	}

	//-- REDIR
	if($_GET['action']=='checkout') {
		$_SESSION['SH_Login_Goto'] = FE_rel."checkout/";
	}

    $META_title_item[] = "Sign in to your account";

    $email_input_custom = $password_input_custom = [];
    if(!$_POST) {
        $email_input_custom['autofocus'] = 'autofocus';
    } else {
        $password_input_custom['autofocus'] = 'autofocus';
    }

    //-- CSS
	$zulu->template->body_class[] = 'page-login';
    $zulu->template->body_class[] = 'page-members';

}

// ###################
// -- Register Actions
// ###################

if(PAGE_file=='register') {

    if($class_setting->data['ws_module_google_captcha_api_key'] != null && $class_setting->data['ws_module_google_captcha_api_secret'] != null) {
        $do_recaptcha = true;
        $recaptcha_key = $class_setting->data['ws_module_google_captcha_api_key'];
        $recaptcha_secret = $class_setting->data['ws_module_google_captcha_api_secret'];
    } else {
        $do_recaptcha = false;
    }

    //-- POST
	if($_POST) {

		$form_edit->valid = true;
        if($form_edit->validate(['name_first','name_last','email','password','password_c','location_id','phone_ext','phone_number'])) {
			$zulu->notification_set("Please enter all details in the form.",2);
			$form_edit->valid = false;

		} elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$zulu->notification_set("Please enter a valid email address. Check there are <b>no spaces</b>.",2);
			$form_edit->valid = false;

		} elseif($form_edit->validate(['password']) || strlen($_POST['password']) < 8) {
			$zulu->notification_set("Please enter a password and/or ensure it contains at least 8 characters.",2);
			$form_edit->valid = false;

		} elseif($_POST['password'] != $_POST['password_c']) {
			$zulu->notification_set("The passwords you enter do not match, please try again.",2);
			$form_edit->valid = false;

		}
		if($form_edit->valid) {
            $client_check = Clients::where([['email',$_POST['email']],['type',1],['status',1]])->first();
			if($client_check != null) {
				$zulu->notification_set("An account already exists with this email address, please sign in or use a different email address.",2);
				$form_edit->valid = false;
			}
		}
		if($form_edit->valid) {
			if($form_edit->validate(['terms'])) {
				$zulu->notification_set("Please read and agree to the Terms of Use.",2);
				$form_edit->valid = false;

			}
		}
        if($form_edit->valid && $do_recaptcha) {
            $recaptcha_result = form::validate_recaptcha($recaptcha_secret);
            if(!$recaptcha_result['success']) {
                $zulu->notification_set("We're unable to process your request due to suspected spam. Please try again or contact us if the problem persists.",2);
				$form_edit->valid = false;
            }
        }

		//-- SAVE
		if($form_edit->valid) {

			if($_POST['location_id'] == 'Other') {
				$_POST['location_id'] = 0;
			}

            $client = new Clients();
            $client->user_id    = $class_user->authorised->id;
            $client->name_first = $_POST['name_first'];
            $client->name_last  = $_POST['name_last'];
            $client->email      = $_POST['email'];
			//$client->phone      = $_POST['phone'];
            $client->password   = $client->passwordHash($_POST['password']);
            //$client->refer      = $_POST['refer'];
			$client->location_id= (int)$_POST['location_id'];
			$client->timezone   = $_SESSION['TIMEZONE'];
			$client->phone 		= ($_POST['phone_ext'] != 'Other' ? $_POST['phone_ext'] : null).$_POST['phone_number'];
            $client->save();
            $id = $client->id;

			if($id > 0) {

                $meta = [
                    "web_access"    => 1,
                    "web_ip"        => $_SERVER['REMOTE_ADDR'],
                    "web_verify"    => 0,
					'phone_ext'		=>	$_POST['phone_ext'],
					'phone_number'	=>	$_POST['phone_number'],
                ];
                foreach($meta as $key=>$val) {
                    ClientMeta::updateOrCreate([
                        'identifier'    =>  $id,
                        'field'         =>  $key,
                    ],[
                        'value'         =>  $val,
                    ]);
                }

				$class_client->change_type($id, 1);
				$client->verifyEmail();

				$zulu->notification_set("Thank you ".$client->name_first.", please proceed to <b>activate your account via an email we just sent you</b>. Remember to check your spam folder, if you cannot find the email after several minutes, contact us for support.",1);
				header("Location: ".FE_rel."members/login.php");
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}

	$locations = Location::where('status','1')->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();

    $META_title_item[] = "Create an account";

}

if(PAGE_file=='verify') {

	$client = Clients::find($_SESSION['user']['id']);
	$meta = $client->metaArray($client->meta);

	if(!$client->phone) {
		$zulu->notification_set("Please add a phone number to your account so you can verify it.", 1);
		header("Location: ".$zulu->front_link(LINK_account_update, ['query'=>['return'=>$zulu->front_link(true, ['self'=>true])]]));
		exit;
	}

	//-- redirect if verified already
	if(isset($meta['web_verify_phone']) && $meta['web_verify_phone'] > 0) {
		$zulu->notification_set("Your account has already been verified.", 1);
		header("Location: ".FE_rel."members/");
		exit;
	}

	if(isset($_GET['action']) && $_GET['action'] == 'resend') {
		$result = $client->verifyPhone();
		if(!$result) {
			$zulu->notification_set("We were unable to send you a verification code. Please try resending or check that your phone number is entered correctly <a href='".$zulu->front_link(LINK_account_update)."' target='_blank'>here</a>.", 2);

		} elseif(!isset($_GET['notify']) || $_GET['notify'] == '1') {
			$zulu->notification_set("Verification code sent.", 1);
		}
		header("Location: ".$zulu->front_link(true, ['self'=>true, 'filter'=>['action','notify']]));
		exit;
	}

    //-- POST
	if($_POST) {

		$form_edit->valid = true;
        if($form_edit->validate(['verify_code'])) {
			$zulu->notification_set("Enter your verification code.",2);
			$form_edit->valid = false;

		}
        if($form_edit->valid) {
            //-- check code
			$phoneAuth = $client->phoneAuths()->whereActive()->latest()->first();
			if(!$phoneAuth || !$phoneAuth->check($_POST['verify_code'])) {
				$zulu->notification_set("You entered an invalid or expired code.",2);
				$form_edit->valid = false;
			}
        }

		//-- SAVE
		if($form_edit->valid) {

			$phoneAuth->complete();

			$zulu->notification_set("Thank you ".$client->name_first.", your account has been verified.",1);

			if(isset($_GET['return']) && $_GET['return']) {
				header("Location: {$_GET['return']}");
				exit;
			}

			header("Location: ".FE_rel."members/");
			exit;
		}
	}

    $META_title_item[] = "Verify your account";

}

// ###################
// -- Restricted Content View
// ###################

if(PAGE_file=='content') {

	$renew_data = $class_renew->renew_data(['active'=>true,'client_id'=>$_SESSION['user']['id']]);
	$renew_count = count($renew_data);
	foreach($renew_data as $data) {
		if($data['template_id'] > 0) {
			$template_data = $class_renew->template_data(['id'=>$data['template_id']]);
			$event_dl = $class_file->embed_table(['class'=>['grid-table'],'public'=>true,'root_id'=>$template_data['file_id']]);
			if($template_data['file_id'] > 0) {
				$file_block[] = "<hr><h2>Downloads for ".$template_data['title']."</h2>{$event_dl}";
			}
		}
	}

	if(count($file_block)>0) {
		$content_html = implode("\n\n",$file_block);
	} else {
		$content_html = "<h2>No restricted content is available for you.</h2>";
	}
}

if(PAGE_file=='checkout_index') {

	if(isset($_GET['Do'])) {
        if($_GET['Do']=="Coupon") {
            $code = $db->escape_string($_GET['Code']);
            $check = checkout_coupon_check($code);
            if($check['success']) {
                $_SESSION['CHECKOUT']['COUPON'] = $code;
            } else {
                unset($_SESSION['CHECKOUT']['COUPON']);
            }
            if($check['err_class'] == '2') {
                $zulu->notification_set($check['err'], $check['err_class'], ['tag'=>'coupon']);
            }
            echo json_encode(['success'=>$check['success'],'err'=>$check['err'],'err_class'=>$check['err_class']]);
            exit;

        } elseif($_GET['Do']=="UnlinkCoupon") {
            unset($_SESSION['CHECKOUT']['COUPON']);
            exit;

        } elseif($_GET['Do'] == 'RefreshCart') {
            $result = checkout_summary(['checkout'=>true]);
            echo $result['html'];
            exit;

        }
    }

	//Abandoned Cart Initiate #1
	$abc_token = $class_cache->load('abc_token');
	if(trim($abc_token)==NULL) {
		$abc_token = $zulu->serial();
		$class_cache->save('abc_token',$abc_token);
		$class_cache->save('abc_start',time());
		$setting_record = $class_setting->setting_data(['key'=>'sale_abc_initial']);
		$initial_setting = ($setting_record['sale_abc_initial']>0?$setting_record['sale_abc_initial']:0);
		if($class_cache->load('abc_initial')!=1) {
			$class_setting->setting_edit('sale_abc_initial',($initial_setting+1));
			$class_cache->save('abc_initial',1);
		}
	}

	//Check checkout is for not just mems
	if($_SESSION['user']['id'] <= 0 && $setting['ws_shop_chk_mode']=='mem') {
		$zulu->notification_set("Please login or register to checkout.",2);
		$_SESSION['SH_Login_Goto'] = $_SERVER['REQUEST_URI'];
		header("Location: ".$zulu->front_link(LINK_account_login));
		exit;
	}
	if(!SHOP_active) {
		$zulu->notification_set(SHOP_disable_msg,2);
		header("Location: ".$zulu->front_link(LINK_basket));
		exit;
	}

	if(isset($_SESSION['CHECKOUT']['TOKEN']) && $_SESSION['CHECKOUT']['TOKEN'] != NULL) {
		$row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN']]);
		$meta = $zulu->meta_array($class_sale->sale_meta($row['id']));
        $sale = Sales::find($row['id']);
        $zulu->meta_update('sale',$row['id'],'checkout_step','1');
        unset($_SESSION['CHECKOUT']['STEP']);
        $has_sale = true;
	} else {
        $sale = new Sales();
        $has_sale = false;
    }

	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'sort'=>'title ASC'));
	$cart_count = count($cart_data);
	if($cart_count <= 0) {
		header("Location: ".$zulu->front_link(LINK_basket));
		exit;
	}

	$shipping_options = $class_module->delivery_options();
    $delivery_method_selectable = ($shipping_options['ship']&&$shipping_options['pickup']?true:false);

	/* Load Cart Data*/
    $guest = ($_SESSION['user']['id']<=0?true:false);
	if(!$guest) {
		$client_row = $class_client->client_data(['id'=>$_SESSION['user']['id']]);
		$client_meta = $zulu->meta_array($class_client->client_meta($client_row['id']));
	}
	if(!$_POST) {
		if(!$has_sale && !$guest) {
			$row = $client_row;
			$meta = $client_meta;
		}
		foreach($meta as $key=>$val) {
			$_POST[$key] = $val;
		}
		foreach($row as $key=>$val) {
			$_POST[$key] = $val;
		}

	} else {
		foreach($_POST['meta'] as $key=>$val) {
			$_POST[$key] = $val;
		}
	}

	$checkout_summary = checkout_summary(['checkout'=>true]);
	$cart_count = $checkout_summary['cart_count'];
	$is_tangible = $checkout_summary['is_tangible'];
    if(!$delivery_method_selectable) {
        if($shipping_options['pickup']) {
            $_POST['delivery_method'] = 'pickup';
        } else {
            $_POST['delivery_method'] = 'ship';
        }
    }

	//-- Load cart lines
	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'sort'=>'title ASC'));
	foreach($cart_data as $cart_row) {
		$custom = unserialize($cart_row['custom']);
		if($cart_row['product_id']>0) {
			$product_data = $class_product->product_data(['id'=>$cart_row['product_id']]);
			$product_meta = $zulu->meta_array($class_product->product_meta($cart_row['product_id']));
			if($product_meta['input_label']!=NULL) {
				foreach($custom['input_label'] as $qty=>$lbl) {
					if(trim($lbl)==NULL) {
						$nogood = true;
					}
				}
				if($nogood||count($custom['input_label'])<=0) {
					$zulu->notification_set("Please specify all ".strtolower($product_meta['input_label'])."'s for ".$cart_row['title'].".",2);
					header("Location: ".$zulu->front_link(LINK_basket));
					exit;
				}
			}
			if($cart_row['product_id']==$class_product->schedule_id&&$class_product->schedule_id>0&&$setting['sche_form_initial']!=NULL) {
				$custom = NULL;
				$form_id_array[$setting['sche_form_initial']] = $setting['sche_form_initial'];
				//$form_id_array_config[$setting['sche_form_initial']] = ['object'=>'schedule','object_id'=>1];
			}
			if($cart_row['product_id']==$class_product->ticket_id&&$class_product->ticket_id>0&&$setting['event_form_initial']!=NULL) {
				$custom = NULL;
				$form_id_array[$setting['event_form_initial']] = $setting['event_form_initial'];
			}
		}
	}

	//-- Post Form
	if($_POST['action'] == 'submit') {
		$form_edit->valid = true;

		if($form_edit->validate(['name_first','name_last','email'])) {
			$zulu->notification_set("Please enter your first name, last name and email address.",2);
			$form_edit->valid = false;

		} elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {
			$zulu->notification_set("Please enter a valid email address. Check there are <b>no spaces</b>.",2);
			$form_edit->valid = false;
		}
		if($is_tangible) {
            if($delivery_method_selectable && $form_edit->validate(['delivery_method'])) {
                $zulu->notification_set("Please select a Delivery Method.",2);
                $form_edit->valid = false;

            } elseif(!$shipping_options['pickup'] && $_POST['delivery_method'] == 'pickup') {
                $zulu->notification_set("Pickup is unavailable for your order.",2);
                $form_edit->valid = false;

            } elseif(!$shipping_options['ship'] && $_POST['delivery_method'] == 'ship') {
                $zulu->notification_set("Shipping is unavailable for your order.",2);
                $form_edit->valid = false;

            } elseif(!$shipping_options['pickup'] || $_POST['delivery_method'] == 'ship') {
                if($form_edit->validate(['ship_name_first','ship_name_last','ship_address','ship_city','ship_country'],['meta'=>true])) {
                    $zulu->notification_set("Please enter the shipping details fields denoted *.",2);
                    $form_edit->valid = false;
                }
            } elseif(!$shipping_options['ship'] || $_POST['delivery_method'] == 'pickup') {
                if($form_edit->validate(['pickup_option'])) {
                    $zulu->notification_set("Please select a Pickup Location.",2);
                    $form_edit->valid = false;
                } else {
                    $pickup_option_parts = explode('-', $_POST['pickup_option']);
                    $module = $pickup_option_parts[0];
                    $module_option = $pickup_option_parts[1];
                    if($module <= 0 || $module_option <= 0) {
                        $zulu->notification_set("Please select a Pickup Location.",2);
                        $form_edit->valid = false;
                    } else {
                        $pickup_avail_die = false;
                        $module_shipping_row = $class_module->module_data(['id'=>$module]);
                        if($module_shipping_row['id'] <= 0) {
                            $pickup_avail_die = true;
                        } else {
                            require_once $class_module->include_path($module_shipping_row['id']);
                            $module_ship = new $module_shipping_row['class']();
                            if(!$module_ship) {
                                $pickup_avail_die = true;
                            } elseif(method_exists($module_ship, 'checkout_pickup_location')) {
                                $pickup_info = $module_ship->checkout_pickup_location($module_option);
                            } else {
                                $pickup_info['name'] = $_POST['note'] = "";
                            }
                        }
                        if($pickup_avail_die) {
                            $zulu->notification_set("The Pickup Location you selected is unavailable, try select another one.",2);
                            $form_edit->valid = false;
                        }
                    }
                }
            }
        }
		if(count($form_id_array)>0) {
			foreach($form_id_array as $form_id_item) {
				$response = $class_form_post->form_process(['id'=>$form_id_item]);
				if(!$response['success']) {
					$form_edit->valid = false;
				} else {
					$form_complete_id[] = $response['id'];
				}
			}
		}

		if($form_edit->valid) {

			if($class_setting->data['ws_shop_country_lock'] != NULL) {
				$_POST['meta']['ship_country'] = $class_setting->data['ws_shop_country_lock'];
			}

			//-- Load sale info
			$client_id = $_SESSION['user']['id'];
			$sale_data = [
                'name'      =>  $zulu->esc($_POST['name_first']." ".$_POST['name_last']),
                'client_id' =>  $client_id,
                'user_id'   =>  $class_user->authorised->id,
                'date'      =>  $zulu->dateDecode(time()),
                'date_due'  =>  $zulu->dateDecode(strtotime("+7 days")),
                'email'     =>  $_POST['email'],
                'meta'      =>  [],
                'line'      =>  [],
            ];

			foreach($_POST['meta'] as $key=>$val) {
				$sale_data['meta'][$key] = addslashes($val);
			}
			$sale_data['meta']['name_first'] = addslashes($_POST['name_first']);
			$sale_data['meta']['name_last'] = addslashes($_POST['name_last']);
			if(trim($_POST['company'])!=NULL) {
				$sale_data['meta']['company'] = addslashes($_POST['company']);
			}
			$sale_data['meta']['web_order'] = 1;
			$sale_data['meta']['ip_address'] = $_SERVER['REMOTE_ADDR'];

            if($is_tangible) {
                $sale_data['meta']['delivery_method'] = $_POST['delivery_method'];
			}
            if(!$shipping_options['ship'] || $_POST['delivery_method'] == 'pickup') {
                $sale_data['meta']['module_shipping'] = $module;
                $sale_data['meta']['ship_method'] = $pickup_info['name'];
                $sale_data['meta']['ship_method_note'] = $pickup_info['note'];
                $sale_data['meta']['ship_price'] = '';
                $sale_data['meta']['pickup_option'] = $_POST['pickup_option'];
            }

			//-- Load cart lines
			$cart_data = $class_sale->cart_data(array('client_id'=>$client_id,'session'=>session_id(),'sort'=>'title ASC'));
			foreach($cart_data as $cart_row) {
				$custom = unserialize($cart_row['custom']);
				$custom['sale_cart_id'] = $cart_row['id'];

				if($cart_row['product_id']>0) {
					$product_data = $class_product->product_data(['id'=>$cart_row['product_id']]);
					$product_meta = $zulu->meta_array($class_product->product_meta($product_data['id']));
				}
                $custom_row = serialize($custom);
                $sale_data['line'][] = array('product_id'=>$cart_row['product_id'],'sku'=>$product_data['sku'],'description'=>$cart_row['title'],'quantity'=>$cart_row['quantity'],'price'=>$cart_row['price'],'discount'=>0,'custom'=>$custom_row,'line_id'=>$cart_row['sale_line_id']);

				unset($product_data,$product_meta);
			}

			if($has_sale) {
				$sale_row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN'],'field'=>'id']);
				$result = $class_sale->sale_edit($sale_row['id'],$sale_data);
			} else {
                if($_SESSION['CHECKOUT']['COUPON']) {
                    $coupon = $_SESSION['CHECKOUT']['COUPON'];
                }
				$result = $class_sale->sale_generate($sale_data);
				unset($_SESSION['CHECKOUT']);
				$_SESSION['CHECKOUT']['TOKEN'] = $result['token'];
                if($coupon) {
                    $_SESSION['CHECKOUT']['COUPON'] = $coupon;
                }
			}
			foreach($form_complete_id as $fci) {
				$class_form_post->update($fci,NULL,NULL,[],['object'=>'sale','object_id'=>$result['id'],'reference'=>$sale_data['name']]);
			}

			//-- Rule for abandoned cart
			if($client_id>0) {
				$rule_result = $class_rule->generate_rule_from_auto('sale_abandoned', $client_id);
				$zulu->meta_update('sale',$result['id'],'sale_abandoned_rule_generate', implode(',',$rule_result['id']));
			}
			$abc_token = $class_cache->load('abc_token');
			$checkout_array[1] = [
				'time_start'	=>	$class_cache->load('abc_start'),
				'time_end'		=>	time(),
				'data'	=>	'',
			];
			$zulu->meta_update('sale',$result['id'],'sale_abandoned_data', serialize($checkout_array));
			$class_cache->dump('abc_token');
			$class_cache->dump('abc_start');

			//-- Remove one abandoned record on intiail step
			$setting_record = $class_setting->setting_data(['key'=>'sale_abc_initial']);
			$initial_setting = ($setting_record['sale_abc_initial']>0?$setting_record['sale_abc_initial']:0);
			if($class_cache->load('abc_initial_end')!=1) {
				$class_setting->setting_edit('sale_abc_initial',($initial_setting-1));
				$class_cache->save('abc_initial_end',1);
			}

			//-- Forward
            if($is_tangible && $_POST['delivery_method'] == 'ship') {
                $_SESSION['CHECKOUT']['STEP'] = '2';
                header("Location: ".$zulu->front_link(LINK_checkout_shipping));
                exit;
            } else {
                $_SESSION['CHECKOUT']['STEP'] = '3';
                header("Location: ".$zulu->front_link(LINK_checkout_payment));
                exit;
            }
		}
	}

	$zulu->template->body_class[] = 'page-checkout';
    $META_title_item[] = "Details - Checkout";

	if($delivery_method_selectable) {
        if(!$_POST['delivery_method']) {
            $_POST['delivery_method'] = 'ship';
        }
        $zulu->template->jquery_code[] = "
        $('input[name=\"delivery_method\"]').change(function() {
            let val = $(this).val();
            $('.delivery-option-block').hide();
            $('.delivery-option-block[data-type=\"'+val+'\"]').show();
        });
        ";
    }
    if($is_tangible && $shipping_options['pickup']) {
        $pickup_option_html = $class_module->pickup_option_html();
        if(!$_POST['pickup_option']) {
            $zulu->template->jquery_code[] = "
            $('input[name=\"pickup_option\"]').first().trigger('click');
            ";
        }
    }

}

if(PAGE_file=='checkout_shipping') {

	if($_SESSION['CHECKOUT']['TOKEN'] == NULL) {
		$zulu->notification_set("Sorry, we don't have any checkout info from you. Please start again.",2);
		header("Location: ".$zulu->front_link(LINK_basket));
		exit;
	}

	$guest = ($_SESSION['user']['id']<=0?true:false);
	$row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN']]);
	$meta = $zulu->meta_array($class_sale->sale_meta($row['id']));
    $sale = Sales::find($row['id']);

    if($_POST['action'] == 'get_price') {
        $module = $zulu->esc($_POST['module']);
        $option = $zulu->esc($_POST['option']);
        $price = 0;

        if($module) {
            $module_shipping_row = $class_module->module_data(['id'=>$module]);
            require_once $class_module->include_path($module_shipping_row['id']);
            $module_ship = new $module_shipping_row['class']();

            if($module_ship) {
                $price = $module_ship->checkout_ship_price(true);
                if(is_array($price)) {
                    if($option > 0) {
                        $price = $price[$option];
                    } else {
                        $price = $price[1];
                    }
                } else {
                    $price = $price;
                }
            }
        }

        echo json_encode(['price'=>$zulu->dollar($price)]);
        exit;
    }

    if($meta['checkout_step'] == '0' || ($meta['checkout_step'] == '1' && $_SESSION['CHECKOUT']['STEP'] != '2')) {
        header("Location: ".$zulu->front_link(LINK_checkout));
		exit;
    }

    $shipping_options = $class_module->delivery_options();
    if(!$shipping_options['ship'] || $meta['delivery_method'] == 'pickup') {
		header("Location: ".$zulu->front_link(LINK_checkout_payment));
		exit;
    }

    $zulu->meta_update('sale',$row['id'],'checkout_step','2');
    unset($_SESSION['CHECKOUT']['STEP']);

	//-- Abandoned Cart Initiate #2
	$abc_token = $class_cache->load('abc_token');
	if(trim($abc_token)==NULL) {
		$abc_token = $zulu->serial();
		$class_cache->save('abc_token',$abc_token);
		$abc_data = $zulu->meta_value('sale',$row['id'],'sale_abandoned_data');
		$checkout_array = unserialize($abc_data['value']);
		$checkout_array[2] = [
			'time_start'	=>	time(),
		];
		$zulu->meta_update('sale',$row['id'],'sale_abandoned_data', serialize($checkout_array));
	}

	$checkout_summary = checkout_summary(['checkout'=>true]);
	$is_tangible = $checkout_summary['is_tangible'];

	if($_POST['action'] == 'submit') {
		$form_edit->valid = true;

        if($form_edit->validate(['shipping'])) {
			$zulu->notification_set("No shipping method was selected, please choose one to continue.",2);
			$form_edit->valid = false;
		} else {
            $module_shipping_row = $class_module->module_data(['id'=>$_POST['shipping']]);
            require_once $class_module->include_path($module_shipping_row['id']);
            $module_ship = new $module_shipping_row['class']();
            if(method_exists($module_ship, 'checkout_ship_validate')) {
                $result_val = $module_ship->checkout_ship_validate();
                if(!$result_val['success']) {
                    $zulu->notification_set($result_val['msg'],2);
                    $form_edit->valid = false;
                }
            }
        }

		if($form_edit->valid) {
			if(method_exists($module_ship, 'checkout_ship_process')) {
                $module_ship->checkout_ship_process();
            }
            $sale_data = [
                'meta'  =>  [
                    'module_shipping'	=>	$_POST['shipping'],
                    'ship_method'       =>  $module_shipping_row['name_client'].($_SESSION['CHECKOUT']['SHIP']['NAME']!=NULL?": ".$_SESSION['CHECKOUT']['SHIP']['NAME']:NULL),
                    'ship_price'        => $module_ship->checkout_ship_price(),
                    'ship_method_note'  =>  '',
                ]
			];

			//--Abandoned Save #2
			$abc_data = $zulu->meta_value('sale',$row['id'],'sale_abandoned_data');
			$checkout_array = unserialize($abc_data['value']);
			$checkout_array[2] = [
				'time_end'		=>	time(),
				'data'	=>	'',
			];
			$zulu->meta_update('sale',$row['id'],'sale_abandoned_data', serialize($checkout_array));
			$class_cache->dump('abc_token');
			$class_cache->dump('abc_start');

			$result = $class_sale->sale_edit($row['id'],$sale_data);
			$_SESSION['CHECKOUT']['STEP'] = '3';
			header("Location: ".$zulu->front_link(LINK_checkout_payment));
			exit;
		}

	}

	if(!$_POST) {
		foreach($meta as $key=>$val) {
			$_POST[$key] = $val;
		}
		foreach($row as $key=>$val) {
			$_POST[$key] = $val;
		}
	}

	$shipping_modules = $class_module->checkout_select(2);

    $zulu->template->body_class[] = 'page-checkout';
    $META_title_item[] = "Shipping - Checkout";

    $zulu->template->jquery_code[] = "
    $('.shipping-options input[name=\"shipping\"]').change(function() {
        var parent = $(this).closest('.list-group-item');
        var module = $(this).val();
        $.post('".$zulu->front_link(true)."', {
            'module': module,
            'option': parent.next('.list-group-item.item-detail').find('.module-option').val(),
            'action': 'get_price'
        }, function(data) {
            var response = JSON.parse(data);
            $('#price-ship').html(response.price);
            calc_totals();
        });
    });
    $('.shipping-options .module-option').change(function() {
        var parent = $(this).closest('.list-group-item');
        var radio = parent.prev('.list-group-item').find('input[name=\"shipping\"]').trigger('change');
    });
    if($('.shipping-options .list-group-item.selected').length) {
        $('.shipping-options .list-group-item.selected').find('input[name=\"shipping\"]').trigger('change');
    } else {
        var first_item = $('.shipping-options .list-group-item').first();
        first_item.find('input[name=\"shipping\"]').trigger('click');
        first_item.addClass('selected');
    }
    ";

}

if(PAGE_file=='checkout_payment') {

	//-- Checkout Token Verify
	if($_SESSION['CHECKOUT']['TOKEN'] == NULL) {
		$zulu->notification_set("Sorry, we don't have any checkout info from you. Please start again.",2);
		header("Location: ".$zulu->front_link(LINK_basket));
		exit;
	}

	$guest = ($_SESSION['user']['id']<=0?true:false);
	$row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN']]);
	$meta = $zulu->meta_array($class_sale->sale_meta($row['id']));
    $sale = Sales::find($row['id']);

    if($_POST['action'] == 'load_form_payment') {
        $module = $zulu->esc($_POST['module']);
        $html = null;
        if($module) {
            $module_payment_row = $class_module->module_data(['id'=>$module]);
            require_once $class_module->include_path($module_payment_row['id']);
            $module_payment = new $module_payment_row['class']();
            if($module_payment && method_exists($module_payment, 'form_payment')) {
                $html = $module_payment->form_payment($row['id']);
            }
        }

        echo json_encode(['html'=>$html]);
        exit;
    }

    if($meta['checkout_step'] == '0') {
        header("Location: ".$zulu->front_link(LINK_checkout));
		exit;
    } elseif(($meta['checkout_step'] == '1' && $meta['delivery_method'] == 'ship') || ($meta['checkout_step'] == '2' && $_SESSION['CHECKOUT']['STEP'] != '3')) {
        header("Location: ".$zulu->front_link(LINK_checkout_shipping));
		exit;
    }

    $zulu->meta_update('sale',$row['id'],'checkout_step','3');
    unset($_SESSION['CHECKOUT']['STEP']);

	//-- Abandoned Cart Initiate #3
	$abc_token = $class_cache->load('abc_token');
	if(trim($abc_token)==NULL) {
		$abc_token = $zulu->serial();
		$class_cache->save('abc_token',$abc_token);
		$abc_data = $zulu->meta_value('sale',$row['id'],'sale_abandoned_data');
		$checkout_array = unserialize($abc_data['value']);
		$checkout_array[3] = [
			'time_start'	=>	time(),
		];
		$zulu->meta_update('sale',$row['id'],'sale_abandoned_data', serialize($checkout_array));
	}

	$checkout_summary = checkout_summary(['checkout'=>true]);
	$is_tangible = $checkout_summary['is_tangible'];

	if($_POST['action'] == 'submit') {
		$form_edit->valid = true;

		if((!isset($_POST['billing_option']) || $_POST['billing_option']) && $form_edit->validate(['bill_name_first','bill_name_last','bill_address','bill_city','bill_country'],['meta'=>true])) {
			$zulu->notification_set("Please enter your billing details.<br><b>This includes your name, address, city and country field</b>.",2);
			$form_edit->valid = false;
		}
		if($form_edit->validate(['payment'])) {
			$zulu->notification_set("No payment method was selected, please choose one to continue.",2);
			$form_edit->valid = false;
		}
        if($_SESSION['CHECKOUT']['COUPON'] != NULL) {
			$check = checkout_coupon_check($_SESSION['CHECKOUT']['COUPON']);
			if($check['success']) {
				$has_coupon = true;
				$coupon_data = $class_sale->coupon_data(array('code'=>$_SESSION['CHECKOUT']['COUPON']));
			} else {
				unset($_SESSION['CHECKOUT']['COUPON']);
				$zulu->notification_set($check['err'],$check['err_class']);
				$form_edit->valid = false;
			}
		}

        if($form_edit->valid) {
            $module_payment_row = $class_module->module_data(['id'=>$_POST['payment']]);
            require_once $class_module->include_path($module_payment_row['id']);
            $module_payment = new $module_payment_row['class']();
            if(method_exists($module_payment,'verify_payment') && $checkout_summary['total'] > 0) {
                $result = $module_payment->verify_payment();
                if(!$result['success']) {
                    $zulu->notification_set($result['msg'],2);
                    $form_edit->valid = false;
                }
            }
        }

		if($form_edit->valid) {

            $module_payment_row = $class_module->module_data(['id'=>$_POST['payment']]);
			$sale_data = [
                'pay_method'    =>  $module_payment_row['name_client'],
                'meta'          =>  [
                    'module_payment'    =>	$_POST['payment']
                ],
            ];

            foreach($_POST['meta'] as $key=>$val) {
				$sale_data['meta'][$key] = addslashes($val);
			}

            //-- copy shipping fields to billing
            if(isset($_POST['billing_option']) && $_POST['billing_option']) {
                $addr_fields = ['name_first','name_last','company','address','suburb','city','post','country'];
                foreach($addr_fields as $addr_field) {
                    $_POST['meta']['bill_'.$addr_field] = $_POST['meta']['ship_'.$addr_field];
                }
            } elseif($class_setting->data['ws_shop_country_lock'] != NULL) {
				$_POST['meta']['bill_country'] = $class_setting->data['ws_shop_country_lock'];
			}

            $class_sale->sale_edit($row['id'], $sale_data);

            //-- Update client records
			if($_SESSION['user']['id'] > 0) {
				$addr_types = ['ship','bill'];
				$client_fields = ['name_first','name_last','company','email','phone'];
				$upd_data = $upd_meta = [];
				foreach($addr_types as $addr_type) {
					if(trim($client_meta[$addr_type.'_address']) == NULL) {		//-- if client record address is empty then add all fields
						foreach($addr_fields as $addr_field) {
							$upd_meta[$addr_type.'_'.$addr_field] = $_POST['meta'][$addr_type.'_'.$addr_field];
						}
					} elseif($client_meta[$addr_type.'_address'] == $_POST['meta'][$addr_type.'_address']) {	//-- if client address field matches POST address field
						foreach($addr_fields as $key=>$addr_field) {
							if($key==0) continue;								//-- skip address field as it's already in client record
							if($client_meta[$addr_type.'_'.$addr_field] == NULL && $_POST['meta'][$addr_type.'_'.$addr_field] != NULL) {	//-- if client field is empty then update
								$upd_meta[$addr_type.'_'.$addr_field] = $_POST['meta'][$addr_type.'_'.$addr_field];
							}
						}
					}
				}

				foreach($client_fields as $client_field) {						//-- update client records if they're empty
					if($client_row[$client_field] == NULL && ($_POST[$client_field] != NULL || $_POST['meta'][$client_field] != NULL)) {
						$upd_data[$client_field] = ($_POST[$client_field]!=NULL?$_POST[$client_field]:$_POST['meta'][$client_field]);
					}
				}

				if(!empty($upd_data) || !empty($upd_meta)) {
					$class_client->client_edit($client_row['id'],$upd_data,$upd_meta);
				}
			}

            if($has_coupon) {
				$class_sale->coupon_apply($row['id'], $coupon_data['id']);
			}

			$class_sale->complete($row['id']);
			if($module_payment->online) {
                $_SESSION['Pay_Now'] = true;
            }
            $class_sale->cart_dump();

			//--Abandoned Save #3
			$abc_data = $zulu->meta_value('sale',$row['id'],'sale_abandoned_data');
			$checkout_array = unserialize($abc_data['value']);
			$checkout_array[3] = [
				'time_end'		=>	time(),
				'data'	=>	'',
			];
			$zulu->meta_update('sale',$row['id'],'sale_abandoned_data', serialize($checkout_array));
			$class_cache->dump('abc_token');
			$class_cache->dump('abc_start');

            //-- Order Link
            $link_config = ['token'=>$row['token'], 'co'=>'1'];
			if($guest) {
                $link_config['Email'] = $row['email'];
            }

            $_SESSION['CHECKOUT']['STEP'] = 'complete';
			$zulu->notification_set("Thank you, your order has been placed.",1);
			header("Location: ".$zulu->front_link(LINK_account_order_view, ['query'=>$link_config]));
			exit;
		}

	}

	if(!$_POST) {
        if(!$guest) {
            $client_row = $class_client->client_data(['id'=>$_SESSION['user']['id']]);
            $client_meta = $zulu->meta_array($class_client->client_meta($client_row['id']));
            foreach($client_meta as $key=>$val) {
                $_POST[$key] = $val;
            }
            foreach($client_row as $key=>$val) {
                $_POST[$key] = $val;
            }
        }
		foreach($meta as $key=>$val) {
			$_POST[$key] = $val;
		}
		foreach($row as $key=>$val) {
			$_POST[$key] = $val;
		}
        $_POST['billing_option'] = '0';
	} else {
		foreach($_POST['meta'] as $key=>$val) {
			$_POST[$key] = $val;
		}
	}

	$payment_modules = $class_module->checkout_select(1,['sale_total'=>$checkout_summary['total']]);

    $zulu->template->body_class[] = 'page-checkout';
    $META_title_item[] = "Payment - Checkout";

    $zulu->template->jquery_code[] = "
    $('.payment-options input[name=\"payment\"]').change(function() {
        var parent = $(this).closest('.list-group-item');
        var parent_detail = parent.next('.list-group-item.item-detail');
        var parent_prev = $('.payment-options .list-group-item.selected');
        var parent_detail_prev = parent_prev.next('.list-group-item.item-detail');
        var module = $(this).val();
        var module_prev = parent_prev.find('input[name=\"payment\"]').attr('value');
        if(parent_detail_prev.find('.form-payment').length) {
            if(typeof window['destroy_module_'+module_prev] === 'function') {
                window['destroy_module_'+module_prev]();
            } else {
                parent_detail_prev.find('.form-payment').html('');
            }
        }
        if(parent_detail.find('.form-payment').length) {
            $.post('".$zulu->front_link(true)."', {
                'module': module,
                'action': 'load_form_payment'
            }, function(data) {
                var response = JSON.parse(data);
                parent_detail.find('.form-payment').html(response.html);
            });
        }
    });
    $('.payment-options .list-group-item.selected').find('input[name=\"payment\"]').trigger('change');
    ";

}

// ###################
// -- SUPPORT TICKETS
// ###################
if(PAGE_file=='support') {

    if(!$SUPPORT_enabled) {
        header("Location: ".FE_rel."members/");
        exit;
    }

	$client_id = $_SESSION['user']['id'];
	$support_id = $db->escape_string($_GET['id']);
	//Ajax get content
	if($_POST['action'] == 'get_support_content' && $_SESSION['user']['id']>0 && $_POST['ticket_id']>0){
		//load messages for ticket
		$message_data_rows = $class_support->message_data(['support_ticket_id'=>$_POST['ticket_id']]);
		$message_html = '';
		foreach($message_data_rows as $message){
			$message_html .= $class_support->message_create_bubble($message);
		}
		echo json_encode(['success'=>true, 'message_html'=>$message_html]);
		exit;
	}else if($_POST['action'] == 'get_support_content'){
		echo json_encode(['success'=>false]);
		exit;
	}

	//Ajax get content
	if($_POST['action'] == 'send_support_message' && $_SESSION['user']['id']>0 && $_POST['ticket_id']>0){

		$message = $db->escape_string($_POST['reply_message']);
		$class_support->new_ticket_message_client($_POST['ticket_id'], $message , $_SESSION['user']['id'], false, false);

		echo json_encode(['success'=>true]);
		exit;
	} elseif($_POST['action'] == 'get_support_content'){
		echo json_encode(['success'=>false]);
		exit;
	}

	if($_POST && isset($_GET['id'])){
		$notify_email = $_POST['send_email'];
		$reply_data = $_POST['reply_message'];
		if(!$form_edit->validate(['reply_message'])){
			$class_support->new_ticket_message_client($_GET['id'],$reply_data, $client_id, false, $notify_email);
		}else{
			$zulu->notification_set("Please enter a message to send.",2);
		}

	 }else if($_POST){
		 $zulu->notification_set("Please select a ticket to reply to.",2);
	 }


	$ticket_sql_config['client_id'] = $client_id;
	$ticket_data_row = $class_support->support_data($ticket_sql_config);
	$tickets_list_html ='<div class="list-group">';
	foreach($ticket_data_row as $ticket){
		$status_indicator = 'success';
		switch ($ticket['status']) {
		case 'Open':
			$status_indicator = 'success';
		break;
		case 'Hold':
			$status_indicator = 'warning';
		break;
		case 'Closed':
			$status_indicator = 'danger';
		break;
		}
		$has_new = $class_support->support_unread($ticket['id'],false);
		$tickets_list_html .= '<a href="'.$zulu->front_link(true,array('query'=>array('id'=>$ticket['id']))).'" class="list-group-item '.($ticket['id'] == $_GET['id'] ? "active": "").'">
							<div class="row">
								<div class="col-lg-12">
									'.$zulu->shorten($ticket['subject'],25).' <span class="color-grey"><i class="far fa-clock"></i> '.$zulu->time_fancy($ticket['stat_add']).'</span>
									<span class="pull-right opt opt-fill opt-'.$status_indicator.'"> '.$ticket['status'].'</span>
									'.($has_new?'<span class=" text-mini" style="padding-top:3px"><span class="opt opt-warning opt-fill"><i class="fas fa-envelope"></i> NEW</span>':NULL).'</span></span>
								</div>
							</div>
							</a>';
	}
	$tickets_list_html .='</div>';

	if(isset($_GET['id'])){
		//load messages for ticket
		$message_sql_config['support_ticket_id'] = $_GET['id'];
		$message_data_rows = $class_support->message_data($message_sql_config);
		$message_html = '';
		//check ticket status
		$ticket_sql_config['id'] = $_GET['id'];
		$current_ticket = $class_support->support_data($ticket_sql_config);
		$current_ticket_status = $current_ticket['status'];
		foreach($message_data_rows as $message){
			$message_html .= $class_support->message_create_bubble($message);
		}

	}else{
		$message_html = '<p>Select a ticket to view messages</p>';
	}

	//Meta
	$META_title_item[] = "Support Tickets";
    //BC
    $zulu->template->breadcrumb[] = ['label'=>'Support Tickets'];

    $zulu->template->js_code[] = "
    $(function() {
		var message_window_height = document.getElementById('message-window').scrollHeight;$('#message-window').scrollTop(message_window_height);

		refreshContent();
		$('#send_message_btn').click(function(e) {
			send_message(e);
		});

		$('#reply_message').keydown(function(e) {
			var code = e.keyCode ? e.keyCode : e.which;
			if (code == 13) {  // Enter keycode
				send_message(e);
			}
		});
	});

	function send_message(e){
		e.preventDefault();
		var ticket_id = $('#current_ticket_id').val();
		var reply_message = $('#reply_message').val();

		if(ticket_id>0 && reply_message != '') {
			$.post('<?php echo FE_rel; ?>members/support.php',
			{
				ticket_id: ticket_id,
				reply_message: reply_message,
				action: 'send_support_message'
			},
			function(data) {
				data = JSON.parse(data);
				if(data.success){
					$('#reply_message').val('');
					console.log('Message sent.');
					refreshContent();
				}else{
					console.log('Error sending message.');
				}
			});

		}
	}

	function refreshContent(){
		var ticket_id = $('#current_ticket_id').val();

		if(ticket_id>0){
			$.post('<?php echo FE_rel; ?>members/support.php',
			{
				ticket_id: ticket_id,
				action: 'get_support_content'
			},
			function(data, status){
				data = JSON.parse(data);
				if(data.success){
					$('#message-window').html(data.message_html);
					if ($('#chat_auto_scroll').is(':checked')) {
						var message_window_height = document.getElementById('message-window').scrollHeight;
						$('#message-window').scrollTop(message_window_height);
					}
				}else{
					console.log('Error getting messages.');
				}
			});
		}
		console.log('Refreshing message content.');
		setTimeout(refreshContent, 5000);
	}
    ";

}
if(PAGE_file=='support_new') {

    if(!$SUPPORT_enabled) {
        header("Location: ".FE_rel."members/");
        exit;
    }

	//Get user data to pre fill fields
	$name_prefill;
	$email_prefill;
	if($_SESSION['user']['name_first'] != "" && $_SESSION['user']['name_first'] != null){
		$name_prefill = $_SESSION['user']['name_first'].' '.$_SESSION['user']['name_last'];
	}else if($_SESSION['user']['company'] != "" && $_SESSION['user']['company'] != null){
		$name_prefill = $_SESSION['user']['company'];
	}
	if($_SESSION['user']['email'] != "" && $_SESSION['user']['email'] != null){
		$email_prefill = $_SESSION['user']['email'];
	}
	if(isset($_GET['object'])){
		$object = $_GET['object'];
		if(isset($_GET['object_id'])){
			$object_id = $_GET['object_id'];
			switch ($_GET['object']) {
				case 'sale':
					$object_link_html = 'For Sale: #'.$_GET['sale_ref'];
					break;
			}
		}
	}

	if($_POST) {
		$notify_email = $_POST['send_email'];
		if(!$form_edit->validate(['ticket_name', 'ticket_email', 'ticket_subject', 'ticket_message'])){
			$ticket_data['contact_name'] = $_POST['ticket_name'];
			$ticket_data['contact_email'] = $_POST['ticket_email'];
			$ticket_data['subject'] = $_POST['ticket_subject'];
			$ticket_data['status'] = 'Open';
			$ticket_data['object'] = $object;
			$ticket_data['object_id'] = $object_id;
			//Still need to get object
			$ticket_data['client_id'] = $_SESSION['user']['id'];
			$message_data['message_text'] = $_POST['ticket_message'];
			//save the ticket details
			$ticket_data_row = $class_support->support_edit(0,$ticket_data,$message_data,true,$notify_email);
			header("Location: ".FE_rel."members/support.php?id=".$ticket_data_row['id']);
			exit;
		}else{
			$zulu->notification_set("Please fill out all fields.",2);
		}

	}

	//Meta
	$META_title_item[] = "New Support Ticket";
    //BC
    $zulu->template->breadcrumb[] = ['link'=>'support.php', 'label'=>'Support Tickets'];
    $zulu->template->breadcrumb[] = ['label'=>'New Support Ticket'];

}

// ###################
// -- Membership
// ###################

if(PAGE_file=='membership') {

  $renew_row = $class_renew->renew_data(['client_id'=>$_SESSION['user']['id'],'sort'=>'id DESC','first'=>true,'active'=>true]);
	$payment_row = $class_module->module_data(['class'=>'m_stripe', 'first'=>true]);

    if(isset($_POST['action'])) {
		if($_POST['action'] == 'subscribe') {
			$form_edit->valid = true;

			if($_POST['template_id'] <= 0) {
				$zulu->notification_set("Please select a Membership option to proceed.", 2);
				$form_edit->valid = false;

			} else {
				$module_payment_row = $class_module->module_data(['id'=>$payment_row['id']]);
				require_once $class_module->include_path($module_payment_row['id']);
				$module_payment = new $module_payment_row['class']();
				if(method_exists($module_payment,'verify_payment')) {
					$result = $module_payment->verify_payment();
					if(!$result['success']) {
						$zulu->notification_set($result['msg'],2);
						$form_edit->valid = false;
					}
				}
			}

			if($form_edit->valid) {
				$template_row = $class_renew->template_data(['id'=>$_POST['template_id']]);
				$client_id = $_SESSION['user']['id'];

				//-- create draft subscription
				$result = $class_renew->renew_create([
					'client_id'		=>	$client_id,
					'template_id'	=>	$template_row['id'],
					'auto_renew'	=>	1,
					'status'		=>	0,
				]);
				$renew_id = $result['id'];

				if($renew_id <= 0) {
					$zulu->notification_set("We were unable to process your payment. Please try again or contact us if the problem persists.", 2);
					$form_edit->valid = false;
				}
			}

			if($form_edit->valid) {
				$renew_row = $class_renew->renew_data(['id'=>$renew_id, 'merge'=>true]);

				$payment_result = $module_payment->renew_payment_handler($renew_row);
				if(!$payment_result['success']) {
					$msg = "We were unable to process your payment. Please try again or contact us if the problem persists.";
					if(isset($payment_result['msg']) && $payment_result['msg']) {
						$msg = $payment_result['msg'];
					}
					$zulu->notification_set($msg, 2);
					$form_edit->valid = false;
				}
			}

			if($form_edit->valid) {

				$update_result = $module_payment->renew_update($renew_id);
				if($update_result['success']) {

					$_SESSION['renew_create'] = NULL;
					unset($_SESSION['renew_create']); //-- reset this process for fresh one

					$class_client->client_edit($_SESSION['user']['id'], [
						'subscribed'	=>	1,
					]);

					$zulu->notification_set("Thanks for subscribing. You now have access to our exclusive member benefits.", 1);

					if(isset($_GET['return'])) {
						switch($_GET['return']) {
							case 'listing':
								$zulu->notification_set("Thanks for subscribing. Continue listing below and enjoy your savings!", 1);
		    				header("Location: ".$zulu->front_link(LINK_listing_new));
								exit;
								break;
						}
					}

					if(trim($renew_row['_template_meta']['web_redirect']) != NULL) {
						header("Location: ".$renew_row['_template_meta']['web_redirect']);
					} else {
    				header("Location: ".$zulu->front_link(LINK_account_membership));
					}
					exit;

				} else {
					$zulu->notification_set($update_result['reason'], 2);
				}

			}

		} elseif($_POST['action'] == 'payment_update') {
			$module_payment_row = $class_module->module_data(['id'=>$payment_row['id']]);
			require_once $class_module->include_path($module_payment_row['id']);
			$module_payment = new $module_payment_row['class']();

	        $result = $module_payment->update_payment($renew_row['id']);

	        if($result['success']) {
	            $zulu->notification_set("Your payment method has been updated.",1);

	        } else {
				$zulu->notification_set("There was an issue with the payment option, please try again or contact us if the problem persists.",2);
			}

	        header("Location: ".$zulu->front_link(true));
	        exit;
		}
    }

	if(isset($_GET['action'])) {
		if($_GET['action'] == 'cancel') {
			$cancel_result = $class_renew->cancel($renew_row['id']);
			if($cancel_result['success']) {
				$zulu->notification_set("Your subscription has been cancelled.", 1);
			} else {
				$zulu->notification_set($cancel_result['reason'], 2);
			}
			header("Location: ".$zulu->front_link(LINK_account_membership));
			exit;

		} elseif($_GET['action'] == 'cancel_undo') {
			$cancel_result = $class_renew->cancel_undo($renew_row['id']);
			if($cancel_result['success']) {
				$zulu->notification_set("Your subscription has been reactivated.", 1);
			} else {
				$zulu->notification_set($cancel_result['reason'], 2);
			}
			header("Location: ".$zulu->front_link(LINK_account_membership));
			exit;

		} elseif($_GET['action'] == 'change') {
			$result = $class_renew->template_change($renew_row['id'], $_GET['sub']);
			if($result['success']) {
				$zulu->notification_set("Your subscription has been changed successfully.", 1);
			} else {
				$zulu->notification_set($result['reason'], 2);
			}
			header("Location: ".$zulu->front_link(LINK_account_membership));
			exit;
		}
	}

	$template_data = $class_renew->template_data();

    if($renew_row['id'] > 0) {

		if($renew_row['cancel_date'] > 0) {
			$is_cancelled = true;
		} else {
			$is_cancelled = false;
		}

        if($renew_row['template_id'] > 0) {
            $template_row = $class_renew->template_data(['id'=>$renew_row['template_id']]);
            $renew_row['title'] = $template_row['title'];
            $renew_row['price'] = $template_row['price'];
        }

        $renew_meta = $zulu->meta_array($class_renew->renew_meta($renew_row['id']));

		if($renew_meta['coupon_id'] > 0 && $renew_meta['coupon_use_remain'] > 0) {
			$renew_row['price'] = $renew_meta['coupon_price'];
		}

        $payment_method_datail = $class_renew->payment_method_detail($renew_row['id']);

		$payment_row = $class_module->module_data(['class'=>'m_stripe', 'first'=>true]);
		require_once $class_module->include_path($payment_row['id']);
		$module_payment = new $payment_row['class']();

        $zulu->template->jquery_code[] = "
        $('.update-btn').click(function() {
            $('form.sub-form').submit();
            return false;
        });
		$('#payment-update-trigger').click(function(e) {
			e.preventDefault();
            $('#payment-update-box').toggleClass('hide');
        });
		$('.popup-overlay-trigger[data-popup=\"change-sub-popup\"]').click(function() {
			let change_sub_href = $(this).attr('href');
			$('#change-sub-popup .change-sub-btn').attr('href', change_sub_href);
		});
        ";

    } else {

		foreach($template_data as $key=>$template_row) {
			$next_renewal_ts = strtotime("+".$template_row['renew_interval']." ".$class_renew->config->renew_scale[$template_row['renew_scale']]);
			$next_renewal = $zulu->dateTimezone($next_renewal_ts, 'jS F Y');
			$template_data[$key]['next_renewal'] = $next_renewal;

			if($key == 0) {
				$sub_price = $template_row['price'];
				$sub_date = $next_renewal;
			}
		}

		$payment_row = $class_module->module_data(['class'=>'m_stripe', 'first'=>true]);
		require_once $class_module->include_path($payment_row['id']);
		$module_payment = new $payment_row['class']();

		$zulu->template->jquery_code[] = "
        $('.template-radio').change(function() {
			let val = $(this).val(),
				price = $(this).data('price'),
				date = $(this).data('date');

            $('#sub-price').html(price);
			$('#sub-date').html(date);

            return false;
        });
		$('.template-radio').first().trigger('click');
        ";
	}

	$zulu->template->js_code[] = "
  $(document).on('click',\"button[name='submitb']\",function() {
		var ctrl = $(this);
		ctrl.html('Please wait...');
		setTimeout(function() {
			ctrl.html('Purchase Subscription');
		},3000);
	});";

	$zulu->include_popup_overlay();
  $META_title_item[] = 'Premier Members Club';
	$zulu->template->breadcrumb[] = ['label'=>'Premier Members Club'];

}

// ###################
// -- Membership Switch
// ###################

if(PAGE_file=='membership_switch') {

    $renew_row = $class_renew->renew_data(['client_id'=>$client_in->id,'sort'=>'id DESC','first'=>true,'active'=>true]);
    if($renew_row['id'] <= 0) {
        header("Location: ".$zulu->front_link(LINK_account_membership));
        exit;
    }



	if($renew_row['cancel_date'] > 0) {
		$is_cancelled = true;
	} else {
		$is_cancelled = false;
	}

    if($renew_row['template_id'] > 0) {
        $template_row = $class_renew->template_data(['id'=>$renew_row['template_id']]);
        $renew_row['title'] = $template_row['title'];
        $renew_row['price'] = $template_row['price'];
        $renew_row['renew_scale'] = $template_row['renew_scale'];
        $renew_row['renew_interval'] = $template_row['renew_interval'];
    }

	$renew_meta = $zulu->meta_array($renew_row['id']);
	if($renew_meta['coupon_id'] > 0 && $renew_meta['coupon_use_remain'] > 0) {
        $renew_row['price'] = $renew_meta['coupon_price'];
    }

    $template_data = $class_renew->template_data();

    $META_title_item[] = 'Change Subscription';
	$zulu->template->breadcrumb[] = ['link'=>FE_rel.'members/membership/','label'=>'Premier Members Club'];
	$zulu->template->breadcrumb[] = ['label'=>'Change Subscription'];
	$zulu->include_popup_overlay();

}

// ###################
// -- CMS / AJAX Actions
// ###################
if(PAGE_file=='cms') {

	//-- No Post? Find homepage id OR slug with home/index otherwise return 404
	if($_GET['TabName']==NULL) {
		$default_home_id = $setting['ws_site_homepage'];
		if($default_home_id>0) {
			$home_tab = $zulu->table_data($class_post->SQL_table,$default_home_id,['field'=>['slug'],'first'=>true]);
		} else {
			$home_tab = $zulu->table_data($class_post->SQL_table,0,['field'=>['slug'],'first'=>true,'where'=>["slug = 'home' OR slug = 'index'","type = 'page'"]]);
		}
		if($home_tab['slug']!=NULL) {
			$_GET['TabName'] = $home_tab['slug'];
		} else {
			$_GET['TabName'] = '404';
		}
	}

	//-- Load POST
	if($_GET['TabName']!=NULL) {

		if(strstr($_GET['TabName'],"/")) {
			$has_folder = true;
			$split = explode("/",$_GET['TabName']);
		} else {
			$split[0] = $_GET['TabName'];
		}

		foreach($class_post->config->template as $template=>$row) {
			if($has_folder&&$row['slug']==$split[0]) {
				$class_post->vars->data->post_template = $template;
				unset($split[0]);
				$class_post->vars->data->post_slug = implode("/",$split);
				$class_post->vars->data->post_config = $row;
				$class_post->vars->data->post_type = $template;
				break;
			} elseif($row['slug']==$split[0]&&$row['config']['frontend_index']!=NULL&&$row['config']['frontend']) {
				$class_post->vars->data->post_config = $row;
				$class_post->vars->data->post_type = $template;
				$class_post->vars->data->post_template = $row['slug'];
				$class_post->vars->data->post_slug = "index";
				$class_post->vars->index = true;
				$class_post->vars->index_template = $row['config']['frontend_index'];
				break;
			} elseif($row['slug']==NULL&&!$has_folder) {
				$class_post->vars->data->post_template = 'page';
				$class_post->vars->data->post_slug = $split[0];
				$class_post->vars->data->post_config = $row;
				$index_post_type = $template;
				break;
			} else {
				//--next
			}
		}
		$POST_config = $class_post->vars->data->post_config;

		//-- Check if the post index HAS a post page already with same slug, therefore overrides it
		if(!$has_folder&&$class_post->post_exists(['type'=>'page','status'=>'published','slug'=>$POST_config['slug']])&&$row['config']['frontend']&&$POST_config['slug']!=NULL) {
			$class_post->vars->data->post_template = 'page'; /* same code as section above */
			$class_post->vars->data->post_slug = $POST_config['slug'];
			$class_post->vars->data->post_config = $POST_config;
			$class_post->vars->data->post_type = 'page';
			$post_clash_index = true;
		}

		$post_data = $class_post->post_data(['slug'=>$class_post->vars->data->post_slug,'type'=>$class_post->vars->data->post_template,'version'=>$_GET['version']]);
		if($post_clash_index&&$post_data['_meta']['post_index']==NULL) {
			unset($class_post->vars->index,$class_post->vars->index_template);
		}

		//-- Page is published?
		if($post_data['status']!='published'&&!$class_post->vars->index) {
			if($class_user->authorised->id==$post_data['author_id']&&trim($class_user->authorised->role)!=NULL) {
			} else {
				$zulu->template->error_screen = true;
				unset($post_data);
			}
		}

		//-- Post exists?
		if($post_data['id']<=0&&!$class_post->vars->index) {
			//$zulu->template->error_screen = true;
			//$zulu->template->error_type = 404;
			//$zulu->template->error_msg = "Sorry this resource could not be found, check back later!<br><br><code><b>Path:</b> ".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."</code>";
			//unset($post_data);
			//-- ABOVE IS ORIGINAL SPLASH SCREEN TYPE 404 PAGE
			$zulu->template->error_screen = false;
			$post->title = "404 Error";
			$post->content = "Sorry this page could not be found. It may have been moved or deleted.<Br><br>";
			header("HTTP/1.0 404 Not Found");
		}

		//-- FILTER: Author
		if($_GET['Author']>0) {
			$author = $db->escape_string($_GET['Author']);
			$authname = $class_user->name($author);
			$META_title_item[] = "By ".$authname;
			$filter_title[] = "by '".$authname."'";
		} else {
			$author = NULL;
		}

		//-- FILTER: Search
		if($_GET['Search']!=NULL) {
			$search = $db->escape_string($_GET['Search']);
			$META_title_item[] = "Searching for '".stripslashes($search)."'";
			$filter_title[] = "search for '".stripslashes($search)."'";
		} else {
			$search = NULL;
		}

		//-- Check if page is index FROM DEFAULT POST ARRAY TYPES
		if($class_post->vars->index) {
			$post->title = ($class_post->vars->data->post_config['config']['frontend_title']!=NULL?$class_post->vars->data->post_config['config']['frontend_title']:($class_post->vars->data->post_config['name_plural']!=NULL?$class_post->vars->data->post_config['name_plural']:$class_post->vars->data->post_config['name']));
			$post->content = $class_post->vars->data->post_config['config']['frontend_html_header'];
		}

		//-- Check if page is index FROM POST
		if($post_data['_meta']['post_index']!=NULL&&$class_post->config->template[$post_data['_meta']['post_index']]['config']['frontend_index']) {
			$class_post->vars->index = true;
			$class_post->vars->index_template = $class_post->config->template[$post_data['_meta']['post_index']]['config']['frontend_index'];
            $class_post->vars->index_config = $class_post->config->template[$post_data['_meta']['post_index']];
			$index_post_type = $post_data['_meta']['post_index'];

            $class_post->vars->data->post_config = $class_post->config->template[$index_post_type];
		}

		//-- Define post data for connect
		define('PAGE_tab',$post_data['type']);
		define('PAGE_slug',$post_data['slug']);
		define('PAGE_id',$post_data['id']);

		//-- Confirm Post Data
		if($post_data['id']<=0) {
			//-- 404 handler
			$post->_data->template = 'default';

		} else {
			$post = $class_post->post_frontend();
			$post_image = $class_post->post_image();

			if($post->_data->frame!=NULL) {
				if(file_exists(MAIN_path.FE_crm.$tpl_profile_path."frames/".$post->_data->frame)) {
					$post->_data->frame_url = MAIN_path.FE_crm.$tpl_profile_path."frames/".$post->_data->frame;
				}
			}
		}

		//If Post is INDEX
		if($class_post->vars->index) {

			if($class_post->vars->data->post_type!=NULL) {
				$index_post_type = $class_post->vars->data->post_type;
			}
			if($class_post->vars->data->post_type==NULL&&!$post_clash_index) {
				$zulu->notification_set("No post index type was specified.",2);
				unset($post_data);
			}

			$pagination_config = $zulu->pagination_config($_GET['Pg'],['post'=>true]);
			$post_index_filter = ['type'=>$index_post_type,'search'=>$search,'author_id'=>$author,'status'=>'published'];
			if(is_array($class_post->vars->data->post_config['config']['index_filter'])) {
				$post_index_filter += $class_post->vars->data->post_config['config']['index_filter'];
			} elseif(isset($class_post->vars->index_config)) {
                if(is_array($class_post->vars->index_config['config']['index_filter'])) {
                    $post_index_filter += $class_post->vars->index_config['config']['index_filter'];
                }
            }
			$index_data = $class_post->post_data($post_index_filter+['start'=>$pagination_config['start'],'limit'=>$pagination_config['limit']]);
			$index_data_total = $class_post->post_data($post_index_filter+['counter'=>true]);
			$total_row = $class_post->vars->table_total;

			foreach($index_data as $index_item) {
				$image_data = $class_post->post_image($index_item['id'],['base'=>$class_post->config->file_rel]);
				$index_loop[$index_item['id']] = [
					'name'		=>	$index_item['title'],
					'content'	=>	$index_item['content'],
					'author'	=>	$index_item['_data']['author_name'],
					'image'		=>	$image_data,
					'url'		=>	$class_post->post_url($index_item['id']),
					'_data'		=>	$index_item
				];
			}
			$pagination = $zulu->pagination($_GET['Pg'],['post'=>true,'count'=>$total_row,'link'=>$_SERVER['REQUEST_URI']]);

			//Page Title
			if($pagination_config['page']>1) {
				$META_title_item[] = "Page ".$pagination_config['page'];
			}
		} else {

            if($post->_meta->content_position == 'top' || (isset($POST_config['config']['content_position']) && $POST_config['config']['content_position'] == 'top')) {
                $zulu->template->body_class[] = 'header-overlay';
            }

        }

		//-- Post Template Root
		$class_post->vars->post_template_root = $class_post->config->template[$class_post->vars->data->post_type]['config']['frontend_tpl_root'];

		//Breadcrumbs
		if($POST_config['config']['frontend_index']!=NULL) {
			$bc = [];
            if($POST_config['config']['parent_meta'] != null && $post->_meta->{$POST_config['config']['parent_meta']} > 0) {
                $parent_row = $class_post->post_data(['id'=>$post->_meta->{$POST_config['config']['parent_meta']}]);
                $parent_config = $class_post->config->template[$parent_row['type']];
                $bc[] = "<a href=\"".FE_rel.$parent_config['slug']."/\">".($parent_config['config']['frontend_label_plural']!=NULL?$parent_config['config']['frontend_label_plural']:($parent_config['name_plural']!=NULL?$parent_config['name_plural']:$parent_config['name']))."</a>";
                $bc[] = "<a href=\"".FE_rel.$parent_config['slug']."/".$parent_row['slug']."/\">".stripslashes($parent_row['title'])."</a>";
            } else {
				$bc[] = "<a href=\"".FE_rel.$POST_config['slug']."/\">".($POST_config['config']['frontend_label_plural']!=NULL?$POST_config['config']['frontend_label_plural']:($POST_config['name_plural']!=NULL?$POST_config['name_plural']:$POST_config['name']))."</a>";
            }
            $bc[] = "<b>".stripslashes($post->title)."</b>";

			$breadcrumb = "
			<div class=\"breadcrumb\">
				".implode(" <span class=\"divide\">/</span> ",$bc)."
			</div>";
		}

		//If Post Redirects
		if($post->_meta->redirect_link!=NULL) {
			header("Location: ".	$post->_meta->redirect_link);
			exit;
		}

		//CSS
		$zulu->template->body_class[] = 'post-type-'.$post->type;
		if((int)$post->_meta->frame_full_width>0) {
			$zulu->template->body_class[] = 'frame-full-width';
		}
		if($post->_data->frame_url!=NULL) {
			$zulu->template->body_class[] = 'has-frame';
		}

		//META Overrides
		if($post_data['_meta']['meta_title']) {
			unset($META_title_item);
			$META_title_hide = true;
			$META_title_item[] = stripslashes($post_data['_meta']['meta_title']);
			$META_title_custom = true;
		}
		if($post_data['_meta']['meta_keyword']) {
			$META_keyword = 	stripslashes($post_data['_meta']['meta_keyword']);
			$META_keyword_custom = true;
		} else {
			$META_keyword = META_keyword;
		}
		if($post_data['_meta']['meta_description']) {
			$META_description = 	stripslashes($post_data['_meta']['meta_description']);
			$META_description_custom = true;
		} else {
			$META_description = META_description;
		}

		//Google AMP?
		if($class_website->config->module->google_amp) {
			if($_GET['GA_Amp']==1) {
				$zulu->template->google_amp = true;
				$zulu->template->google_amp_non_url = FE_url.str_replace([FE_rel,"amp/"],"",$_SERVER['REQUEST_URI']);
			} else {
				$zulu->template->google_amp_url = str_replace(FE_rel,FE_rel."amp/",FE_url.str_replace([FE_rel],"",$_SERVER['REQUEST_URI']));
			}
		}

		//Post include
		$include = $class_post->post_type_include($post_data['type']);
		if($include!=NULL) {
			include($include);
		}

	}

	//-- Ajax / Basic Functions

	//-- Download file
	if($_GET['Action']=='Download') {
		$class_file->file_download($_GET['token'],['public'=>true,'ovr_parent'=>true]);
		exit;
	}

	//-- Ajax
	if(isset($_GET['Ajax'])) {
		//include AJAX file from CRM
		include(MAIN_path."includes/page/ajax.php");
		exit;
	}

}

/** Extra JS/CSS File inclusions **/
//-- Font awesome
$zulu->template->css_file['fontawesome'] = MAIN_rel."bower_components/font-awesome/css/all.min.css";
//-- Default JS
$zulu->template->js_file['default-site'] = FE_tpl_rel."assets/site.js";
//-- Fancybox
$zulu->template->css_file['jquery-fancybox'] = FE_tpl_rel."assets/fancybox/source/jquery.fancybox.css?1";
$zulu->template->js_file['jquery-fancybox'] = FE_tpl_rel."assets/fancybox/source/jquery.fancybox.js";
$zulu->template->js_file['jquery-fancybox-pack'] = FE_tpl_rel."assets/fancybox/source/jquery.fancybox.pack.js";
$zulu->template->js_file['fancybox'] = FE_tpl_rel."assets/fancybox/source/fancybox.js";
//-- Typekit
if($class_setting->data['ws_module_adobe_typekit'] != NULL) {
    $zulu->template->css_file['typekit'] = "https://use.typekit.net/".$class_setting->data['ws_module_adobe_typekit'].".css";
}

/*if(!CLIENT_auth) {
	$zulu->template->js_file[] = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBzAnafF4Cd-cAx5n4cF58Mwe9bC2N88RI";
	$zulu->template->jquery_code[] = "getGeoLocation();";
}*/
if(!isset($_SESSION['TIMEZONE']) || !$_SESSION['TIMEZONE']) {
	$zulu->template->jquery_code[] = "setTimezone();";
}

$footer_locations = Location::where('status', 1)->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();
