<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'listing');
define(PAGE_name,'Listings');

$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

$zulu->template->head = "";
$zulu->template->body = "";

if(!$class_user->authorised->opt_product) {
	$zulu->notification_set("Sorry, you are not authorised to use the ".PAGE_name." area.",2);
	header("Location: ".$zulu->link_page("index"));exit;
}

$currency_symbol = LOCALE_currency_symbol;

if(PAGE_action==NULL) {	//grid page

	//-- Action for bulk selection
	if($_POST&&$_POST['execute']!=NULL) {
		switch ($_POST['execute']) {
			case 'publish':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['hide'=>'0']);
				}
				$zulu->notification_set("Selected products were published successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			case 'hide':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['hide'=>'1']);
				}
				$zulu->notification_set("Selected products were hidden successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
		}
	}

	$zulu->template->config->select_all = true;
	$form_edit = new form;

    function edit_bt($id,$data) {
		global $zulu,$class_user,$class_product;

		$buttons = [
            "<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'edit')))."\" title=\"Edit\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>"
        ];
		if((MASTER_mode=='web'&&$class_website->config->shop)||$class_user->authorised->opt_website) {
			$buttons[] = "<a target=\"_blank\" href=\"".$class_product->product_url($id,$data->slug,true)."\" title=\"View\" class=\"btn btn-info btn-xs\"><i class=\"fas fa-external-link-alt\"></i> View</a>";
		}

		return "<div class='row-options'>".implode(" ", $buttons)."</div>";
	}

	if(!isset($_GET['tab']) || !$_GET['tab']) {
		$_GET['tab'] = 'active';
	}

	//Load Products / Categories
	$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
	$table_column[] = array("",array('class'=>array('col-image center')));
	$table_column[] = array("Name");
	$table_column[] = array("Member");
	$table_column[] = array("Listing Type");
	$table_column[] = array("Price");
	$table_column[] = array("Start Date");
	$table_column[] = array("End Date");

	$table_row[-1] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$query = Products::query();
	$query->where('product.client_id', '>', 0)
		->where('product.status', 1)
		->join('product_listing', 'product.id', '=', 'product_listing.product_id')
		->select('product.*', 'time_close', 'time_start');

	if(isset($_GET['Search']) && $_GET['Search'] != NULL) {
		$query->where('name', 'like', "%".$_GET['Search']."%");
	}

	switch ($_GET['tab']) {
		case 'sold':
			$query->where('product.sale_record_id', '>', 0)
				->where('product.live', 0)
				->orderBy('time_close', 'desc');
			break;
		case 'unsold':
			$query->where('product.sale_record_id', '<=', 0)
				->where('product.live', 0)
				->orderBy('time_close', 'desc');
			break;
		case 'active':
		default:
			$query->where('time_close', '>', time())
				->where('product.live', 1)
				->orderBy('time_close', 'asc');
			break;
	}

	$query_all = clone $query;
	$product_total_count = $query_all->count();

	$query->take(MAX_per_page)
		->offset($start);
	$products = $query->get();

	$i = 0;
	foreach($products as $product) {
		$link = $zulu->link_page(PAGE_file, array('query'=>array('id'=>$product->id,'Action'=>'edit')));
		$images = $product->images();
		$product_image = trim($images['main']) != null ? $zulu->thumb($images['main'],"w=40&h=40&far=1&bg=FFFFFF") : null;

		$listing = $product->listing;
		$client = $product->client;
		$type = ucwords($product->listing_type);
		$client_name = $client ? "<a href='".$zulu->link_page('client',array('query'=>array('id'=>$client->id,'Action'=>'edit')))."'>".$client->nameFull()."</a>" : '';

		$name_extra = [];
		if($product->hide) {
			$name_extra[] = "<span class=\"opt opt-danger opt-bord\"><span class='fas fa-exclamation-triangle'></span> Hidden</span>";
		}
		$table_row[$i] = array("content" => array(
			array($form_edit->input_html("checkbox", "action[".$product->id."]", 1, array('checked'=>($_POST['action'][$product->id]>0?true:false), 'class'=>array('action'))), array('class'=>array('action-field'))),
			array("<a href=\"".$link."\">".($product_image!=NULL?"<img src=\"{$product_image}\" class=\"product-image\" alt=\"product image\" />":"<span class=\"fas ".$class_product->product_icon($product->type)."\" style=\"font-size:20px\"></span>")."</a>",array('class'=>array('col-image center'))),
			array("<a href=\"".$link."\"><b>".stripslashes($product->name)."</b></a> ".(count($name_extra)>0?implode(" ",$name_extra):null).edit_bt($product->id, $product)),
			[$client_name],
			[$type],
			array($currency_symbol.number_format($product->price)),
			[$zulu->dateTimezone($listing->time_start, 'H:ia d/m/Y')." NZT"],
			[$zulu->dateTimezone($listing->time_close, 'H:ia d/m/Y')." NZT"],
		));
		$i++;
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>false));
	$pagination = $zulu->pagination($_GET['Pg'],['count'=>$product_total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
	$zulu->nav->title = "Listings";

	$zulu->nav->breadcrumb['All Listings'] = [];

	$tabs = [
		'active'	=>	'Active',
		'sold'		=>	'Sold',
		'unsold'	=>	'Unsold',
	];

	$zulu->template->jquery[] = "
	$(\".bt-data-bulk\").click(function() {
		var field = $(this).data('field');
		var val = prompt(\"Enter a new value...\");
		if(val) {
			$('.input-' + field).val(val);
		}
		return false;
	});

	$(\"a[rel='toggle-input']\").click(function() {
		$(\"input[type='checkbox'].action\").each(function() {
			if(!$(this).is(\":disabled\")) {
				$(this).prop(\"checked\", !$(this).prop(\"checked\"));
			}
		});
		panel_cbox_toggle();
		return false;
	});";

}

if(PAGE_action=='delete') { //delete product
	//-- MOVED main function into index function as DO
	$product_data = $class_product->product_data(array('id'=>PAGE_id));

	if($class_product->delete(PAGE_id)) {
		$zulu->notification_set("Product removed successfully.",1);
		header("Location: ".(isset($_GET['Return'])?urldecode($_GET['Return']):$zulu->link_page('product',array('query'=>array('ProductRoot'=>$product_data['parent_id'])))));
		exit;
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}

if(PAGE_action=='edit') { //edit page
	$form_edit = new form;

	if(PAGE_id<1) {
		header("Location: ".$zulu->link_page(PAGE_file));
		exit;
	}

	$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

	$product = Products::find($id);
	$listing = $product->listing;

	//$product->getFamilyData();
	/*var_dump($product->getFamilyData());
	exit;*/

	if(!$listing) {
		header("Location: ".$zulu->link_page(PAGE_file));
		exit;
	}

	if(isset($_GET['do'])) {
		if($_GET['do'] == 'disable') {
			$product->hide = 1;
			$product->save();
			$zulu->notification_set("Listing disabled.",1);
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do'])));
			exit;

		} elseif($_GET['do'] == 'enable') {
			$product->hide = 0;
			$product->save();
			$zulu->notification_set("Listing enabled.",1);
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do'])));
			exit;

		} elseif($_GET['do'] == 'delete') {
			$product->delete();
			$zulu->notification_set("Listing deleted.",1);
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do'])));
			exit;

		} elseif($_GET['do'] == 'del_bid') {
			$bid = $listing->bids()->where('id',$_GET['bid'])->first();
			if($bid) {
				$last_bid = $listing->lastBid();
				$bid->delete();
				if($last_bid->id == $bid->id) {
					$last_bid = $listing->lastBid();
					$listing->price_bid = $last_bid->bid_amount;
		            $listing->save();
					$product->price = $last_bid->bid_amount;
	                $product->save();
				}
				$zulu->notification_set("Bid deleted.",1);
			}
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do','bid'])));
			exit;

		} elseif($_GET['do'] == 'addon_enable') {
			$listing->{$_GET['addon']} = 1;
			$listing->save();
			$zulu->notification_set("Add-on enabled.",1);
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do','addon'])));
			exit;

		} elseif($_GET['do'] == 'addon_disable') {
			$listing->{$_GET['addon']} = 0;
			$listing->save();
			$zulu->notification_set("Add-on disabled.",1);
			header("Location: ".$zulu->link_page(PAGE_file, array('self'=>true, 'filter'=>['do','addon'])));
			exit;

		}
	}

	$meta = $product->metaArray($product->meta);
	$currency_code = $product->currencyCode();
	$location_name = $product->locationFullText();
	$current_views = $product->statViews();
	$current_watchers = $product->statWatchers();
	$bids = $listing->bids()->latest()->get();
	$current_bids = count($bids);

	$image_data = $class_product->image_data($id);

	if($product->listing_type == 'auction') {
		$table_column = $table_row = [];
		$table_column[] = array("User");
		$table_column[] = array("Amount");
		$table_column[] = array("Time");
		$table_column[] = array("");
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		foreach($bids as $bid) {
			$bid_client = $bid->client;
			$client_name = $bid_client ? "<a href='".$zulu->link_page('client',array('query'=>array('id'=>$bid_client->id,'Action'=>'edit')))."'>".$bid_client->nameFull()."</a>" : '';

			$buttons = [
				['link'=>$zulu->link_page(PAGE_file,array('self'=>true, 'query'=>['do'=>'del_bid','bid'=>$bid->id])),'label'=>'','icon'=>'times','class'=>'danger','class_append'=>['confirm'],'title'=>'Delete Bid']
			];

			$table_row[] = array("content" => array(
				[$client_name],
				[$currency_symbol.number_format($bid->bid_amount)],
				[$zulu->date($bid->stat_add, 'H:i:s d/m/Y')." NZT"],
				[$zulu->button_render($buttons)],
			));
		}
		$bid_table = $zulu->table_render($table_row,0,array('class'=>'','html_id'=>'','data_table'=>false));
	}

	$table_column = $table_row = [];
	$table_column[] = array("Add-on");
	$table_column[] = array("Status");
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);
	$addon_options = PriceList::addonArray();
	foreach($addon_options as $key=>$addon) {

		$buttons = [
			['link'=>$zulu->link_page(PAGE_file,array('self'=>true, 'query'=>['do'=>'del_bid','bid'=>$bid->id])),'label'=>'','icon'=>'times','class'=>'danger','class_append'=>['confirm'],'title'=>'Delete Bid']
		];

		if($listing->{$key}) {
			$status = "<span class=\"opt opt-success opt-bord\"><span class='fas fa-check'></span> Enabled</span> <a href='".$zulu->link_page(PAGE_file, array('self'=>true, 'query'=>['do'=>'addon_disable','addon'=>$key]))."' class='btn btn-xs btn-danger pull-right'><span class='fas fa-times'></span> Disable</a>";
		} else {
			$status = "<span class=\"opt opt-danger opt-bord\"><span class='fas fa-times'></span> Disabled</span> <a href='".$zulu->link_page(PAGE_file, array('self'=>true, 'query'=>['do'=>'addon_enable','addon'=>$key]))."' class='btn btn-xs btn-success pull-right'><span class='fas fa-check'></span> Enable</a>";
		}

		$table_row[] = array("content" => array(
			[$addon['title']],
			[$status],
		));
	}
	$addon_table = $zulu->table_render($table_row,0,array('class'=>'','html_id'=>'','data_table'=>false));

	$zulu->nav->breadcrumb[stripslashes($product->name)] = array();
	$zulu->nav->title = "Edit Listing";

}
