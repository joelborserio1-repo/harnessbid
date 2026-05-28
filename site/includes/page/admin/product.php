<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'product');
define(PAGE_name,'Products');

$zulu->nav->breadcrumb['Products'] = array("link"=>$zulu->link_page('product'));

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

	//-- Do: Delete
	if($_GET['Do']=='delete') {
		$product_data = $class_product->product_data(array('id'=>PAGE_id,'field'=>['parent_id','type']));
		if($class_product->delete(PAGE_id)) {
			$zulu->notification_set(($product_data['type']=='product'?"Product":"Category")." removed successfully.",1);
			header("Location: ".(isset($_GET['Return'])?urldecode($_GET['Return']):$zulu->link_page('product',array('self'=>true,'filter'=>['Do','id']))));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}


	//-- Action for bulk selection
	if($_POST&&$_POST['execute']!=NULL) {
		switch ($_POST['execute']) {
			case 'delete':
				foreach($_POST['action'] as $id=>$val) {
					if(!$checkret = $class_product->delete($id)) {
						$error_log[] = "Failed to delete product ID #".$id;
					} else {

					}
				}
				$zulu->notification_set("Selected products were removed successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
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
			case 'special':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['special'=>'1']);
				}
				$zulu->notification_set("Selected products were marked on special successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			case 'unspecial':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['special'=>'0']);
				}
				$zulu->notification_set("Selected products were taken off special successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			case 'new':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['new'=>'1']);
				}
				$zulu->notification_set("Selected products were marked as new successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
			case 'unnew':
				foreach($_POST['action'] as $id=>$val) {
					$class_product->product_edit($id,['new'=>'0']);
				}
				$zulu->notification_set("Selected products were taken off new successfully.",1);
				header("Location: ".$_SERVER['HTTP_REFERER']);
				exit;
		}
	}

	//-- Bulk Update POST
	if($_POST['action']=='bulk') {
		foreach($_POST['sku'] as $key=>$val) {
			$data = [
				'sku'	=>	$_POST['sku'][$key],
				'name'	=>	$_POST['name'][$key],
				'price'	=>	$_POST['price'][$key],
				'price_special'	=>	$_POST['price_special'][$key],
			];
			$process = $class_product->product_edit($_SESSION['product']['id_array'][$key],$data);
		}

		$zulu->notification_set("Bulk products updated.",1);
		header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
		exit;
	}

	$zulu->template->config->select_all = true;
	$form_edit = new form;
	$zulu->template->js_code[] = "
      	$(document).ready(function() {
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
			});
		});
	";

	/*function edit_bt($id,$data) {
		global $zulu,$class_user,$class_product;
		//$product_data = $class_product->product_data(array("id"=>$id));

		$prebt = [];
		if((MASTER_mode=='web'&&$class_website->config->shop)||$class_user->authorised->opt_website) {
			$prebt[] = "<a target=\"_blank\" href=\"".$class_product->product_url($id,$data['slug'],true)."\" title=\"Preview\" class=\"btn btn-info btn-xs\"><i class=\"fas fa-laptop\"></i> Preview</a>";
		}

		if($data['type']=='product' && $data['type_variant']!=2) {
			$prebt[] = "<a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$id,'Action'=>'duplicate')))."\" title=\"Duplicate\" class=\"btn btn-warning btn-xs\"><i class=\"fas fa-clone\"></i> Duplicate</a>";
		}

		return "
            <a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$id,'Action'=>'edit')))."\" title=\"Edit\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>
			".implode(" ",$prebt)."
			<a href=\"".$zulu->link_page('product',array('self'=>true,'query'=>array('id'=>$id,'Do'=>'delete')))."\" title=\"Delete\" class=\"btn btn-danger confirm-delete btn-xs\"><i class=\"fas fa-times\"></i></a>
		";
	}*/

    function edit_bt($id,$data) {
		global $zulu,$class_user,$class_product;

		$buttons = [
            "<a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$id,'Action'=>'edit')))."\" title=\"Edit\" class=\"btn btn-primary btn-xs\"><i class=\"fas fa-edit\"></i> Edit</a>"
        ];
		if((MASTER_mode=='web'&&$class_website->config->shop)||$class_user->authorised->opt_website) {
			$buttons[] = "<a target=\"_blank\" href=\"".$class_product->product_url($id,$data['slug'],true)."\" title=\"View\" class=\"btn btn-info btn-xs\"><i class=\"fas fa-external-link-alt\"></i> View</a>";
		}
		if($data['type']=='product' && $data['type_variant']!=2) {
			$buttons[] = "<a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$id,'Action'=>'duplicate')))."\" title=\"Duplicate\" class=\"btn btn-warning btn-xs confirm\"><i class=\"fas fa-clone\"></i> Duplicate</a>";
		}
        $buttons[] = "<a href=\"".$zulu->link_page('product',array('self'=>true,'query'=>array('id'=>$id,'Do'=>'delete')))."\" title=\"Delete\" class=\"btn btn-danger confirm-delete btn-xs\"><i class=\"fas fa-times\"></i> Delete</a>";

		return "<div class='row-options'>".implode(" ", $buttons)."</div>";
	}

	//Load Current Category Data
	$category_data = $class_product->product_data(array('id'=>$class_product->root_id,'field'=>['id','name','parent_id','type']));
	if($class_product->root_id==0) {
		$has_cat_data = $class_product->product_data(array('field'=>['id'],'type'=>'category','parent_id'=>0));
		$has_cat = ($has_cat_data['id']>0?true:false);
	}

	$class_product->category->name = $category_data['name'];
	if($class_product->root_id>0) {
		$_SESSION['product']['last_root'] = $class_product->root_id;
	} elseif($class_product->root_id<0) {
		$all = true;
	} else {
		unset($_SESSION['product']['last_root']);
	}
	$true_category_data = $class_product->product_data(array('id'=>$class_product->root_id,'type'=>'category','field'=>['id']));
	if($class_product->root_id<=0&&count($true_category_data)<=0&&$_GET['Search']==NULL) {
		$all = true;
	}

	//Options
	if($category_data['type']=='product') {
		$product_option = true;

		$options = $class_product->attribute_option($category_data['id']);
		$combos = $class_product->attribute_option_combinations($category_data['id']);
		$combos_nice = $class_product->attribute_option_combinations($category_data['id'],['label'=>true]);

        $count_option = count($options);
		$count_possible = count($combos);
	}
	$has_attribute = $class_product->attribute_is(['id'=>$category_data['id']]);

	//Load Products / Categories
	$table_column[] = array($form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
	$table_column[] = array("",array('class'=>array('col-image center')));
	$table_column[] = array("Name");
	$table_column[] = array("SKU");
	$table_column[] = array("Price");
	$table_column[] = array("Stock");
	//$table_column[] = array("Actions",array('class'=>array('text-right')));

	$bulk_mode = ($_GET['Do']=='bulk'?true:false);
	if($bulk_mode) {
        $table_column = [];
		//$table_column[] = array("",array('class'=>array('col-image center')));
		$table_column[] = array("Name <a href=\"#\" class=\"bt-data-bulk\" data-field=\"title
		\"><i class=\"fas fa-bars\"></i></a>");
		$table_column[] = array("SKU <a href=\"#\" class=\"bt-data-bulk\" data-field=\"sku\"><i class=\"fas fa-bars\"></i></a>");
		$table_column[] = array("Price <a href=\"#\" class=\"bt-data-bulk\" data-field=\"price\"><i class=\"fas fa-bars\"></i></a>");
		$table_column[] = array("Special Price <a href=\"#\" class=\"bt-data-bulk\" data-field=\"prices\"><i class=\"fas fa-bars\"></i></a>");
		$table_column[] = array("Actions",array('class'=>array('text-right')));
	}

	$table_row[-1] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

    if(isset($_GET['brand']) && $_GET['brand'] > 0) {
        $brand = ProductBrand::find($_GET['brand']);
        if($brand != null) {
            $filter['brand_id'] = $brand->id;
            $filter_all['brand_id'] = $brand->id;
            $all = true;
        }
    }

	if($_GET['Search'] != NULL) {
		$filter['search'] = $db->escape_string($_GET['Search']);
		$filter_all['search'] = $db->escape_string($_GET['Search']);
		if($class_product->root_id==0) {
			//
		} else {
			//-- searching with in a root cat
			$class_product->category_children($class_product->root_id);
			$root_id_filter = $class_product->root_id;
			if(count($class_product->category_child)>0) {
				$root_id_filter = [];
				foreach($class_product->category_child as $cid=>$cval) {
					$root_id_filter[$cid] = $cval;
				}
				$root_id_filter[$class_product->root_id] = $class_product->root_id;
			}
		}
	} else {
		$root_id_filter = $class_product->root_id;
	}

	//Stock Locations
	$stock_locations = $class_product->location_data();
	$class_product->vars->has_location = count($stock_locations);

	//SQL Start
	$start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
	$filter_all['field'] = ['product.id AS id'];
	$filter['row_start'] = $start;
	$filter['row_limit'] = MAX_per_page;
	$filter['field'] = ['sku','price','price_special','type','type_variant','name','hide','new','special','product.id AS id','brand_id'];
	//$filter['field'] = 'sku,price,price_special,type,type_variant,name,id';
	//$filter['test'] = true;
	if($all) {
		$product_row = $class_product->product_data(['type'=>'product','type_variant'=>[0,1],'sort'=>'type DESC, name ASC','sys'=>'0']+$filter);
		$product_data_total = $class_product->product_data(['type'=>'product','type_variant'=>[0,1],'sys'=>'0']+$filter_all);
	} else {
		$product_row = $class_product->product_data(array(($product_option?'parent_id':'root_id')=>$root_id_filter,'sort'=>'type DESC, name ASC','sys'=>'0')+$filter);
		$product_data_total = $class_product->product_data(array(($product_option?'parent_id':'root_id')=>$root_id_filter,'sys'=>'0')+$filter_all);
	}
	$product_total_count = count($product_data_total);
	$i = 0;

	foreach($product_row as $row) {
		$class_product->vars->data = $row;
		$link = ($row['type']=='category'?$class_product->category_url(array('root_id'=>$row['id'])):$zulu->link_page('product',array('query'=>array('id'=>$row['id'],'Action'=>'edit'))));
		if($row['type']=='product') {
			$meta = $class_product->product_meta($row['id']);
			$class_product->vars->meta = $meta;
			$stock = $class_product->stock_label($meta['stock']['value']);
			$image = $class_product->image_data($row['id']);
			if(trim($image['main'])!=NULL) {
				$product_image = $zulu->thumb($image['main'],"w=40&h=40&far=1&bg=FFFFFF");
			}
		}
		$price = $class_product->price();

		if($bulk_mode) {

			if(!$_POST['sku'][$i]) { //create default data
				$_POST['sku'][$i] = $row['sku'];
				$_POST['name'][$i] = $row['name'];
				$_POST['price'][$i] = $row['price'];
				if($row['price_special']>0) {
					$_POST['price_special'][$i] = $row['price_special'];
				}
			}

			$_SESSION['product']['id_array'][$i] = $row['id'];

			$table_row[$i] = array("content" => array(
			//array("<a href=\"".$link."\">".($row['type']=='product'?($product_image!=NULL?"<img src=\"{$product_image}\" class=\"product-image\" alt=\"product image\" />":"<span class=\"fas ".$class_product->product_icon($row['type'])."\" style=\"font-size:20px\"></span>"):"<span class=\"fas ".$class_product->product_icon($row['type'])."\" style=\"font-size:20px\"></span>")."</a>",array('class'=>array('col-image center'))),
			array($form_edit->input_html('input',"name[".$i."]",$_POST['name'][$i],['class'=>['input-title'],'placeholder'=>($product_option?"(Auto) ".$class_product->name():NULL)]),['class'=>['w30']]),
			array($form_edit->input_html('input',"sku[".$i."]",$_POST['sku'][$i],['class'=>['input-sku']]),['class'=>['w20']]),
			array($form_edit->input_html('input',"price[".$i."]",$_POST['price'][$i],['class'=>['input-price'],'placeholder'=>'$0.00']),['class'=>['w20']]),
			array($form_edit->input_html('input',"price_special[".$i."]",$_POST['price_special'][$i],['class'=>['input-prices'],'placeholder'=>'No special ($0.00)']),['class'=>['w20']]),
			['<a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$row['id'],'Return'=>urlencode($zulu->link_page(PAGE_file,['self'=>true]))]]).'" class="btn btn-primary btn-circle btn-xs"><i class="fas fa-edit"></i></a> <a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'delete','id'=>$row['id'],'Return'=>urlencode($zulu->link_page(PAGE_file,['self'=>true]))]]).'" class="btn btn-danger btn-circle btn-xs"><i class="fas fa-times"></i></a>',['class'=>'text-right']]
			));

			//if variant parent then we unset the price boxes
			if($row['type_variant']==1) {
				$table_row[$i]['content'][2] = '<span class="opt opt-grey">-</span>';
				$table_row[$i]['content'][3] = '<span class="opt opt-grey">-</span>';
			}
		} else {
			if($row['hide']==1) {
				$name_extra[] = "<span class=\"opt opt-danger opt-bord\"><span class='fas fa-exclamation-triangle'></span> Hidden</span>";
			}
			if($row['special']==1) {
				$name_extra[] = "<span class=\"opt opt-success opt-bord\"><span class='fas fa-star'></span> Special</span>";
			}
			if($row['new']==1) {
				$name_extra[] = "<span class=\"opt opt-success opt-bord\"><span class='fas fa-plus'></span> New</span>";
			}
			if($row['type_variant']==1) {
				$name_extra[] = "<span class=\"opt opt-grey\"><a href=\"".$zulu->link_page(PAGE_file,['query'=>['ProductRoot'=>$row['id']]])."\"><i class=\"fas fa-cubes\"></i> <b>".$class_product->child_count($row['id'])."</b> variants</a></span>";
			}
			$table_row[$i] = array("content" => array(
				array(($row['type']=='product'?$form_edit->input_html("checkbox","action[".$row['id']."]",1,array('checked'=>($_POST['action'][$row['id']]>0?true:false),'class'=>array('action'))):NULL),array('class'=>array('action-field'))),
				array("<a href=\"".$link."\">".($row['type']=='product'?($product_image!=NULL?"<img src=\"{$product_image}\" class=\"product-image\" alt=\"product image\" />":"<span class=\"fas ".$class_product->product_icon($row['type'])."\" style=\"font-size:20px\"></span>"):"<span class=\"fas ".$class_product->product_icon($row['type'])."\" style=\"font-size:20px\"></span>")."</a>",array('class'=>array('col-image center'))),
				array("<a href=\"".$link."\"><b>".$class_product->name()."</b></a> ".(count($name_extra)>0?implode(" ",$name_extra):null).edit_bt($row['id'],$row)),
				array($row['sku']),
				array(($row['type']=='product'?($price['price_count']>0?"From ":NULL).$currency_symbol.$zulu->dollar($price['price'],true).($price['special']?" <span title=\"Product is on special, normally ".($price['rrp_low']>0?'from':NULL)." ".$currency_symbol.$zulu->dollar(($price['rrp_low']>0?$price['rrp_low']:$price['rrp']),true)."\" class=\"opt opt-danger text-bold\"><i class=\"fas fa-ticket-alt\"></i></span>":NULL):NULL)),
				array($stock),
				//array(edit_bt($row['id'],$row),array('class'=>array('right')))
			));
		}
		$i++;
		unset($stock,$product_image,$name_extra,$class_product->vars->meta);
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>false));
	$pagination = $zulu->pagination($_GET['Pg'],['count'=>$product_total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
	$zulu->nav->title = "Products";

	//Build Tree of Links
	if($product_option) {
		unset($zulu->nav->breadcrumb);
		$zulu->nav->breadcrumb[stripslashes($category_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$category_data['id']]]));
		$zulu->nav->breadcrumb['Variants'] = array();
		$page_variants = true;
	} else {
		if($all) {
			if($brand != null) {
                $zulu->nav->breadcrumb[$brand->title] = [];
            } else {
                $zulu->nav->breadcrumb['All Products'] = [];
            }
		} else {
			$class_product->category_breadcrumb($class_product->root_id);
		}
	}

}
if(PAGE_action=='duplicate') {
	$result = $class_product->product_duplicate(PAGE_id);
	if($result['success']) {
		$zulu->notification_set("Product duplicated successfully.",1);
		header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$result['id'],'Action'=>'edit'))));
		exit;
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
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
if(PAGE_action=='stock') { //stock page
	$zulu->nav->title = "Adjust Stock";
	$zulu->nav->breadcrumb['Adjust Stock'] = array();
	$form_edit = new form;


	//-- Bulk Update Stock Location Setup
	if($class_setting->data['stock_location']>0) {
		$stock_locations = $class_product->location_data();
		foreach($stock_locations as $r) {
			$loc_array[$r['id']] = stripslashes($r['name']);
		}
	}


	//-- Post adjust
	if($_POST['action']=='stock_adjust') {
		$reason = $db->escape_string($_POST['reason']);
		$reason = (trim($reason)==NULL?"Bulk Adjustment":$reason);
		$loc_id = $_POST['location_id'];
		foreach($_POST['stock'] as $key=>$val) {
			$stock = trim($val);
			if(is_numeric($stock)) {
				$data = [
					'value'		=>	$stock,
					'note'		=>	$reason,
					'location_id'	=>	$loc_id,
				];
				$result = $class_product->stock_adjust($key,$data);
			}
		}

		$zulu->notification_set("Stock was adjusted on the products selected.",1);
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}

	//Load Current Category Data
	if($class_product->root_id>0) {
		$category_data = $class_product->product_data(array('id'=>$class_product->root_id));
		$class_product->category->name = $category_data['name'];
	} else {
		$class_product->category->name = "All Products";
		$all = true;
	}

	//Options
	if($category_data['type']=='product') {
		$product_option = true;

		unset($zulu->nav->breadcrumb);
		$zulu->nav->breadcrumb[stripslashes($category_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$category_data['id']]]));
		$zulu->nav->breadcrumb['Adjust Stock Options'] = array();
	}

	//Load Products / Categories
	$table_column[] = array("Name");
	$table_column[] = array("SKU");
	$table_column[] = array("Price");
	$table_column[] = array("Current Stock");
	$table_column[] = array("Adjust");
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$filter = [];
	if($_GET['Search'] != NULL) {
		$filter['search'] = $db->escape_string($_GET['Search']);
	}
	if($all) {
		$product_row = $class_product->product_data(array('sort'=>'type DESC, name ASC')+$filter);
	} else {
		$product_row = $class_product->product_data(array('root_id'=>$class_product->root_id,'sort'=>'type DESC, name ASC')+$filter);
	}
	foreach($product_row as $row) {
		$class_product->vars->data = $row;
		$link = ($row['type']=='category'?$class_product->category_url(array('root_id'=>$row['id'])):$zulu->link_page('product',array('query'=>array('id'=>$row['id'],'Action'=>'edit'))));
		$price = $class_product->price();
		if($row['type']=='product') {
			$meta = $class_product->product_meta($row['id']);
			$stock = $class_product->stock_label($meta['stock']['value']);
			if($row['type_variant']==1) {
				$show_stock = "<a class=\"btn btn-default btn-xs\" href=\"".$zulu->link_page(PAGE_file,['query'=>['ProductRoot'=>$row['id']],'self'=>true])."\" target=\"_blank\"><i class=\"fa fa-cube\"></i> Edit Variant Stock</a>";
			} else {
				$show_stock = $form_edit->input_html('number',"stock[".$row['id']."]",$_POST['stock'][$row['id']],['placeholder'=>'0','limit'=>['max'=>10000,'min'=>-10000]]);
			}
		} else {
			$show_stock = "<span class='opt opt-grey'>-</span>";
		}
		$table_row[] = array("content" => array(
			array("<a href=\"".$link."\">".$class_product->name()."</a>"),
			array($row['sku']),
			array(($row['type']=='product'?"$".number_format($price['price'],2):NULL)),
			array($stock),
			array($show_stock)
		));
		unset($stock,$show_stock);
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>array('sort'=>[[0,'ASC']])));
}
if(PAGE_action=='edit') { //edit page
	$form_edit = new form;
	$zulu->template->css_file[] = "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
	$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
	$zulu->template->js_file[] = TPL_rel."assets/smart.find.js";

	$zulu->template->jquery[] = "
	$(\".date\").datepicker({ dateFormat: \"dd/mm/yy\" });";
	//JS for adding feilds to media section
	$zulu->template->js_code[] .= '
	$(document).ready(function() {
		var max_fields      = 10; //maximum input boxes allowed
		var wrapper         = $(".input_fields_wrap"); //Fields wrapper
		var add_button      = $(".add_field_button"); //Add button ID

		var x = 1; //initlal text box count
		$(add_button).click(function(e){ //on add input button click
		 console.log("TEST");
			e.preventDefault();
			if(x < max_fields){ //max input box allowed
				x++; //text box increment
				$(wrapper).append(\'<div class="form-group video_removeble"><div class="row"><div class="col-sm-3">'.$form_edit->input_html('input','video_urls[]',$video_url,['custom'=>['placeholder'=>'Enter a video URL']]).'</div><div class="col-sm-1"><button type="button" class="remove_field btn btn-default"><i class="fas fa-minus"></i></button></div></div></div>\'); //add input box
			}
		});

		$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
			e.preventDefault(); $(this).closest(".video_removeble").remove(); x--;
		})
	});';

	//--adjust stock
	if($_POST['do'] == 'stock') {
		if(isset($_POST['submit_sub'])) {
			$_POST['stock'] *= -1;
		}
		$data = [
			'value'		=>	$_POST['stock'],
			'note'		=>	$_POST['note']
		];
		if($_POST['location_id']>0) {
			$data['location_id'] = $_POST['location_id'];
		}
		$result = $class_product->stock_adjust(PAGE_id,$data);

		$zulu->notification_set("Stock was adjusted successfully.",1);
		header("Location: ".$zulu->link_page(PAGE_file,array("query"=>array("Action"=>'edit','id'=>PAGE_id))));
		exit;
	}

	if($_GET['Type']=='category') {
		$class_product->product_is = false;
		$type = 'Category';
	} else {
		$class_product->product_is = true;
		$type = 'Product';
	}
	if($_GET['Method']=='SetImageMain'&&isset($_GET['Image'])) {
		$class_product->image_set(PAGE_id,$db->escape_string($_GET['Image']));
		$zulu->notification_set("Main image was set to '".$_GET['Image']."'.",1);
	}
	if($_GET['Method']=='DeleteImage'&&isset($_GET['Image'])) {
		$class_product->image_delete(PAGE_id,$db->escape_string($_GET['Image']));
		$zulu->notification_set("'".$_GET['Image']."' was removed.",1);
	}
	if($_GET['Method']=='category_add') {
		$class_product->category_add(PAGE_id,$_GET['cat_id']);
		echo 1;
		exit;
	}
	if($_GET['Method']=='category_delete') {
		$class_product->category_remove(PAGE_id,$_GET['cat_id']);
		echo 1;
		exit;
	}
	if($_GET['Do']=='FeatureSort') {
		$array = explode(",",$_GET['Array']);
		$i = 0;
		foreach($array as $item_id) {
			$class_product->feature_edit($item_id,['sort'=>$i]);
			$i++;
		}
		exit;
	}
	if(PAGE_id<1) {
		$id = 0;
		$new = true;

		//Cat
		if($_GET['CatSet']>0) {
			$_POST['parent_id'] = $_GET['CatSet'];
		}

		//Check type
		$zulu->nav->title = "New ".$type;

		//Product DATA
		if($_GET['ProductRoot']>0) {
			$parent_data = $class_product->product_data(array('id'=>$_GET['ProductRoot']));
		}
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

		$product_data = $class_product->product_data(array('id'=>$id));
		$product_meta = $class_product->product_meta($id);
		$product_meta_strip = $zulu->meta_array($product_meta);
		if($product_data['parent_id']>0) {
			$parent_data = $class_product->product_data(array('id'=>$product_data['parent_id']));
			$parent_meta = $zulu->meta_array($class_product->product_meta($product_data['parent_id']));
		}
		$type = ($product_data['type']=='category'?"Category":"Product");
		$current_stock = $product_meta['stock']['value'];

		if(!$_POST) {
			foreach($product_data as $key=>$val) {
				$_POST[$key] = stripslashes($val);
			}

			foreach($product_meta as $key=>$val) {
				$_POST['meta'][$val['field']] = $val['value'];
				if($val['field']=='category') {
					$pdata = $class_product->product_data(['id'=>$val['value'],'field'=>['name']]);
					$_POST['category'][$val['value']] = $pdata['name'];
				}
			}

			$_POST['stock_adjust'] = unserialize($_POST['meta']['stock_adjust']);
			$_POST['sa_prod_name'] = $class_product->name($_POST['stock_adjust'][0]['product_id']);

			$_POST['meta']['video'] = explode(",",$_POST['meta']['video']);
			if($_POST['meta']['opt_stock_eta']>0) {
				$_POST['meta']['opt_stock_eta'] = $zulu->dateDecode($_POST['meta']['opt_stock_eta']);
			} else {
				unset($_POST['meta']['opt_stock_eta']);
			}
		}
		$image_data = $class_product->image_data($id);

		//-- Compile locations & stock holdings at location
		$stock_locations = $class_product->location_data();
		$stock_location_count = count($stock_locations);
		foreach($stock_locations as $stock_loc) {
			$loc_stock = $class_product->stock($id,['location_id'=>$stock_loc['id']]);
			$opt_location[$stock_loc['id']] = $stock_loc['code']." ".$stock_loc['name'];
			$loc_stock_count[$stock_loc['id']] = $loc_stock['level'];
			$loc_cumulative += $loc_stock['level'];
		}
		$opt_location[0] = "Default";

		//-- IF Stock locations, then add a default stock one too - i.e. if location was added after stock held
		if($stock_location_count>0) {
			$loc_stock = $class_product->stock($id);
			if($loc_cumulative!=$loc_stock['level']) {
				$loc_stock_count[0] = $loc_stock['level']-$loc_cumulative;
			}
		}
		$table_column[] = array("Date",array('class'=>array('')));
		if($stock_location_count>0) {
			$table_column[] = array("Location",array('class'=>array('')));
		}
		$table_column[] = array("Adjustment",array('class'=>array('')));
		$table_column[] = array("Note",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('center')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);
		$data_row = $class_product->stock_data(array('product_id'=>PAGE_id,'sort'=>'stat_add DESC','limit'=>20));
		foreach($data_row as $row) {

			$val = ($row['value']<=0?"<span class=\"opt opt-danger\"><span class=\"fas fa-chevron-down\"></span> ".$row['value']."</span>":"<span class=\"opt opt-success\"><span class=\"fas fa-chevron-up\"></span> ".$row['value']."</span>");

			$actions = "<a href=\"".$zulu->link_page($row['object'],array('query'=>array('id'=>$row['object_id'],'Action'=>'edit')))."\" title=\"Link\" class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-link\"></i></a>";

			if($stock_location_count>0) {
				$table_row[] = array("content" => array(
					array(zulu::date($row['stat_add'],"d/m/Y g:ia")),
					array(($opt_location[$row['location_id']]!=NULL?$opt_location[$row['location_id']]:"<span class='opt opt-grey'>Default</span>")),
					array($val),
					array("<span title=\"".stripslashes($row['note'])."\">".zulu::shorten(stripslashes($row['note']),30)."</span>"),
					array(($row['object_id']>0?"<a href=\"".$zulu->object_link($row['object'],$row['object_id'])."\"><span class=\"fas fa-link\"></span> ".$zulu->vars->link_title."</a>":NULL),array('class'=>array('center')))
				));
			} else {
				$table_row[] = array("content" => array(
					array(zulu::date($row['stat_add'],"d/m/Y g:ia")),
					array($val),
					array("<span title=\"".stripslashes($row['note'])."\">".zulu::shorten(stripslashes($row['note']),30)."</span>"),
					array(($row['object_id']>0?"<a href=\"".$zulu->object_link($row['object'],$row['object_id'])."\"><span class=\"fas fa-link\"></span> ".$zulu->vars->link_title."</a>":NULL),array('class'=>array('center')))
				));
			}
		}
		$zulu->template->body->panel_stock = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>false));
		unset($table_column,$table_row);

		if($stock_location_count>0) {
			$table_column[] = array("Location",array('class'=>array('')));
			$table_column[] = array("Level",array('class'=>array('')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);
			$data_row = $class_product->stock_data(array('product_id'=>PAGE_id,'sort'=>'stat_add DESC','limit'=>20));
			foreach($loc_stock_count as $sub_loc=>$sub_lvl) {
				$table_row[] = array("content" => array(
					array(($opt_location[$sub_loc]!=NULL?$opt_location[$sub_loc]:"<span class='opt opt-grey'>Default</span>")),
					array($class_product->stock_label($sub_lvl))
				));
			}
			$zulu->template->body->panel_stock_location = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>false));
			unset($table_column,$table_row);
		}

		$table_column[] = array("Statistic",array('class'=>array('')));
		$table_column[] = array("Value",array('class'=>array('')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$child_data = $class_product->product_data(['parent_id'=>PAGE_id,'field'=>['id']]);
		if(count($child_data)>0) {
			foreach($child_data as $child) {
				$child_meta = $zulu->meta_array($class_product->product_meta($child['id']));
				$stock = $class_product->stock($child['id']);
				$price = $class_product->price($child['id']);
				$stock_value += ($stock['level']*$price['price']);

				$stock_sold = $zulu->table_data('sale_line',0,['join'=>"sale ON sale_line.sale_id = sale.id",'where'=>["sale.status = 1","user_id = ".$class_user->authorised->id,"sale_line.product_id = '".$child['id']."'"],'sort'=>'sale.id DESC','field'=>['sale.id','sale_line.total']]);
				foreach($stock_sold as $ss) {
					if($ss['total']>0) {
						$stock_sold_num += $ss['total'];
						$stock_sold_qty += $ss['quantity'];
					}
				}
				$stock_received_this = 0;
				$stock_in = $zulu->table_data('product_stock',0,['where'=>["product_id = '".$child['id']."'","value > 0","is_return = 0"]]);
				foreach($stock_in as $si) {
					$stock_received_total += $si['value'];
					$stock_received_this += $si['value'];
				}
				if($product_meta_strip['price_base_method']=='percent') {
					$cost_total += $product_data['price_base']*($child_meta['price_weight']/100);
				} else {
					$cost_total += ($child_data['price_base']*$stock_received_this);
				}
			}
		} else {
			$stock = $class_product->stock(PAGE_id);
			$price = $class_product->price(PAGE_id);
			$stock_value += ($stock['level']*$price['price']);

			$stock_sold = $zulu->table_data('sale_line',0,['join'=>"sale ON sale_line.sale_id = sale.id",'where'=>["sale.status = 1","user_id = ".$class_user->authorised->id,"sale_line.product_id = '".PAGE_id."'"],'sort'=>'sale.id DESC','field'=>['sale.id','sale_line.total']]);
			foreach($stock_sold as $ss) {
				if($ss['total']>0) {
					$stock_sold_num += $ss['total'];
					$stock_sold_qty += $ss['quantity'];
				}
			}

			$stock_in = $zulu->table_data('product_stock',0,['where'=>["product_id = '".PAGE_id."'","value > 0","is_return = 0"]]);
			foreach($stock_in as $si) {
				$stock_received_total += $si['value'];
			}
		}

		//-- Gross profit
		/*if($product_data['price_base']>0) {
			//-- use inputted price base to get GP
			$base_price = $product_data['price_base'];
			$cost_total = $stock_received_total*$base_price;
			$gross_profit = $stock_sold_num-$cost_total;
		} else {
			//-- use total stock received to get GP
			$data_query = $zulu->table_data('product_stock',0,['where'=>['product_id = '.PAGE_id,'value > 0'],'field'=>'SUM(value) AS stock_total','first'=>true]);
			//$base_price = $_POST['meta']['price_base'];
			//echo $base_price."<BR>".$product_data['price_base'];*/
			//--123
		//}

		//--123
		if($product_data['type_variant']==0) {
			$base_price = $product_data['price_base'];
			$cost_total = $stock_received_total*$base_price;
			$gross_profit = $stock_sold_num-$cost_total;
		} elseif($product_data['type_variant']==1) {
			$gross_profit = $stock_sold_num-$cost_total;
		} elseif($product_data['type_variant']==2) {
			$base_price = $product_data['price_base'];
			$cost_total = $stock_received_total*$base_price;
			if($parent_meta['price_base_method']=='percent'&&$product_meta_strip['price_weight']>0) {
				$cost_total = ($parent_data['price_base']*($product_meta_strip['price_weight']/100));//($stock_received_total*($parent_data['price_base']*($product_meta_strip['price_weight']/100)));
			}
			$gross_profit = $stock_sold_num-$cost_total;
		}

		if($gross_profit!=0&&$stock_sold_num!=0)
			$gp_margin = number_format((100*($gross_profit/$stock_sold_num)),0);
		else
			$cp_margin = '0';

		$table_row[] = array("content" => array(
			array('<b>Stock on Hand</b>'),
			array($current_stock),
		));
		$table_row[] = array("content" => array(
			array('&nbsp;&nbsp;Total Value'),
			array($class_sale->currency_symbol.$zulu->dollar($stock_value)),
		));
		$table_row[] = array("content" => array(
			array('<b>Value of Stock Sold</b>'),
			array($class_sale->currency_symbol.$zulu->dollar($stock_sold_num)),
		));
		$table_row[] = array("content" => array(
			array('&nbsp;&nbsp;Total Cost'),
			array($class_sale->currency_symbol.$zulu->dollar($cost_total)." ".$form_edit->icon_help('Based on total units received.')),
		));
		$table_row[] = array("content" => array(
			array('&nbsp;&nbsp;Gross Profit'),
			array(($gross_profit<0?"<b><span class=\"font-bold opt opt-danger\">":"<b><span class=\"font-bold opt opt-success\">").$class_sale->currency_symbol.$zulu->dollar($gross_profit)." ".$gp_margin."%".($gross_profit<0?"</span></b>":"</span></b>")),
		));

		$zulu->template->body->panel_report = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','data_table'=>false));

		$has_attribute = $class_product->attribute_is($id);

		unset($table_column,$table_row);
		$table_column[] = array("Title",array('class'=>array('')));
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('center')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_product->feature_data(['product_id'=>$id]);
		foreach($data_row as $row) {
			$actions = "<a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$row['id'],'Action'=>'feature_edit')))."\" title=\"Edit\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a> <a href=\"".$zulu->link_page('product',array('query'=>array('id'=>$row['id'],'Action'=>'feature_delete')))."\" title=\"Remove\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>";
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($zulu->date($row['stat_add'],"d/m/Y")),
				array($actions,array('class'=>array('right')))
			),"data"=>['feature-id'=>$row['id']]);
		}
		$zulu->template->feature_table = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'feature_list','data_table'=>false,'tbody'=>['id'=>'sortable-rows']));
		if(count($data_row)<1) $zulu->template->feature_table = "<p><i>This product has no features added to it.</i></p>";
	}

	if($_POST['meta']['video']==NULL) $_POST['meta']['video'] = [''];
	foreach($_POST['meta']['video'] as $v_key=>$video_url) {
		$video_url_html .= '
		<div class="form-group '.($v_key>0?"video_removeble":NULL).'">
			<div class="row">
				<div class="col-sm-3">
					'.$form_edit->input_html('input','video_urls[]',$video_url,['custom'=>['placeholder'=>'Enter a video URL']]).'
				</div>
				<div class="col-sm-1">
					'.($v_key>0?'
					<button type="button" class="remove_field btn btn-default"><i class="fas fa-minus"></i></button>':'
					<button type="button" class="add_field_button btn btn-default" ><i class="fas fa-plus"></i></button>').'
				</div>
			</div>
		</div>';
	}

	//Parent Info
	$cat_preload = [];
	if($parent_data['type']=='product') {
		$product_option = true;
		unset($zulu->nav->breadcrumb);
		$zulu->nav->breadcrumb[stripslashes($parent_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$parent_data['id']]]));
		$zulu->nav->breadcrumb['Options'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['ProductRoot'=>$parent_data['id']]]));
	}
	if($class_product->has_children(PAGE_id)&&!$new) {
		$has_options = true;
	}
	if($new) {
		$zulu->nav->breadcrumb['New '.$type] = array();
	} else {
		//Config
		$zulu->nav->breadcrumb['Edit '.$type] = array();
		$zulu->nav->breadcrumb[stripslashes($product_data['name'])] = array();
		$zulu->nav->title = "Edit ".$type;
	}

	//-- Load category html
	foreach($_POST['category'] as $cat_id=>$cat_title) {
		$cat_preload[] = "<span class=\"cat-".$cat_id."\">".($cat_id>0?"<a href=\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$cat_id]])."\" target=\"_blank\">".stripslashes($cat_title)."</a>":"Root")." <a class=\"bt-del-cat\" data-id=\"".$cat_id."\" href=\"#\"><button class=\"btn btn-danger btn-circle btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a></span> ";
	}

	//-- Stock control?
	$stock_control = (!$new&&$product_data['type']!='category'?true:false);

	//Load Image JS & Assets
	if($new) {
		if($_POST['product_temp_folder']==NULL) {
			$temp_folder = "product_".date("Ymd")."_".$zulu->serial(8);
			$_POST['product_temp_folder'] = $temp_folder;
		} else {
			$temp_folder = $_POST['product_temp_folder'];
			$post_temp = true;
		}
		$main_path = 'temp/'.$temp_folder.'/';
	} else {
		$main_path = 'product/'.$id.'/';
	}

	@mkdir($class_file->file_root."/../".$main_path);
	$class_file->uploadifive_new("image",['preview'=>true,'post'=>['action'=>'product_image','product_id'=>PAGE_id,'path'=>$main_path],'setting'=>['multi'=>true,'queueSizeLimit'=>8,'event'=>['complete'=>'']]]);

	//Supplier
	$supplier_array = [''=>"No Supplier (Select...)"];
	$supplier_list = $class_client->client_data(['supplier'=>true,'field'=>['id','company','name_first','name_last']]);
	foreach($supplier_list as $supplier) {
		$supplier_array[$supplier['id']] = $class_client->client_name($supplier);
	}

	//Form Submit
	if($_POST['action']=='edit') {
		$form_edit->valid = true;
		if(($_POST['type']=='product' || $_POST['type']=='category')) {
			if(isset($_POST['price'])) {
				$_POST['price'] = str_replace([',','-'],'',$_POST['price']);
				$_POST['price_special'] = str_replace([',','-'],'',$_POST['price_special']);
			}
			if($form_edit->validate(array('name')) && $product_data['type_variant']!=2) {
				$zulu->notification_set("Please enter a name for your ".$_POST['type'].".",2);
				$form_edit->valid = false;
			}
			if(($_POST['type']=='option'||$_POST['type']=='product')&&$_POST['price_special']>0&&$_POST['price_special']>=$_POST['price']) {
				$zulu->notification_set("Please enter a special price that is lesser than the retail price.",2);
				$form_edit->valid = false;
			}
			//--clear parent_id
			/*if($_POST['type']=='product') {
				$_POST['parent_id'] = 0;
			}*/

			if($_GET['ProductRoot']>0&&!isset($_POST['parent_id'])) {
				$_POST['parent_id'] =  $db->escape_string($_GET['ProductRoot']);
			}
			if($_POST['parent_id']>0&&$class_product->type($_POST['parent_id'])=='product') {
				$variant = 2;
				$_POST['type_variant'] = 2;
			}
			if($_POST['parent_id']>0&&($_POST['type']=='category'||$product_data['type']=='category')) {
				$parent_tree = $class_product->category_tree($_POST['parent_id']);
				if(count($class_product->category_tree->tree)>0) {
					foreach($class_product->category_tree->tree as $sub_cat_id) {
						$sub_cat_data = $class_product->product_data(['id'=>$sub_cat_id,'field'=>['name']]);
						$slug_convert[] = $sub_cat_data['name'];
					}
				}
			}
			if($product_data['type']=='product' && $_POST['parent_id']>0 && $product_data['type_variant']!=2) {
				unset($_POST['parent_id']);
			}
			$slug_convert[] = $_POST['name'];

			/*$_POST['description'] = addslashes($_POST['description']);
			if($id>0) {
				$_POST['price'] = str_replace([',','-'],'',$_POST['price']);
				$_POST['price_special'] = str_replace([',','-'],'',$_POST['price_special']);
				$_POST['stat_update'] = time();
				$_POST['slug'] = $zulu->slug($slug_convert);

				$query = "UPDATE product SET ".$db->build(1,array('name','template','sort','parent_id','image','stat_update','description','price','price_special','sku','slug','feature'))." WHERE id = '".$id."'";
			} else {
				$_POST['price'] = str_replace([',','-'],'',$_POST['price']);
				$_POST['price_special'] = str_replace([',','-'],'',$_POST['price_special']);
				$_POST['stat_add'] = time();
				$_POST['stat_update'] = time();
				$_POST['user_id'] = $_SESSION['zl_user']['id'];
				$_POST['token'] = zulu::serial();
				$_POST['serial'] = zulu::serial(32);
				$_POST['type'] = $_POST['type'];
				if($_POST['name']!=NULL) {
					$_POST['slug'] = zulu::makeHT($_POST['name']);
				}
				if($variant==2) {
					$class_product->product_edit($_POST['parent_id'],['type_variant'=>1]);
				}

				$query = "INSERT INTO product ".$db->build(2,array('serial','template','token','user_id','parent_id','image','description','sku','price','price_special','type','type_variant','name','sort','stat_add','stat_update','slug','feature'));
			}*/

			if($form_edit->valid) {

				$data = [
					'name'	=>	addslashes($_POST['name']),
					'description'	=>	addslashes($_POST['description']),
					'slug'	=>	$zulu->slug($slug_convert),
					'type'	=>	$_POST['type'],
					'price'	=>	$_POST['price'],
					'price_special'	=>	$_POST['price_special'],
					'price_base'	=>	$_POST['price_base'],
					'template'	=>	$_POST['template'],
					'sort'	=>	$_POST['sort'],
					'parent_id'	=>	$_POST['parent_id'],
					'sku'	=>	$_POST['sku'],
					'feature'	=>	$_POST['feature'],
					'hide'	=>	$_POST['hide'],
					'new'	=>	$_POST['new'],
					'special' => $_POST['special'],
                    'brand_id'  =>  (isset($_POST['brand_id'])&&$_POST['brand_id']>0?$_POST['brand_id']:0),
					//'stock_product_quantity'	=>	$_POST['stock_product_quantity'],
				];

				if($id<=0) {
					$data['type_variant'] = $_POST['type_variant'];
					if($variant==2) {
						$class_product->product_edit($_POST['parent_id'],['type_variant'=>1]);
					}
				}

				$result = $class_product->product_edit($id,$data);
				$id = $result['id'];

				if($result['success']) {

					//-- videos
					foreach($_POST['video_urls'] as $key=>$val) {
						if($val=='') unset($_POST['video_urls'][$key]);
					}
					$_POST['meta']['video'] = implode(",",$_POST['video_urls']);

					//-- Categories
					if(count($_POST['category'])>0) {
						foreach($_POST['category'] as $catid=>$cattitle) {
							if($catid > 0) {
								$class_product->category_add($id,$catid);
							}
						}
					}

					//-- Digital
					$_POST['meta']['opt_stock_eta'] = $zulu->dateEncode($_POST['meta']['opt_stock_eta']);
					$_POST['meta']['xero_ignore'] = ($_POST['meta']['xero_ignore']>0?'1':'0');
					$_POST['meta']['digital'] = ($_POST['meta']['digital']>0?'1':'0');
					$_POST['meta']['quantity_group_stop'] = ($_POST['meta']['quantity_group_stop']>0?'1':'0');
                    $_POST['meta']['clearance'] = ($_POST['meta']['clearance']>0?'1':'0');
					if($_POST['meta']['height']!=NULL && $_POST['meta']['width']!=NULL && $_POST['meta']['depth']!=NULL) {
						$_POST['meta']['volume'] = ($_POST['meta']['height']*$_POST['meta']['width']*$_POST['meta']['depth'])/1000000;
					}
					if(count($_POST['meta'])>0) {
						foreach($_POST['meta'] as $key=>$val) {
							$zulu->meta_update("product",$id,$key,$zulu->entity($val));
						}
					}

					//-- Stock adjust
					if($_POST['stock_adjust'][0]['product_id']>0) {
						$zulu->meta_update("product",$id,'stock_adjust',serialize($_POST['stock_adjust']));
					}

					//-- temp product images?
					if($_POST['product_temp_folder']!=NULL) {
						$files_temp = glob($class_file->file_root."/../".$main_path."*.*");

						$main_path = 'product/'.$id.'/';
						$newdir = $class_file->file_root."/../".$main_path;
						@mkdir($newdir);

						foreach($files_temp as $file) {
							copy($file,$newdir.basename($file));
							@unlink($file);
						}
					}

					$zulu->notification_set(ucfirst($_POST['type'])." ".($id>0?"updated":"created")." successfully.",1);

					if(isset($_POST['submit'])) {
						header("Location: ".$zulu->link_page('product',array('query'=>array('Action'=>'edit','id'=>$id))));
					} else {
						header("Location: ".$zulu->link_page('product',array('query'=>array('ProductRoot'=>($_SESSION['product']['last_root']?$_SESSION['product']['last_root']:$_POST['parent_id'])))));
					}
					exit;
				} else {
					//echo $db->error;exit;
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}
	}

	//-- Jquery
	$zulu->template->jquery[] = "
	$(\".input-cat\").change(function() {
		$(\".bt-new-cat\").trigger('click');
		$(\".input-cat\").find(\":selected\").removeAttr(\"selected\");
		return false;
	});
	$(\".bt-new-cat\").click(function() {
		var cat_id = $(\".input-cat\").find(\":selected\").data('id');
		var cat_title = $(\".input-cat\").find(\":selected\").data('label');

		if(cat_id > 0) {
			$.get('".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Method'=>'category_add']])."&cat_id=' + cat_id,function(data) {
				var html = '<span class=\"cat-' + cat_id + '\"><a href=\"".$zulu->link_page('product',['query'=>['Action'=>'edit']])."&id=' + cat_id + '\" target=\"_blank\">' + cat_title + '</a> <a class=\"bt-del-cat\" data-id=\"'+cat_id+'\" href=\"#\"><button class=\"btn btn-danger btn-circle btn-xs\" type=\"button\"><i class=\"fas fa-times\"></i></button></a></span>".($new?"<input type=\"hidden\" name=\"category[' + cat_id + ']\" value=\"'  + cat_title + '\" />":NULL)."';
				$('.js-categories').append(html);
			});
		}

		return false;
	});
	".($new&&$_GET['ProductRoot']>0?"
	$(\".input-cat\").val(\"".$_GET['ProductRoot']."\");
	$(\".bt-new-cat\").trigger('click');
	":NULL)."
	$(document).on('click','.bt-del-cat',function() {
		var cat_id = $(this).data('id');
		var parent = $(this).parent();
		if(cat_id > 0) {
			$.get('".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Method'=>'category_delete']])."&cat_id=' + cat_id,function(data) {
				parent.remove();
			});
		}
		return false;
	});

	 $(\"#sortable-rows\").sortable({
		update: function(event, ui) {
			var srt = [];
			$(\"#sortable-rows\").children(\"tr\").each(function( index ) {
				srt.push($(this).data('feature-id'));
			});
			$.get(\"".$zulu->link_page(PAGE_file,['query'=>['Action'=>PAGE_action,'Do'=>'FeatureSort','id'=>PAGE_id]])."&Array=\" + srt,function(data) {
				console.log(data);
			});
		}
	});
	$(\"#sortable\").disableSelection();
	";

}
if(PAGE_action=='attribute') { //attribute page

	unset($zulu->nav->breadcrumb);
	$form_edit = new form;
	$zulu->nav->title = "Attributes";

	if($class_product->root_id<=0&&PAGE_id<=0) {
		$zulu->notification_set("Please select a product / category to set attributes.",2);
		header("Location: ".$zulu->link_page(PAGE_file));
		exit;
	}

	//Data
	$id = ($class_product->root_id?$class_product->root_id:PAGE_id);
	$data = $class_product->product_data(['id'=>$id]);
	$is_product = ($data['type']=='product'?true:false);

	$zulu->nav->breadcrumb[stripslashes($data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$id]]));
	$zulu->nav->breadcrumb['Attributes'] = array();

	$options = $class_product->attribute_option($data['id']);
	$combos = $class_product->attribute_option_combinations($data['id']);

	$count_option = count($options);
	$count_possible = count($combos);

	//Load Products / Categories
	$table_column[] = array("Name");
	$table_column[] = array("Options");
	$table_column[] = array("Visibility");
	$table_column[] = array("Actions",array('class'=>array('text-right')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$product_row = $class_product->attribute_data(array('product_id'=>$class_product->root_id));
	foreach($product_row as $row) {

		$opt_count = explode(",",$row['options']);
		$table_row[] = array("content" => array(
			array("<a href=\"".$zulu->link_page(PAGE_file,['Action'=>'attribute_option','ProductRoot'=>$class_product->root_id,"filter_attribute[]"=>$row['id']])."\">".$row['name']."</a>"),
			array($zulu->shorten($row['options'],50)."&nbsp;&nbsp;&nbsp;<span class=\"opt opt-grey\"><i class=\"fas fa-bars\"></i> ".count($opt_count)." Options</span>"),
			array(($row['input_hide']<=0?"<span class=\"opt opt-success\"><i class=\"fas fa-check\"></i> Visible</span>":"<span class=\"opt opt-grey\"><i class=\"fas fa-times\"></i> Hidden</span>")),
			array("<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'attribute_edit')))."\" title=\"Edit\" class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></a>
			<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$row['id'],'Action'=>'attribute_delete')))."\" title=\"Delete\" class=\"btn btn-danger confirm-delete btn-circle\"><i class=\"fas fa-times\"></i></a>",array('class'=>array('right')))
		));
		unset($stock,$product_image);
	}
	$has_attribute = (count($product_row)>0?true:false);

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','js_table'=>false,'data_table'=>false));
}
if(PAGE_action=='attribute_edit') { //attribute page
	$form_edit = new form;

	if($_GET['Do'] == 'LoadTable') {
		$html = $class_product->attribute_edit_table(PAGE_id,['input'=>$_GET['Input']]);
		echo $html;
		exit;
	}

	unset($zulu->nav->breadcrumb);
	if(PAGE_id<=0) {
		$id = $_GET['field']['product_id'];
		$data = $class_product->product_data(['id'=>$id]);

		if($data['id']<=0) {
			$zulu->notification_set("Please select a product / category to create attributes.",2);
			header("Location: ".$zulu->link_page(PAGE_file));
			exit;
		}

		$zulu->nav->breadcrumb[stripslashes($data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$data['id']]]));
		$zulu->nav->breadcrumb['Attributes'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'attribute','ProductRoot'=>$data['id']]]));
	} else {
		$id = PAGE_id;
		$data = $class_product->product_data(['id'=>$id]);
	}

	if(PAGE_id<1) {
		$id = 0;
		$new = true;

		$zulu->nav->breadcrumb['New Attribute'] = array();
		$zulu->nav->title = "New Attribute";

		$_POST['product_id'] = $_GET['field']['product_id'];
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

		$attribute_data = $class_product->attribute_data(array('id'=>$id));
		$product_data = $class_product->product_data(array('id'=>$attribute_data['product_id']));

		if(!$_POST) {
			foreach($attribute_data as $key=>$val) {
				$_POST[$key] = $val;
			}
		}

		$zulu->nav->breadcrumb[stripslashes($product_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$product_data['id']]]));
		$zulu->nav->breadcrumb['Attributes'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'attribute','ProductRoot'=>$product_data['id']]]));
		$zulu->nav->breadcrumb['Edit Attribute'] = array();
		$zulu->nav->breadcrumb[stripslashes($attribute_data['name'])] = array();
		$zulu->nav->title = "Edit Attribute";
	}

	$zulu->template->body_option = $class_product->attribute_edit_table($id,['input'=>$_POST['input']]);

	if($_POST['action'] == "edit") {
		$form_edit->valid = true;

		if($form_edit->validate(['name'])) {
			$zulu->notification_set("Please enter fields denoted *.",2);
			$form_edit->valid = false;
		}
		foreach($_POST['option']['name'] as $key=>$val) {
			if($val == '') unset($_POST['option']['name'][$key],$_POST['option']['price'][$key]);
		}
		foreach($_POST['option']['price'] as $key=>$val) {
			if($val == '') $val = 0;
			$_POST['option']['price'][$key] = str_replace(['$',','],'',sprintf("%.2f", $val));
		}
		$option_vals = implode(",",$_POST['option']['name']);
		$option_prices = implode(",",$_POST['option']['price']);
		if($option_vals=='') {
			$zulu->notification_set("Please specify attribute options; for example: Red,Green,Blue,Yellow",2);
			$form_edit->valid = false;
		}
		//Check if existing attribute has the same name - attribute names must be unique
		$attr_check = $class_product->attribute_data(array('product_id'=>$_POST['product_id'],'slug'=>$zulu->slug($_POST['name']),'first'=>true));
		if($attr_check['id'] > 0 && $attr_check['id'] != $id) {
			$zulu->notification_set("Please enter an attribute name that is unique to this product.",2);
			$form_edit->valid = false;
		}

		if($form_edit->valid) {

			$input = [
				'options'	=>	addslashes($option_vals),
				'options_price'	=>	$option_prices,
				'name'		=>	addslashes($_POST['name']),
				'product_id'	=>	$_POST['product_id'],
				'input_hide'	=>	$_POST['input_hide'],
				'input_sort'	=>	$_POST['input_sort'],
				'input'			=>	$_POST['input'],
				'option_ids'	=>	$_POST['option']['option_id']
			];

			$data = $class_product->attribute_edit($id,$input);

			if($data['success']) {
				$id = $data['id'];
				$zulu->notification_set("Attribute ".(!$new?"updated":"created")." successfully.",1);
				if($new) {
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'attribute_edit'))));
				} else {
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'attribute','ProductRoot'=>$input['product_id']))));
				}
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}

	$zulu->template->js_code[] = "
		$(document).ready(function() {
			var show_price = ".(in_array($_POST['input'],$class_product->config->attr_input_price)?"true":"false").";
			var show_price_array = ['".implode("','",$class_product->config->attr_input_price)."'];
			$(\"select[name='input']\").change(function() {
				var input = $(this).val();
				$('#attr-table').slideUp(300, function() {
					$.get('".$zulu->link_page(PAGE_file,['query'=>['id'=>PAGE_id,'Action'=>PAGE_action,'Do'=>'LoadTable']])."&Input='+input, function(data) {
						$('#attr-options').replaceWith(data);
						$('#attr-table').slideDown(300);
						$(\"#sortable-rows\").sortable();
					});
				});
				if(jQuery.inArray(input, show_price_array) !== -1) show_price = true;
				else show_price = false;
			});

			$(\"#sortable-rows\").sortable();
			$(\"body\").on('click','#row-add',function() {
				$(\"#attr-options\").append('<tr><td>".$form_edit->input_html('input','option[name][]')."</td>'+(show_price?'<td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[price][]')."</td>':'".(!$new?"<td></td>":NULL)."')+'<td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
			});
			$('body').on('click','.clear-row',function() {
				var trow = $(this).parent().parent();
				$(trow).find('input').val('');
				return false;
			});
			$('body').on('click','.remove-row',function() {
				var trow = $(this).parent().parent();
				$(trow).remove();
				return false;
			});
		});
	";
}
if(PAGE_action=='attribute_delete') { //delete attribute
	$attribute_data = $class_product->attribute_data(array('id'=>PAGE_id));
	$product_data = $class_product->product_data(array('id'=>$attribute_data['product_id']));

	if($product_data['user_id']!=$class_user->authorised->id) {
		$zulu->notification_set("This attribute is unavailable.",2);
	} else {
		if($class_product->attribute_delete(PAGE_id)) {
			$zulu->notification_set("Attribute '".$attribute_data['name']."' removed successfully.",1);
			header("Location: ".$zulu->link_page('product',['query'=>array('Action'=>'attribute','ProductRoot'=>$product_data['id'])]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
}
if(PAGE_action=='attribute_option') { //attribute options

	$zulu->template->js_code[] = "
      	$(document).ready(function() {
			$(\".bt-data-bulk\").click(function() {
				var field = $(this).data('field');
				var val = prompt(\"Enter a new value...\");
				if(val) {
					$('.input-' + field).val(val);
				}
				return false;
			});
			$(\".toggle-input\").click(function() {
				$(\"input[type='checkbox'].action\").each(function() {
					if(!$(this).is(\":disabled\")) {
						$(this).prop(\"checked\", !$(this).prop(\"checked\"));
					}
				});
			});
		});
	";

	unset($zulu->nav->breadcrumb);
	$form_edit = new form;
	$zulu->nav->title = "Generate Options";

	if($class_product->root_id<=0) {
		$zulu->notification_set("Please select a product / category to load options.",2);
		header("Location: ".$zulu->link_page(PAGE_file));
		exit;
	}

	//Data
	$id = $class_product->root_id;
	$data = $class_product->product_data(['id'=>$id]);
	$is_product = ($data['type']=='product'?true:false);
	$zulu->nav->breadcrumb[stripslashes($data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$id]]));
	$zulu->nav->breadcrumb['Option Combinations'] = array();

	$options = $class_product->attribute_option($data['id']);
	$combos = $class_product->attribute_option_combinations($data['id']);
	$combos_nice = $class_product->attribute_option_combinations($data['id'],['label'=>true]);


	if(count($combos)==1&&$combos[0]==NULL) {
		unset($combos);
	}

	$count_option = count($options);
	$count_possible = count($combos);

	//Load Products / Categories
	$table_column[] = array("Variant");
	$table_column[] = array("Name <a href=\"#\" class=\"bt-data-bulk\" data-field=\"title
	\"><i class=\"fas fa-bars\"></i></a>");
	$table_column[] = array("SKU <a href=\"#\" class=\"bt-data-bulk\" data-field=\"sku\"><i class=\"fas fa-bars\"></i></a>");
	$table_column[] = array("Stock <a href=\"#\" class=\"bt-data-bulk\" data-field=\"stock\"><i class=\"fas fa-bars\"></i></a>");
	$table_column[] = array("Price <a href=\"#\" class=\"bt-data-bulk\" data-field=\"price\"><i class=\"fas fa-bars\"></i></a>");
	$table_column[] = array("Special Price <a href=\"#\" class=\"bt-data-bulk\" data-field=\"prices\"><i class=\"fas fa-bars\"></i></a>");
	$table_column[] = array("Create ".$form_edit->input_html("checkbox","selectall",1,array("class"=>['toggle-input'])),array('class'=>array('')));
	$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	$i = 0;
	foreach($combos as $key=>$row) {
		$serialize = serialize($row);
		foreach($combos_nice[$key] as $subkey=>$subrow) {
			$nice_title[] = $subkey.": ".$subrow;
		}
		if(!$_POST['sku'][$i]) { //create default data
			$_POST['sku'][$i] = $data['sku']."-".strtoupper(str_replace(" ","-",implode("-",$row)));
			//$_POST['name'][$i] = $data['name'];
			$_POST['price'][$i] = $data['price'];
			if($data['price_special']>0) {
				$_POST['price_special'][$i] = $data['price_special'];
			}
		}

		$check_exist_data = $class_product->product_option_data(['attribute'=>$row,'parent_id'=>$id]);
		$check_exist = (count($check_exist_data)>0?true:false);

		$_SESSION['product']['option_gen_array'][$i] = $serialize;
		if($check_exist) {
			$check_exist_data = $class_product->product_data(['id'=>$check_exist_data[0]]);
			$check_exist_meta = $zulu->meta_array($class_product->product_meta($check_exist_data['id']));
			$class_product->vars->data = $check_exist_data;
			$table_row[] = ['class'=>'row-disabled color-grey','content'=>[
				["<span class=\"fas fa-check\"></span> ".implode(", ",$row)],
				[$class_product->name()],
				[$check_exist_data['sku']],
				[($check_exist_meta['stock']>0?$check_exist_meta['stock']:'0')],
				[$currency_symbol.$zulu->dollar($check_exist_data['price'],1)],
				[$currency_symbol.$zulu->dollar($check_exist_data['price_special'],1)],
				['<a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$check_exist_data['id'],'Return'=>urlencode($zulu->link_page(PAGE_file,['self'=>true]))]]).'" class="btn btn-primary btn-circle btn-xs"><i class="fas fa-edit"></i></a> <a href="'.$zulu->link_page(PAGE_file,['query'=>['Action'=>'delete','id'=>$check_exist_data['id'],'Return'=>urlencode($zulu->link_page(PAGE_file,['self'=>true]))]]).'" class="btn btn-danger btn-circle btn-xs"><i class="fas fa-times"></i></a>']],
			];
		} else {
			$table_row[] = array("content" => array(
				array(implode(", ",$row),['class'=>['w20']]),
				array($form_edit->input_html('input',"name[".$i."]",$_POST['name'][$i],['class'=>['input-title'],'placeholder'=>"Leave blank for default title"]),['class'=>['w20']]),
				array($form_edit->input_html('input',"sku[".$i."]",$_POST['sku'][$i],['class'=>['input-sku']]),['class'=>['w20']]),
				array($form_edit->input_html('input',"stock[".$i."]",$_POST['stock'][$i],['class'=>['input-stock'],'placeholder'=>'0']),['class'=>['w10']]),
				array($form_edit->input_html('input',"price[".$i."]",$_POST['price'][$i],['class'=>['input-price'],'placeholder'=>'$0.00']),['class'=>['w10']]),
				array($form_edit->input_html('input',"price_special[".$i."]",$_POST['price_special'][$i],['class'=>['input-prices'],'placeholder'=>'No special ($0.00)']),['class'=>['w10']]),
				array($form_edit->input_html("checkbox","save[".$i."]",1,array('checked'=>($_POST['action'][$i]>0?true:false),'class'=>array('action'))),array('class'=>array('action-field','w10')))
			));

			if($_GET['Do']=='GenOptions') { //-- force SAVE post variable
				$_POST['save'][$i] = 1;
			}
		}
		unset($stock,$product_image,$nice_title);
		$i++;
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'file','html_id'=>'product_list','js_table'=>false,'data_table'=>false));

	//-- Do: Gen Options
	if($_GET['Do']=='GenOptions') {
		$_POST['action'] = 'save';
	}

	//-- Save
	if($_POST['action']=='save') {
		foreach($_POST['sku'] as $key=>$val) {
			if(isset($_POST['save'][$key])&&$_POST['save'][$key]>0) {
				$attr_data = unserialize($_SESSION['product']['option_gen_array'][$key]);

				$check_exist = $class_product->product_option_data(['attribute'=>$attr_data,'parent_id'=>$id]);
				$check_exist = (count($check_exist)>0?true:false);

				if(!$check_exist) {
					$data = [
						'sku'	=>	$_POST['sku'][$key],
						'name'	=>	$_POST['name'][$key],
						'price'	=>	$_POST['price'][$key],
						'price_special'	=>	$_POST['price_special'][$key],
						'stock'			=>	$_POST['stock'][$key],
						'parent_id'		=>	$id,
						'type'			=>	'product',
						'type_variant'	=>	2,
						'attribute'		=>	$attr_data,
					];
					$process = $class_product->product_edit(0,$data);
				}
			}
		}

		$zulu->notification_set("Product options updated.",1);
		header("Location: ".$zulu->link_page(PAGE_file,['self'=>true,'filter'=>['Do']]));
		exit;
	}
}

if(PAGE_action=='feature_edit') { //feature page
	$form_edit = new form;

	if($_GET['Method']=='DeleteImage') {
		$class_product->feature_image_delete(PAGE_id);
		$zulu->notification_set("Feature image was removed.",1);
	}

	if(PAGE_id<1) {
		$id = 0;
		$new = true;

		$_POST['product_id'] = $_GET['Product'];
		$product_data = $class_product->product_data(array('id'=>$_POST['product_id']));

		$zulu->nav->breadcrumb[stripslashes($product_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$product_data['id']]]));
		$zulu->nav->breadcrumb['New Feature'] = array();
		$zulu->nav->title = "New Feature";
	} else {
		$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));

		$feature_data = $class_product->feature_data(array('id'=>$id));
		$product_data = $class_product->product_data(array('id'=>$feature_data['product_id']));

		if(!$_POST) {
			foreach($feature_data as $key=>$val) {
				$_POST[$key] = $val;
			}
		}
		$class_product->vars->product_id = $product_data['id'];

		$zulu->nav->breadcrumb[stripslashes($product_data['name'])] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$product_data['id']]]));
		$zulu->nav->breadcrumb['Edit Feature'] = array();
		$zulu->nav->breadcrumb[stripslashes($feature_data['name'])] = array();
		$zulu->nav->title = "Edit Feature";
	}

	if($_POST['action'] == "edit") {
		$form_edit->valid = true;

		if($form_edit->validate(['title'])) {
			$zulu->notification_set("Please enter fields denoted *.",2);
			$form_edit->valid = false;
		}

		if($form_edit->valid) {

			$input = [
				'title'			=>	addslashes(htmlentities($_POST['title'])),
				'description'	=>	addslashes($_POST['description']),
				'sort'			=>	$_POST['sort'],
				'product_id'	=>	$_POST['product_id'],
			];

			$data = $class_product->feature_edit($id,$input);

			if($data['success']) {
				$id = $data['id'];

				if($_FILES['image']['tmp_name']!=NULL) {
					$upl_data = $class_product->feature_image_add($id);
					$class_product->feature_edit($id,['image'=>$upl_data['name']]);
				}

				$zulu->notification_set("Feature ".(!$new?"updated":"created")." successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$_POST['product_id'],'Action'=>'edit'))));
				exit;
			} else {
				$zulu->notification_set("A database error occurred.",2);
			}
		}
	}
}

if(PAGE_action=='feature_delete') { //delete feature
	$feature_data = $class_product->feature_data(array('id'=>PAGE_id));
	$product_data = $class_product->product_data(array('id'=>$feature_data['product_id']));

	if($product_data['user_id']!=$class_user->authorised->id) {
		$zulu->notification_set("This attribute is unavailable.",2);
	} else {
		if($class_product->feature_delete($feature_data['id'])) {
			$zulu->notification_set("Feature removed successfully.",1);
			header("Location: ".$zulu->link_page('product',['query'=>array('id'=>$product_data['id'],'Action'=>'edit')]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
	}
}

if(PAGE_action=='special') {
	if(isset($_GET['sort']) && !empty($_GET['sort'])) {
		switch ($_GET['sort']) {
			case 'active':
				$sql_config['active'] = true;
			break;
			case 'upcoming':
				$sql_config['upcoming'] = true;
			break;
			case 'complete':
				$sql_config['complete'] = true;
			break;
		}
		$tab = $_GET['sort'];
	}

	$new_special_html = '<a href="'.$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'special_edit'))).'"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> New Special</button></a>';

	$data_rows = $class_product->product_special_data($sql_config);

	function create_function_buttons($id) {
		global $zulu;
		return "
			<a href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'special_edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
			<a class=\"confirm-delete\" href=\"".$zulu->link_page(PAGE_file,array('query'=>array('id'=>$id,'Action'=>'special_delete','Sort'=>$_GET['Sort'])))."\"><button class=\"btn btn-danger btn-circle\" type=\"button\"><i class=\"fas fa-times\"></i></button></a>
		";
	}

	$table_column[] = array("Title",array('class'=>array('')));
	$table_column[] = array("Status",array('class'=>array('')));
	$table_column[] = array("Amount",array('class'=>array('')));
	$table_column[] = array("Type",array('class'=>array('')));
	$table_column[] = array("Date From",array('class'=>array('')));
	$table_column[] = array("Date To",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('')));
		$table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

	foreach($data_rows as $row) {
		//Get the status
		$type_text = '';
		switch ($row['discount_type']) {
			case 'percent':
				$type_text = 'Percent';
			break;
			case 'fixed':
				$type_text = 'Fixed';
			break;
			case 'actual':
				$type_text = 'Actual';
			break;
		}
		$enabled_html = '';
		if($row['status'] == 1){
			$enabled_html = '<span class="opt opt-success opt-bord"><span class="fas fa-check"></span> Enabled</span>';
		}else{
			$enabled_html = '<span class="opt opt-danger opt-bord"><span class="fas fa-times"></span> Disabled</span>';
		}
		$status_indicator_colour = '';
		$status_indicator = '';
		$status_text = '';
		if(time() > $row['date_from'] && time() < $row['date_to']){
			//Within date period
			$status_text = 'Active';
			$status_indicator_colour = 'success';
			$status_indicator = 'check';
		}else if($row['date_to'] > time() && $row['date_from'] > time()){
			//Before date period
			$status_text = 'Upcoming';
			$status_indicator_colour = 'warning';
			$status_indicator = 'pause';
		}else if($row['date_to'] < time() && $row['date_from'] < time()){
			//After date period
			$status_text = 'Complete';
			$status_indicator_colour = 'danger';
			$status_indicator = 'remove';
		}else{
			$status_text = 'Unknown';
			$status_indicator_colour = 'secondary';
			$status_indicator = 'question';
		}

		$table_row[] = array("content" => array(
			array($row['title']),
			array('</p><span class="opt opt-'.$status_indicator_colour.' opt-bord"><span class="fas fa-'.$status_indicator.'"></span> '.$status_text.'</span>&nbsp;'.$enabled_html.'</p>'),
			array($row['amount']),
			array($type_text),
			array($zulu->dateDecode($row['date_from'])),
			array($zulu->dateDecode($row['date_to'])),
			array(create_function_buttons($row['id']),array('class'=>array('right','w120')))
		));
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>''));
	$zulu->nav->title = 'Product Specials';
}

if(PAGE_action=='special_edit') {
	$zulu->template->css_file[] = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css";
	$zulu->template->js_file[] = "//code.jquery.com/ui/1.11.4/jquery-ui.js";
	//Assets for the product/category selection
	$zulu->template->css_file[] = TPL_rel."assets/tree-multiselect/dist/jquery.tree-multiselect.min.css";
	$zulu->template->js_file[] = TPL_rel."assets/tree-multiselect/dist/jquery.tree-multiselect.min.js";

	$zulu->template->js_code[] = "
	$(function() {
		$('.datepicker').datepicker({dateFormat: 'dd-mm-yy'});
		$('#product_select').treeMultiselect({
			startCollapsed:true,
			enableSelectAll:true,
		});
	});";

	$edit_mode = false;
	if($_GET['id'] > 0){
		$id = $db->escape_string($_GET['id']);
		$special_data = $class_product->product_special_data(['id'=>$id]);
		$product_special_item_data = $class_product->product_special_item_data(['product_special_id'=>$id]);
		$current_included_products = [];
		foreach($product_special_item_data as $special_item){
			$current_included_products[] = $special_item['product_id'];
		}
		$mode_text = "Editing Special: ".$special_data['title'];
		$edit_mode = true;
	}else{
		$id = 0;
		$mode_text = "New Special";
		$edit_mode = false;
	}
	$zulu->nav->breadcrumb[$mode_text] = array();
	$zulu->nav->title = $mode_text;

	$product_data = $class_product->product_data(['type'=>'product', 'type_variant'=>[0,1], 'sys'=>'0']);
	foreach($product_data as $product){
		$has_cats = false;
		$prod_cat_path_string_arr = [];
		if(!empty(trim($product['name']))){
			$cat_path_string ='';
			$product_meta = $class_product->product_meta($product['id']);
			$product_cats = $product_meta['category'];
			if(count($product_cats) > 0 && is_array($product_cats) && !empty($product_cats)){
				$has_cats = true;
				if (count($product_cats) == count($product_cats, COUNT_RECURSIVE)){
					//Single item
					if($product_cats['value'] > 0){
						$category_tree = $class_product->category_tree($product_cats['value']);
						$cat_count = count($class_product->category_tree->tree);
						$loop_count = 1;
						$cat_path_string ='';
						foreach($class_product->category_tree->tree as $cat_id){
							$cat_data = $class_product->product_data(['id'=>$cat_id]);
							$text = str_replace('/','|',$cat_data['name']);
							$cat_path_string .= $text;
							if($loop_count != $cat_count){
								$cat_path_string .= '/';
							}
							$loop_count++;
						}
						$prod_cat_path_string_arr[] = $cat_path_string;
						$class_product->category_tree->tree = [];
					}
				}else{
					//Multiple items
					//Add an option for each category the product is in
					foreach($product_cats as $cat){
						$category_tree = $class_product->category_tree($cat['value']);
						$cat_count = count($class_product->category_tree->tree);
						$loop_count = 1;
						$cat_path_string ='';
						foreach($class_product->category_tree->tree as $cat_id){
							$cat_data = $class_product->product_data(['id'=>$cat_id]);
							$text = str_replace('/','|',$cat_data['name']);
							$cat_path_string .= $text;
							if($loop_count != $cat_count){
								$cat_path_string .= '/';
							}
							$loop_count++;
						}
						$prod_cat_path_string_arr[$cat['value']] = $cat_path_string;
						$class_product->category_tree->tree = [];
					}
				}
			}

			if($product['type_variant'] == 1){
				//Product has variants. Make the main product a 'category' and list variants under that category
				$variant_data = $class_product->product_data(['type'=>'product', 'type_variant'=>[2], 'parent_id'=>$product['id'], 'sys'=>'0']);
				if(!$has_cats) {
					$cat_path_string .= $product['name'];
					foreach($variant_data as $variant){
						$selected = false;
						if($edit_mode){
							$selected = in_array($variant['id'],$current_included_products);
						}
						$options_html .= "<option value='".$variant['id']."' data-section='".$cat_path_string."' ".($selected?"selected":NULL).">".$class_product->name($variant['id'])."</option>";
					}
				} else {
					foreach($prod_cat_path_string_arr as $cat_path_string) {
						$cat_path_string .= "/".$product['name'];
						foreach($variant_data as $variant){
							$selected = false;
							if($edit_mode){
								$selected = in_array($variant['id'],$current_included_products);
							}
							$options_html .= "<option value='".$variant['id']."' data-section='".$cat_path_string."' ".($selected?"selected":NULL).">".$class_product->name($variant['id'])."</option>";
						}
					}
				}

			}else{
				$selected = false;
				if($edit_mode){
					$selected = in_array($product['id'],$current_included_products);
				}
				if(!$has_cats) {
					$options_html .= "<option value='".$product['id']."' data-section='".$cat_path_string."' ".($selected?"selected":NULL).">".$product['name']."</option>";
				} else {
					foreach($prod_cat_path_string_arr as $cat_path_string) {
						$options_html .= "<option value='".$product['id']."' data-section='".$cat_path_string."' ".($selected?"selected":NULL).">".$product['name']."</option>";
					}
				}
			}
			$class_product->category_tree->tree = [];
		}
	}

	$form_edit = new form();
	if($_POST) {
		if($_POST['action'] == 'save_special'){
			$valid = true;
			if($form_edit->validate(['title','date_from', 'date_to','amount','discount_type'])){
				$zulu->notification_set("Please fill out all fields.",2);
				$valid = false;
			}
			if($valid){
				$special_data = [
					"title"=>$db->escape_string($_POST['title']),
					"date_from"=>$db->escape_string(strtotime($_POST['date_from'])),
					"date_to"=>$db->escape_string(strtotime($_POST['date_to'])),
					"amount"=>$db->escape_string($_POST['amount']),
					"discount_type"=>$db->escape_string($_POST['discount_type']),
					"status"=>$db->escape_string($_POST['status']),
				];

				$return = $class_product->product_special_edit($id, $special_data, $_POST['products'], true);
				if($return['success']){
					$id=$return['id'];
					$zulu->notification_set("Special ".($edit_mode?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'special_edit','id'=>$id,'Sort'=>$_GET['Sort']))));
					exit;
				}

			}

		}

	}
	if($edit_mode){
		foreach($special_data as $key=>$val){
			if($key=='date_to' || $key=='date_from'){
				$_POST[$key] = date("d-m-Y",$val);
			}else{
				$_POST[$key] = $val;
			}
		}
	}
}

if(PAGE_action=='special_delete') {
	if($class_product->product_special_delete(PAGE_id)) {
		$zulu->notification_set("Special removed successfully.",1);
		header("Location: ".$zulu->link_page(PAGE_file,array('query'=>array('Action'=>'special','Sort'=>$_GET['Sort']))));
		exit;
	} else {
		$zulu->notification_set("A database error occurred.",2);
	}
}

if(PAGE_action == 'price_break') {

	$form_edit = new form;
	$zulu->nav->title = "Price Breaks";

	if(PAGE_id <= 0) {
		$zulu->notification_set("Please select a product to manage pricing brackets.",2);
		header("Location: ".$zulu->link_page(PAGE_file));
		exit;
	}

    if(!$class_setting->data['price_break_enable']) {
        $zulu->notification_set("Price Breaks are currently disabled. To view Price Breaks you must enable them <a href='".$zulu->link_page('setting', ['query'=>['Tab'=>'misc']])."' class='alert-link'>here</a>.",2);
		header("Location: ".$zulu->link_page(PAGE_file, ['query'=>['Action'=>'edit','id'=>PAGE_id]]));
		exit;
    }

	//Data
	$id = PAGE_id;
	$product_row = $class_product->product_data(['id'=>$id]);
    $product_meta = $zulu->meta_array($class_product->product_meta($id));

	$zulu->nav->breadcrumb[stripslashes($class_product->name($id))] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'edit','id'=>$id]]));
	$zulu->nav->breadcrumb['Price Breaks'] = [];

    if($_POST['action'] == 'edit') {
        $form_edit->valid = true;
        foreach($_POST['break'] as $break_id=>$break_row) {
            if($break_row['quantity_min'] > $break_row['quantity_max'] && $break_row['quantity_max'] > 0) {
                $zulu->notification_set("Please make sure you enter maximum quantities that are greater then their minimum quatities.",2);
				$form_edit->valid = false;
            } elseif($break_row['price_special'] > $break_row['price']) {
                $zulu->notification_set("Please make sure you enter a special price that is less than the regular price.",2);
				$form_edit->valid = false;
            }
        }
        foreach($_POST['break_new']['quantity_min'] as $break_id=>$break_val) {
            if($_POST['break_new']['quantity_min'][$break_id] > $_POST['break_new']['quantity_max'][$break_id] && $_POST['break_new']['quantity_max'][$break_id] > 0) {
                $zulu->notification_set("Please make sure you enter maximum quantities that are greater then their minimum quatities.",2);
				$form_edit->valid = false;
            } elseif($_POST['break_new']['price_special'][$break_id] > $_POST['break_new']['price'][$break_id]) {
                $zulu->notification_set("Please make sure you enter a special price that is less than the regular price.",2);
				$form_edit->valid = false;
            }
        }

        if($form_edit->valid) {
            $valid_ids = [];
            foreach($_POST['break'] as $break_id=>$break_row) {
                $data = [
                    'product_id'    =>  $id,
                    'quantity_min'  =>  $db->escape_string(($break_row['quantity_min']>0?$break_row['quantity_min']:1)),
                    'quantity_max'  =>  $db->escape_string($break_row['quantity_max']),
                    'price'         =>  $db->escape_string($break_row['price']),
                    'price_special' =>  $db->escape_string($break_row['price_special']),
                ];
                $result = $class_product->price_break_edit($break_id,$data);
                $valid_ids[] = $result['id'];
            }
            foreach($_POST['break_new']['quantity_min'] as $break_id=>$break_val) {
                $data = [
                    'product_id'    =>  $id,
                    'quantity_min'  =>  $db->escape_string(($_POST['break_new']['quantity_min'][$break_id]>0?$_POST['break_new']['quantity_min'][$break_id]:1)),
                    'quantity_max'  =>  $db->escape_string($_POST['break_new']['quantity_max'][$break_id]),
                    'price'         =>  $db->escape_string($_POST['break_new']['price'][$break_id]),
                    'price_special' =>  $db->escape_string($_POST['break_new']['price_special'][$break_id]),
                ];
                $result = $class_product->price_break_edit(0,$data);
                $valid_ids[] = $result['id'];
            }
            $price_break_data = $class_product->price_break_data(['product_id'=>$id,'sort'=>'quantity_min ASC']);
            foreach($price_break_data as $price_break_row) {
                if(!in_array($price_break_row['id'],$valid_ids)) {
                    $result = $class_product->price_break_delete($price_break_row['id']);
                }
            }

            foreach($_POST['meta'] as $key=>$val) {
                $zulu->meta_update('product',$id,$key,$db->escape_string($val));
            }

            $zulu->notification_set("Price Breaks have been updated successfully.",1);
            header("Location: ".$zulu->link_page(PAGE_file,['self'=>true]));
            exit;
        }
    }

    $price_break_data = $class_product->price_break_data(['product_id'=>$id,'sort'=>'quantity_min ASC']);
    if(!$_POST) {
        foreach($price_break_data as $price_break_row) {
            foreach($price_break_row as $key=>$val) {
                $_POST['break'][$price_break_row['id']][$key] = $val;
            }
        }
        $_POST['meta']['price_break_disable'] = $product_meta['price_break_disable'];
        $_POST['meta']['price_break_disable_parent'] = $product_meta['price_break_disable_parent'];
    }

    $table_column = [
        ["Minimum Quantity ".$form_edit->icon_help("Must be less than or equal to the Maximum Quantity. Leave blank if there's no Minimum Quantity."), ['class'=>[]]],
        ["Maximum Quantity ".$form_edit->icon_help("Must be greater than the Minimum Quantity. Leave blank if there's no Maximum Quantity."), ['class'=>[]]],
        ["Price", ['class'=>[]]],
        ["Special Price", ['class'=>[]]],
        ["Actions", ['class'=>['right']]],
    ];
    $table_row[] = ["header" => true, "class" => "", "content" => $table_column];
    foreach($_POST['break'] as $break_id=>$break_row) {
        $table_row[] = ["content" => [
            [$form_edit->input_html('number','break['.$break_id.'][quantity_min]',$break_row['quantity_min'],['custom'=>['min'=>'0','placeholder'=>'1','step'=>'any']])],
            [$form_edit->input_html('number','break['.$break_id.'][quantity_max]',($break_row['quantity_max']>0?$break_row['quantity_max']:null),['custom'=>['min'=>'0','placeholder'=>'+','step'=>'any']])],
            ["<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('number','break['.$break_id.'][price]',$break_row['price'],['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div>"],
            ["<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('number','break['.$break_id.'][price_special]',($break_row['price_special']>0?$break_row['price_special']:null),['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div>"],
            ["<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",['class'=>['right','w80']]]
        ]];
    }
    foreach($_POST['break_new']['quantity_min'] as $break_id=>$break_val) {
        $table_row[] = ["content" => [
            [$form_edit->input_html('number','break_new[quantity_min]['.$break_id.']',$_POST['break_new']['quantity_min'][$break_id],['custom'=>['min'=>'0','placeholder'=>'1','step'=>'any']])],
            [$form_edit->input_html('number','break_new[quantity_max]['.$break_id.']',($_POST['break_new']['quantity_max'][$break_id]>0?$_POST['break_new']['quantity_max'][$break_id]:null),['custom'=>['min'=>'0','placeholder'=>'+','step'=>'any']])],
            ["<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('number','break_new[price]['.$break_id.']',$_POST['break_new']['price'][$break_id],['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div>"],
            ["<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('number','break_new[price_special]['.$break_id.']',($_POST['break_new']['price_special'][$break_id]>0?$_POST['break_new']['price_special'][$break_id]:null),['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div>"],
            ["<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",['class'=>['right','w80']]]
        ]];
    }
    $zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'','js_table'=>false,'data_table'=>false,'html_id'=>'price-break-options','tbody'=>['id'=>'sortable-rows']));

    $breaks_valid = $class_product->price_break_check($id);

	$zulu->template->jquery[] = "
		$('#sortable-rows').sortable();
        $(document).on('click','#row-add',function() {
            $('#price-break-options').append('<tr><td>".$form_edit->input_html('number','break_new[quantity_min][]','',['custom'=>['min'=>'0','placeholder'=>'1','step'=>'any']])."</td><td>".$form_edit->input_html('number','break_new[quantity_max][]','',['custom'=>['min'=>'0','placeholder'=>'+','step'=>'any']])."</td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('number','break_new[price][]','',['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div></td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('number','break_new[price_special][]','',['custom'=>['min'=>'0','placeholder'=>'0.00','step'=>'any']])."</div></td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
        });
        $(document).on('click','.clear-row',function() {
            var trow = $(this).parent().parent();
            $(trow).find('input').val('');
            return false;
        });
        $(document).on('click','.remove-row',function() {
            var trow = $(this).parent().parent();
            $(trow).remove();
            return false;
        });
        ".(count($price_break_data)<=0?"
        $('#row-add').trigger('click');
        ":null)."
	";
}

if(PAGE_action=='brand') {
    $form_edit = new form;

    $start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
    $where = [];

    if(isset($_GET['Search']) && $_GET['Search'] != null) {
        $where[] = ['title', 'like', "%".$_GET['Search']."%"];
    }

    $table_column = $table_column = [];
    $table_column[] = array("Title",array('class'=>array('')));
    $table_column[] = array("Products",array('class'=>array('')));
    if($class_user->authorised->opt_website) {
        $table_column[] = array("Live",array('class'=>array('')));
    }
    $table_column[] = array("Added",array('class'=>array('')));
    $table_column[] = array("Actions",array('class'=>array('')));
    $table_row[] = array(
            "header"	=>	 true,
            "class"		=>	"",
            "content"	=>	$table_column);

    $brands_all = ProductBrand::where($where)->get();
    $brands = ProductBrand::orderBy('title','ASC')->where($where)->offset($start)->take(MAX_per_page)->get();
    foreach($brands as $brand) {

        $products = $brand->products;

        $name_extra = [];
        if($class_user->authorised->opt_website && trim($brand->meta_title)!=null && trim($brand->meta_description)!=null) {
            $name_extra[] = "<span class=\"opt opt-success opt-bord\"><span class='fa fa-check'></span> Meta Data</span>";
        }

        $buttons = [];
        if($class_user->authorised->opt_website) {
            $buttons[] = ['link'=>$zulu->front_link($brand->feURL()),'label'=>'View','icon'=>'external-link-alt','class'=>'default','target'=>'_blank'];
        }
        $buttons[] = ['link'=>$zulu->link_page(PAGE_file,array('query'=>array('brand'=>$brand->id))),'label'=>'Products','icon'=>'cube','class'=>'info'];
        $buttons[] = ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'brand_edit','id'=>$brand->id))),'label'=>'Edit','icon'=>'edit','class'=>'primary'];
        $buttons[] = ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'brand_delete','id'=>$brand->id))),'label'=>'Remove','icon'=>'times','class'=>'danger','class_append'=>['confirm']];

        $table_row_contents = [
            array($brand->title." <small>".implode(" ",$name_extra)."</small>"),
            array(count($products)),
        ];
        if($class_user->authorised->opt_website) {
            $table_row_contents[] = array(($brand->hide?'No':'Yes'));
        }
        $table_row_contents[] = array($zulu->dateDecode($brand->stat_add, 'h:ia d/m/Y'));
        $table_row_contents[] = array($zulu->button_render($buttons),array('class'=>array('right')));
        $table_row[] = array("content"=>$table_row_contents);
    }

    $total_count = count($brands_all);
	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false));
    $pagination = $zulu->pagination($_GET['Pg'],['count'=>$total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
	$zulu->nav->title = 'Product Brands';
    $zulu->nav->breadcrumb['Brands'] = array();

}

if(PAGE_action=='brand_delete') {
    $result = ProductBrand::where([['id', PAGE_id]])->delete();

    $zulu->notification_set("Brand removed successfully.",1);
    header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['id'], 'query'=>['Action'=>'brand']]));
    exit;
}

if(PAGE_action=='brand_edit') {
    $form_edit = new form;

    if(PAGE_id<1) {
        $id = 0;
        $new = true;

        if(!$_POST) {
            $temp_folder = date("Ymd")."-".$zulu->serial(8);
        } else {
            $temp_folder = $_POST['temp_folder'];
        }
        $main_path = 'temp/'.$temp_folder.'/';
    } else {
        $id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
        $new = false;
        $brand = ProductBrand::find($id);
        if($brand == null) {
            header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['id']]));
            exit;
        }

        $main_path = $brand->id.'/';
    }

    if($_GET['Do'] == 'ClearImage') {
        @unlink(ProductBrand::file_path().$main_path.$brand->image);
        $brand->image           = '';
        $brand->colour_default  = '';
        $brand->save();
        header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do']]));
        exit;
    }

    //Form Submit
    if($_POST['action']=='edit') {
        $form_edit->valid = true;

        if($form_edit->validate(['title'])) {
            $zulu->notification_set("Please enter all fields denoted *.",2);
            $form_edit->valid = false;
        }

        if($form_edit->valid) {

            if($new || $brand == null) {
                $brand = new ProductBrand();
                $brand->user_id     = $class_user->authorised->id;
            }
            $brand->title 			= $_POST['title'];
            $brand->slug 			= $zulu->slug($_POST['title']);
            $brand->description 	= $_POST['description'];
            $brand->meta_title 	    = $_POST['meta_title'];
            $brand->meta_keywords 	= $_POST['meta_keywords'];
            $brand->meta_description= $_POST['meta_description'];
            $brand->hide            = ($_POST['hide']?1:0);
            $brand->colour_manual   = (!$_POST['colour_manual_off']&&isset($_POST['colour_manual'])?$_POST['colour_manual']:'');
            $brand->save();

            if($brand->id > 0) {

                if($new && $class_user->authorised->opt_website) {
                    $file_path = ProductBrand::file_path();
                    $temp_folder = $file_path.$main_path;
                    $file_content = glob($temp_folder."*");
                    $main_path = $brand->id.'/';
                    @mkdir($file_path.$main_path);
                    foreach($file_content as $file) {
                        $basename = basename($file);
                        $new_file = $file_path.$main_path.$basename;
                        @copy($file, $new_file);
                        @unlink($file);
                        $brand->image = $basename;
                        $colours = $class_file->colour_extract($new_file);
                        if(count($colours) > 0 && $colours[0] != null) {
                            $brand->colour_default = $colours[0];
                        }
                        $brand->save();
                    }
                    @rmdir($temp_folder);
                }

                $zulu->notification_set("Brand ".(!$new?"updated":"created")." successfully.",1);
                header("Location: ".$zulu->link_page(PAGE_file,['query'=>['Action'=>'brand']]));
                exit;
            } else {
                $zulu->notification_set("A database error occurred.",2);
            }
        }
    }

    $zulu->nav->breadcrumb['Brands'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'brand']]));
    if($new) {
        $zulu->nav->breadcrumb['New Brand'] = array();
        $zulu->nav->title = "New Brand";
    } else {
        $zulu->nav->title = "Edit Brand";
        $zulu->nav->breadcrumb[$brand->title] = array();

        if(!$_POST) {
            $_POST = $brand->getAttributes();
            if(!$_POST['colour_manual']) {
                $_POST['colour_manual_off'] = 1;
            }
        }

        $image = $brand->image();
    }

    @mkdir(ProductBrand::file_path().$main_path);
    $class_file->uploadifive_new("image_main",['preview'=>true,'post'=>['action'=>'product_brand_image','id'=>PAGE_id,'file_name'=>'','path'=>ProductBrand::$file_folder.$main_path],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);

}

if(PAGE_action=='review') {
    $form_edit = new form;

    if(isset($_GET['Do']) && $_GET['Do'] != null) {
        $review = ProductReview::find($_GET['id']);
        if($review != null) {
            switch ($_GET['Do']) {
                case 'approve':
                    $review->status = 'live';
                    $review->approveNotify();
                    break;
                case 'disapprove':
                    $review->delete();
                    break;
                case 'show':
                    $review->status = 'live';
                    break;
                case 'hide':
                    $review->status = 'hidden';
                    break;
                case 'feature':
                    $review->feature = 1;
                    break;
                case 'defeature':
                    $review->feature = 0;
                    break;
                case 'verify':
                    $review->verified = 1;
                    break;
                case 'deverify':
                    $review->verified = 0;
                    break;
            }
            $review->save();
            $zulu->notification_set("Review has been updated successfully.", 1);
        }

        header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do','id']]));
        exit;
    }

	$table_column = $table_column = [];
    $table_column[] = array("Rating",array('class'=>array('')));
	$table_column[] = array("Title",array('class'=>array('')));
	$table_column[] = array("Product",array('class'=>array('')));
	$table_column[] = array("Client",array('class'=>array('')));
    $table_column[] = array("Added",array('class'=>array('')));
	$table_column[] = array("Actions",array('class'=>array('')));
    $table_row[] = array(
			"header"	=>	 true,
			"class"		=>	"",
			"content"	=>	$table_column);

    $start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
    $where = [];

    if(isset($_GET['Search']) && $_GET['Search'] != null) {
        $where[] = ['title', 'like', "%".$_GET['Search']."%"];
    }
    if(isset($_GET['Product']) && $_GET['Product'] > 0) {
        $product = Products::find($_GET['Product']);
        if($product != null) {
            $where[] = ['product_id', $_GET['Product']];
            $product_search_title = $product->name;
            $product_filter = true;
        }
    } else {
        $product_search_title = null;
        $product_filter = false;
    }
    if(isset($_GET['Client']) && $_GET['Client'] > 0) {
        $where[] = ['client_id', $_GET['Client']];
        $client = Clients::find($_GET['Client']);
        $client_filter = true;
    } else {
        $client_filter = false;
    }
    switch ($_GET['Tab']) {
        case 'live':
            $view_tab = 'live';
            break;
        case 'pending':
            $view_tab = 'pending';
            break;
        case 'hidden':
            $view_tab = 'hidden';
            break;
        default:
            if($class_setting->data['ws_shop_product_review_approval']) {
                $view_tab = 'pending';
            } else {
                $view_tab = 'live';
            }
            break;
    }
    $where[] = ['status', $view_tab];

    $reviews_all = ProductReview::where($where)->get();
    $reviews = ProductReview::latest('id','DESC')->where($where)->offset($start)->take(MAX_per_page)->get();
    $modals = [];

    foreach($reviews as $review) {

        if(!$product_filter) {
            $rproduct = $review->product;
        } else {
            $rproduct = $product;
        }
        if($review->client_id > 0 && !$client_filter) {
            $client = $review->client;
        }

        $buttons = $pbuttons = [];
        if($review->title != null) {
            $modal = $review->viewModal();
            $modals[] = $modal['html'];
            $buttons[] = ['link'=>'#','label'=>'View','icon'=>'search','class'=>'info','class_append'=>['modal-trigger'],'data'=>['modal'=>$modal['id']]];
        } else {
            $review->title = "<i>No review</i>";
        }
        $buttons[] = ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'review_delete','id'=>$review->id))),'label'=>'Remove','icon'=>'times','class'=>'danger'];
        if(!$product_filter) {
            $pbuttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Product'=>$review->product_id],'filter'=>['Pg']]),'label'=>'Filter','icon'=>'filter','class'=>'default'];
        }

        $name_extra = $rating_extra = [];
        if($review->feature) {
            $name_extra[] = "<span class='opt opt-primary opt-bord'><span class='fas fa-star'></span> Featured</span>";
        }
        if($review->verified) {
            $rating_extra[] = "<span class='opt opt-success opt-bord'><span class='fas fa-check'></span> Verified</span>";
        }

		$table_row[] = array("content" => array(
            array($review->rating." / ".ProductReview::$stars.(count($rating_extra)>0?" <small>".implode(' ',$rating_extra)."</small>":null)),
            array($review->title.(count($name_extra)>0?" <small>".implode(' ',$name_extra)."</small>":null)),
			array($rproduct->beLink().(count($pbuttons)>0?" ".$zulu->button_render($pbuttons):null)),
			array(($review->client_id>0?$client->beLink():'Anonymous')),
			array($zulu->dateDecode($review->stat_add, 'h:ia d/m/Y')),
			array($zulu->button_render($buttons),array('class'=>array('right')))
		));
	}

	$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false));
	$zulu->nav->title = 'Product Reviews';
    if($product != null) {
        $zulu->nav->breadcrumb['Reviews'] = array('link'=>$zulu->link_page(PAGE_file, ['query'=>['Action'=>'review', 'Tab'=>$view_tab]]));
        $zulu->nav->breadcrumb[$product_search_title] = array();
    } else {
        $zulu->nav->breadcrumb['Reviews'] = array();
    }
    $total_count = count($reviews_all);
    $pagination = $zulu->pagination($_GET['Pg'],['count'=>$total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true,'filter'=>['Pg']))]);

    $tabs = [
        'pending'   =>  'Pending',
        'live'      =>  'Live',
        'hidden'    =>  'Hidden',
    ];

    $zulu->template->js_file[] = TPL_rel."assets/smart.find.js";
    $zulu->template->jquery[] = "
    $(document).on('change', '#product-filter', function() {
        $(this).closest('form').submit();
    });
    ";
}

if(PAGE_action=='review_delete') {
    $review = ProductReview::find(PAGE_id);
    if($review != null) {
        $review->delete();
        $product = $review->product;
        if($product != null) {
            $product->setReviewRating();
        }
    }

    $zulu->notification_set("Review removed successfully.",1);
    header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['id'], 'query'=>['Action'=>'review']]));
    exit;
}
