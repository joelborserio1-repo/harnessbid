<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Template

if(MASTER_section=='admin') {

	$user_auth = $class_user->user_authorised();

	$MENU_CONFIG_web[] = ["label"=>"View Website","link"=>FE_rel,"target"=>"_blank","icon"=>"far fa-laptop"];

	if($class_website->config->program=='ZULUSHP'&&MASTER_mode!='main') {
		$MENU_CONFIG_web[] = array("label"=>"Customers","link"=>"#","icon"=>"far fa-users","option"=>
			array(
				array("label"=>"All Customers","link"=>$zulu->link_page('client'),"icon"=>"far fa-users"),
				array("label"=>"Customer Groups","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'client_role'))),"icon"=>"far fa-sitemap"),
			)
		);
		$MENU_CONFIG_web[] = array("label"=>"Sales","link"=>"#","icon"=>"far fa-money-bill","option"=>
			array(
				($class_user->has_perm('sale_edit')?array("label"=>"New Sale","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"):NULL),
				array("label"=>"All Sales","link"=>$zulu->link_page('sale'),"icon"=>"far fa-tag"),
				array("label"=>"Parked Sales","link"=>$zulu->link_page('sale',array("query"=>array('View'=>'parked'))),"icon"=>"far fa-pause"),
				array("label"=>"Report","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-pie"),
				array("label"=>"Abandoned","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'abandon'))),"icon"=>"far fa-ban"),
				array("label"=>"Coupons","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'coupon'))),"icon"=>"far fa-ticket-alt"),
			)
		);
		$MENU_CONFIG_web[] = array("label"=>"Subscriptions","link"=>"#","icon"=>"far fa-sync-alt","option"=>
			array(
				//array("label"=>"New Subscription","link"=>$zulu->link_page('renew',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"Subscriptions","link"=>$zulu->link_page('renew'),"icon"=>"far fa-sync-alt"),
				array("label"=>"Templates","link"=>$zulu->link_page('renew',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-pencil"),
				//array("label"=>"Report","link"=>$zulu->link_page('renew',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-line"),
			)
		);
		/*$MENU_CONFIG_web[] = array("label"=>"Products","link"=>"#","icon"=>"far fa-cube","option"=>
			array(
				array("label"=>"New Product","link"=>$zulu->link_page('product',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Products","link"=>$zulu->link_page('product'),"icon"=>"far fa-cube"),
                array("label"=>"Brands","link"=>$zulu->link_page('product', ['query'=>['Action'=>'brand']]),"icon"=>"far fa-tag"),
                array("label"=>"Reviews","link"=>$zulu->link_page('product', ['query'=>['Action'=>'review']]),"icon"=>"far fa-star"),
			)
		);*/
		$MENU_CONFIG_web[] = array("label"=>"Listings","link"=>$zulu->link_page('listing'),"icon"=>"far fa-horse");
	}

	$MENU_CONFIG_web[] = array("label"=>"Data","link"=>"#","icon"=>"far fa-database","option"=>
		array(
			["label"=>"Locations","link"=>$zulu->link_page('location'),"icon"=>"far fa-map-marker"],
			["label"=>"Regions","link"=>$zulu->link_page('location',['query'=>['Action'=>'region']]),"icon"=>"far fa-map-marker"],
			["label"=>"Currencies","link"=>$zulu->link_page('currency'),"icon"=>"far fa-dollar-sign"],
			["label"=>"Price List","link"=>$zulu->link_page('price_list'),"icon"=>"far fa-clipboard-list"],
		)
	);

	$MENU_CONFIG_web[] = ["label"=>"Pages","link"=>$zulu->link_page('post',['query'=>['type'=>'page']]),"icon"=>"far fa-browser"];
	$MENU_CONFIG_web[] = ["label"=>"Menus","link"=>$zulu->link_page('post',['query'=>['type'=>'menu']]),"icon"=>"far fa-mouse-pointer"];
	/*if($WEBSITE_feature['slider']) {
		$MENU_CONFIG_web[] = ["label"=>"Sliders","link"=>$zulu->link_page('post',['query'=>['type'=>'slider']]),"icon"=>"far fa-laptop"];
	}
	if($WEBSITE_feature['gallery']) {
		$MENU_CONFIG_web[] = ["label"=>"Gallery","link"=>$zulu->link_page('post',['query'=>['type'=>'gallery']]),"icon"=>"far fa-image"];
		$MENU_CONFIG_web[] = ["label"=>"Gallery Categories","link"=>$zulu->link_page('post',['query'=>['type'=>'gallery_category']]),"icon"=>"far fa-th"];
	}
	if($WEBSITE_feature['news']) {
		$MENU_CONFIG_web[] = ["label"=>"News","link"=>$zulu->link_page('post',['query'=>['type'=>'news']]),"icon"=>"far fa-newspaper"];
	}
	if($WEBSITE_feature['testimonial']) {
		$MENU_CONFIG_web[] = ["label"=>"Testimonials","link"=>$zulu->link_page('post',['query'=>['type'=>'testimonial']]),"icon"=>"far fa-star"];
	}
	if($WEBSITE_feature['faq']) {
		$MENU_CONFIG_web[] = ["label"=>"FAQs","link"=>$zulu->link_page('post',['query'=>['type'=>'faq']]),"icon"=>"far fa-question-circle"];
	}*/
	$MENU_CONFIG_web[] = ["label"=>"Forms","link"=>$zulu->link_page('form_post',['query'=>['Action'=>'form']]),"icon"=>"far fa-comment"];

	if(MASTER_mode!='main') {
		$MENU_CONFIG_web[] = array("label"=>"Cloud Storage","link"=>"#","icon"=>"far fa-cloud","option"=>
			array(
				array("label"=>"New File","link"=>$zulu->link_page('file',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Folders","link"=>$zulu->link_page('file'),"icon"=>"far fa-folder"),
				//array("label"=>"User Groups","link"=>$zulu->link_page('group'),"icon"=>"far fa-lock"),
				array("label"=>"Quick Access","link"=>$zulu->link_page('file',array("query"=>array('Action'=>'quick'))),"icon"=>"far fa-bolt"),
			)
		);
	}

    $MENU_CONFIG_web['settings'] = array("label"=>"Settings","link"=>"#","icon"=>"far fa-cog","option"=>
        array(
            array("label"=>"Website","link"=>$zulu->link_page('website',['query'=>['Action'=>'site']]),"icon"=>"far fa-cog"),
        )
    );
    if($class_setting->data['ws_status'] == '0') {
        $MENU_CONFIG_web['settings']['option'][] = array("label"=>"Theme","link"=>$zulu->link_page('website',['query'=>['Action'=>'theme']]),"icon"=>"far fa-paint-brush");
    }
	if($class_website->config->program=='ZULUSHP') {
		$MENU_CONFIG_web['settings']['option'][] = array("label"=>"Modules","link"=>$zulu->link_page('module'),"icon"=>"far fa-plug");
	}
    $MENU_CONFIG_web['settings']['option'][] = array("label"=>"System","link"=>$zulu->link_page('setting'),"icon"=>"far fa-hdd");

	//-- Mode: WEB
	if(MASTER_mode=='web'&&$user_auth) {
		$zulu->nav->menu['main'][] = array("label"=>"Home","link"=>$zulu->link_page('index'),"icon"=>"far fa-home");
		foreach($MENU_CONFIG_web as $menu_item) {
			$zulu->nav->menu['main'][] = $menu_item;
		}
	}

	//-- Mode: MAIN
	if(MASTER_mode=='main'&&$user_auth) {
		$zulu->nav->menu['main'][] = array("label"=>"Dashboard","link"=>$zulu->link_page('index'),"icon"=>"far fa-tachometer");
		if($class_user->authorised->opt_client) {
		$zulu->nav->menu['main'][] = array("label"=>"Contacts","link"=>"#","icon"=>"far fa-users","option"=>
			array(
				array("label"=>"Leads","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'lead'))),"icon"=>"far fa-briefcase"),
				array("label"=>"Prospect","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'prospect'))),"icon"=>"far fa-question-circle"),
				array("label"=>"Clients","link"=>$zulu->link_page('client'),"icon"=>"far fa-users"),
				array("label"=>"Cancelled","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'cancel'))),"icon"=>"far fa-ban"),
				array("label"=>"Suppliers","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'supplier'))),"icon"=>"far fa-truck"),
				array("label"=>"Client Groups","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'client_role'))),"icon"=>"far fa-sitemap"),
			)
		);
		}
		if($class_user->authorised->opt_website) {
		$zulu->nav->menu['main'][] = array("label"=>"Website","link"=>"#","icon"=>"far fa-desktop","option"=>
			$MENU_CONFIG_web
		);
		}
		if($class_user->authorised->opt_sale) {
		$zulu->nav->menu['main'][] = array("label"=>"Sales","link"=>"#","icon"=>"far fa-money-bill","option"=>
			array(
				($class_user->has_perm('sale_edit')?array("label"=>"New Sale","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"):NULL),
				array("label"=>"All Sales","link"=>$zulu->link_page('sale'),"icon"=>"far fa-tag"),
				array("label"=>"Parked Sales","link"=>$zulu->link_page('sale',array("query"=>array('View'=>'parked'))),"icon"=>"far fa-pause"),
				array("label"=>"Report","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-line"),
				array("label"=>"Abandoned","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'abandon'))),"icon"=>"far fa-ban"),
				array("label"=>"Coupons","link"=>$zulu->link_page('sale',array("query"=>array('Action'=>'coupon'))),"icon"=>"far fa-ticket-alt"),
			)
		);
		}
		if($class_user->authorised->opt_sale_po) {
		$zulu->nav->menu['main'][] = array("label"=>"Purchase Orders","link"=>"#","icon"=>"far fa-truck","option"=>
		array(
			array("label"=>"New Order","link"=>$zulu->link_page('porder',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
			array("label"=>"All Orders","link"=>$zulu->link_page('porder'),"icon"=>"far fa-tag"),
			array("label"=>"Parked Sales","link"=>$zulu->link_page('porder',array("query"=>array('View'=>'parked'))),"icon"=>"far fa-pause"),
			array("label"=>"Report","link"=>$zulu->link_page('porder',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-line"),
		)
	);
		}
		if($class_user->authorised->opt_sell) {
		$zulu->nav->menu['main'][] = array("label"=>"Point of Sale","link"=>"#","icon"=>"far fa-shopping-basket","option"=>
			array(
				array("label"=>"Register","link"=>$zulu->link_page('sell'),"icon"=>"far fa-money-bill"),
				array("label"=>"Closures","link"=>$zulu->link_page('sell',['query'=>['Action'=>'closure']]),"icon"=>"far fa-balance-scale"),
				array("label"=>"Layouts","link"=>$zulu->link_page('sell',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-folder"),
			)
		);
		}
		if($class_user->authorised->opt_quote) {
		$zulu->nav->menu['main'][] = array("label"=>"Quotes","link"=>"#","icon"=>"far fa-file","option"=>
			array(
				array("label"=>"New Quote","link"=>$zulu->link_page('quote',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Quotes","link"=>$zulu->link_page('quote'),"icon"=>"far fa-folder-open"),
				array("label"=>"Quote Templates","link"=>$zulu->link_page('quote',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-copy"),
			)
		);
		}
		if($class_user->authorised->opt_product) {
		$zulu->nav->menu['main'][] = array("label"=>"Products","link"=>"#","icon"=>"far fa-cube","option"=>
			array(
				array("label"=>"New Product","link"=>$zulu->link_page('product',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Products","link"=>$zulu->link_page('product'),"icon"=>"far fa-cube"),
                array("label"=>"Brands","link"=>$zulu->link_page('product', ['query'=>['Action'=>'brand']]),"icon"=>"far fa-tag"),
				array("label"=>"Special Scheduling","link"=>$zulu->link_page('product',array("query"=>array('Action'=>'special'))),"icon"=>"far fa-tags"),
			)
		);
		}
		if($class_user->authorised->opt_project) {
			$zulu->nav->menu['main'][] = array("label"=>"Projects","link"=>"#","icon"=>"far fa-briefcase","option"=>
				array(
					array("label"=>"New Project","link"=>$zulu->link_page('project',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
					array("label"=>"All Projects","link"=>$zulu->link_page('project',['query'=>['Sort'=>'0']]),"icon"=>"far fa-briefcase"),
					array("label"=>"Calendar","link"=>$zulu->link_page('project',['query'=>['View'=>'month']]),"icon"=>"far fa-calendar"),
					array("label"=>"Project Templates","link"=>$zulu->link_page('project',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-pencil"),
				)
			);
		}
		if($class_user->authorised->opt_task) {
			$zulu->nav->menu['main'][] = array("label"=>"Tasks","link"=>"#","icon"=>"far fa-tasks","option"=>
				array(
					array("label"=>"Daily Tasks","link"=>$zulu->link_page('daily'),"icon"=>"far fa-check-square"),
					array("label"=>"All Tasks","link"=>$zulu->link_page('task'),"icon"=>"far fa-tasks"),
					array("label"=>"Recurring Tasks","link"=>$zulu->link_page('task',['query'=>['Action'=>'recur']]),"icon"=>"far fa-sync-alt"),
				)
			);
			$zulu->nav->menu['main'][] = array("label"=>"Billables","link"=>$zulu->link_page('bill'),"icon"=>"far fa-credit-card");
		}
		//Support Tickets
		if($class_user->authorised->opt_support) {
			$zulu->nav->menu['main'][] = array("label"=>"Support Tickets","link"=>"#","icon"=>"far fa-life-ring","option"=>
				array(
					array("label"=>"New Ticket","link"=>$zulu->link_page('support',array("query"=>array('Action'=>'edit_ticket'))),"icon"=>"far fa-plus-circle"),
					array("label"=>"All Tickets","link"=>$zulu->link_page('support',['query'=>['Sort'=>'0']]),"icon"=>"far fa-briefcase"),
					//array("label"=>"Test Client Side","link"=>$zulu->link_page('support',array("query"=>array('Action'=>'client_messages'))),"icon"=>"far fa-pencil"),
				)
			);
		}
		if($class_user->authorised->opt_production) {
			$zulu->nav->menu['main'][] = array("label"=>"Production","link"=>"#","icon"=>"far fa-industry","option"=>
				array(
					array("label"=>"Assemblies","link"=>$zulu->link_page('production',array("query"=>array('Action'=>'assembly'))),"icon"=>"far fa-wrench"),
				)
			);
		}
		if($class_user->authorised->opt_renew) {
			$zulu->nav->menu['main'][] = array("label"=>"Subscriptions","link"=>"#","icon"=>"far fa-sync-alt","option"=>
				array(
					array("label"=>"New Subscription","link"=>$zulu->link_page('renew',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
					array("label"=>"All Subscriptions","link"=>$zulu->link_page('renew'),"icon"=>"far fa-sync-alt"),
					array("label"=>"Subscription Types","link"=>$zulu->link_page('renew',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-pencil"),
				)
			);
		}
		if($class_user->authorised->opt_book) {
		$zulu->nav->menu['main'][] = array("label"=>"Bookings","link"=>"#","icon"=>"far fa-ticket-alt","option"=>
			array(
				array("label"=>"Redeem","link"=>$zulu->link_page('book',array("query"=>array('Action'=>'redeem'))),"icon"=>"far fa-barcode"),
				array("label"=>"Events","link"=>$zulu->link_page('book'),"icon"=>"far fa-flag"),
				array("label"=>"Calendar","link"=>$zulu->link_page('book',array("query"=>array('Action'=>'calendar'))),"icon"=>"far fa-calendar"),
				array("label"=>"Ticketing","link"=>$zulu->link_page('book',array("query"=>array('Action'=>'ticket'))),"icon"=>"far fa-ticket-alt"),
				array("label"=>"Reporting","link"=>$zulu->link_page('book',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-line"),
				array("label"=>"Settings","link"=>$zulu->link_page('book',array('query'=>array('Action'=>'setting'))),"icon"=>"far fa-cog"),
			)
		);
		}
		if($class_user->authorised->opt_schedule) {
			$zulu->nav->menu['main'][] = array("label"=>"Booking Schedule","link"=>"#","icon"=>"far fa-calendar","option"=>
				array(
					array("label"=>"New Booking","link"=>$zulu->link_page('schedule',array("query"=>array('Action'=>'book_edit'))),"icon"=>"far fa-plus-circle"),
					array("label"=>"Schedule","link"=>$zulu->link_page('schedule',array("query"=>array('View'=>'3'))),"icon"=>"far fa-list"),
					array("label"=>"Assets","link"=>$zulu->link_page('schedule',array("query"=>array('Action'=>'asset'))),"icon"=>"far fa-flag"),
					//array("label"=>"Reporting","link"=>$zulu->link_page('book',array("query"=>array('Action'=>'report'))),"icon"=>"far fa-chart-line"),
					array("label"=>"Settings","link"=>$zulu->link_page('setting',array('query'=>array('Tab'=>'schedule'))),"icon"=>"far fa-cog"),
				)
			);
		}
		if($class_user->authorised->opt_mail) {
		$zulu->nav->menu['main'][] = array("label"=>"Messaging","link"=>$zulu->link_page('rule'),"icon"=>"far fa-envelope","option"=>
			array(
				array("label"=>"Send Message","link"=>$zulu->link_page('client',array("query"=>array('Action'=>'email'))),"icon"=>"far fa-envelope-open"),
				array("label"=>"Scheduling","link"=>$zulu->link_page('rule'),"icon"=>"far fa-clock"),
				array("label"=>"Sent Messages","link"=>$zulu->link_page('rule',['query'=>['Action'=>'queue']]),"icon"=>"far fa-upload"),
				array("label"=>"Email Templates","link"=>$zulu->link_page('rule',array("query"=>array('Action'=>'template'))),"icon"=>"far fa-th-large"),
				array("label"=>"Sequence Templates","link"=>$zulu->link_page('rule',array("query"=>array('Action'=>'sequence_template'))),"icon"=>"far fa-archive"),
				array("label"=>"Email Signature","link"=>$zulu->link_page('rule',array("query"=>array('Action'=>'signature_edit'))),"icon"=>"far fa-pencil"),
			)
		);
		}
		if($class_user->authorised->opt_procedure) {
		$zulu->nav->menu['main'][] = array("label"=>"Procedures","link"=>"#","icon"=>"far fa-list-ol","option"=>
			array(
				array("label"=>"All Procedures","link"=>$zulu->link_page('procedure'),"icon"=>"far fa-bars"),
				array("label"=>"Categories","link"=>$zulu->link_page('procedure',array("query"=>array('Action'=>'category'))),"icon"=>"far fa-th"),
			)
		);
		}
		if($class_user->authorised->opt_staff) {
			$zulu->nav->menu['main'][] = array("label"=>"Timesheet","link"=>$zulu->link_page('timesheet'),"icon"=>"far fa-clock");
		}
		if($class_user->authorised->opt_form_post) {
		$zulu->nav->menu['main'][] = array("label"=>"Forms","link"=>"#","icon"=>"far fa-file-alt","option"=>
			array(
				array("label"=>"Submissions","link"=>$zulu->link_page('form_post'),"icon"=>"far fa-bars"),
				array("label"=>"Manage","link"=>$zulu->link_page('form_post',array("query"=>array('Action'=>'form'))),"icon"=>"far fa-folder"),
			)
		);
		}
		$zulu->nav->menu['main'][] = array("label"=>"Cloud Storage","link"=>"#","icon"=>"far fa-cloud","option"=>
			array(
				array("label"=>"New File","link"=>$zulu->link_page('file',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Folders","link"=>$zulu->link_page('file'),"icon"=>"far fa-folder"),
				//array("label"=>"User Groups","link"=>$zulu->link_page('group'),"icon"=>"far fa-lock"),
				array("label"=>"Quick Access","link"=>$zulu->link_page('file',array("query"=>array('Action'=>'quick'))),"icon"=>"far fa-bolt"),
			)
		);
		$zulu->nav->menu['main'][] = array("label"=>"Objects","link"=>"#","icon"=>"far fa-cube","option"=>
			array(
				array("label"=>"Objects","link"=>$zulu->link_page('object'), "icon"=>"far fa-cube"),
				array("label"=>"New Object Type","link"=>$zulu->link_page('object', array("query"=>array('Action'=>'edit_type'))),"icon"=>"far fa-plus-circle"),
				array("label"=>"All Object Types","link"=>$zulu->link_page('object', array("query"=>array('Action'=>'type'))),"icon"=>"far fa-cube"),

			)
		);
	}
}

//-- Root Links
if($user_auth) {
	if($class_user->authorised->role == 'admin' || $class_user->authorised->role == 'client') { //admin / client to see only
		$sub_link = array(
			array("label"=>"New User","link"=>$zulu->link_page('user',array("query"=>array('Action'=>'edit'))),"icon"=>"far fa-plus-circle"),
			array("label"=>"All Users","link"=>$zulu->link_page('user'),"icon"=>"far fa-users"),
			array("label"=>"Groups","link"=>$zulu->link_page('user',['query'=>['Action'=>'user_group']]),"icon"=>"far fa-sitemap"),
		);
	}
	if($class_user->authorised->role == 'admin') { //admin to see only
		$sub_link[] = array("label"=>"User Roles","link"=>$zulu->link_page('user',array('query'=>array('Action'=>'user_role'))),"icon"=>"far fa-quote-left");
	}
	if($class_user->authorised->role == 'staff') { //admin to see only
		$sub_link[] = array("label"=>"My Settings","link"=>$zulu->link_page('user',array('query'=>array('Action'=>'edit'))),"icon"=>"far fa-pencil");
	}
	$zulu->nav->menu['main'][] = array("label"=>"Users","link"=>"#","icon"=>"far fa-user","option"=>
		$sub_link
	);
	$zulu->nav->menu['main'][] = array("label"=>"Logout","link"=>$zulu->link_page('login',['query'=>['Action'=>'logout']]),"icon"=>"far fa-sign-out fa-fw");
}

//Global JS
$zulu->template->js_file[] = TPL_rel.'assets/site.js';
$zulu->template->js_file[] = TPL_rel.'assets/jquery-ui.js';

//-- Fancybox
$zulu->template->js_file[] = TPL_rel."assets/fancybox/lib/jquery.mousewheel-3.0.6.pack.js";
$zulu->template->js_file[] = TPL_rel."assets/fancybox/source/jquery.fancybox.pack.js?v=2.1.5";
$zulu->template->css_file[] = TPL_rel."assets/fancybox/source/jquery.fancybox.css?v=2.1.5";

//-- Checkbox Multiselect
if(isset($zulu->config->select_all) && $zulu->config->select_all) {
	$zulu->template->jquery[] = "
		$(\".toggle-input\").click(function() {
			$(\"input[type='checkbox'].action\").each(function() {
				if(!$(this).is(\":disabled\")) {
					$(this).prop(\"checked\", !$(this).prop(\"checked\"));
				}
			});
			panel_cbox_toggle();
		});
	";
}

//-- Load FLOT Chart JS
if(isset($zulu->config->chart_flot_js) && $zulu->config->chart_flot_js) {
	$zulu->template->js_file[] = "../bower_components/flot/excanvas.min.js";
	$zulu->template->js_file[] = "../bower_components/flot/jquery.flot.js";
	$zulu->template->js_file[] = "../bower_components/flot/jquery.flot.pie.js";
	$zulu->template->js_file[] = "../bower_components/flot/jquery.flot.resize.js";
	$zulu->template->js_file[] = "../bower_components/flot/jquery.flot.time.js";
	$zulu->template->js_file[] = "../bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js";
}
