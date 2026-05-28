<?php
//(C)2007-2011 RAZOR WEB DESIGN
//RAZOR WEB DESIGN (SHOP v2.0.0)
//ZULU SHOP - www.razorweb.co.nz/zulu
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a open-source module built into Zulu    //
//Shopfront. It is highly illegal to resell this  //
//module.										  //
//************************************************//

//MODULE TYPE: Shipping
//MODULE NAME: ZULU Shopfront - Weight Based Delivery (COURIER)

//Module Functions***********

class m_product_quantity {

	function __construct($config=[]) {

		$this->type = 2;
		$this->class_name = 'm_product_quantity';
		$this->filename = 'm_product_quantity.php';
        $this->short = 'prodq';
		$this->title = 'Product Quantity Based Shipping';
		$this->title_client = 'Standard Delivery';
		$this->info_client = 'Shipping time varies from 1 to 5 business days depending on location.';
		$this->meta_field_count = 4;
        $this->shipping_detail = true;
	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
				//Load default rule
				$zulu->meta_update('module',$result['id'],'1_name','Flat Rate');
				$zulu->meta_update('module',$result['id'],'1_price','5');
				$zulu->meta_update('module',$result['id'],'1_unit','2');
				$zulu->meta_update('module',$result['id'],'1_max','0');
				return ['success'=>true, 'id'=>$result['id']];
			} else {
				return ['success'=>false, 'reason'=>'Error installing module.'];
			}
		} else {
			return ['success'=>false, 'reason'=>'Module already installed.'];
		}
	}
	function uninstall() {
		global $class_module;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] > 0) {
			$class_module->delete($check['id']);
			return ['success'=>true];
		} else {
			return ['success'=>false,'reason'=>'Module not installed.'];
		}
	}

	//##################################### ADMIN FUNCTIONS
	function admin_form_edit() {
		global $form_edit,$zulu;

		$table_column = [
			array("Option Name",array('class'=>array(''))),
			array("Base Price",array('class'=>array(''))),
			array("Additional Unit Price",array('class'=>array(''))),
			array("Max Shipping Price",array('class'=>array(''))),
			array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

		$count = count($_POST['meta']) / $this->meta_field_count;
		for($i=1; $i<=$count; $i++) {
			$table_row[] = array("content" => [
				array($form_edit->input_html('input','option[name][]',stripslashes($_POST['meta'][''.$i.'_name']))),
				array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[price][]',sprintf("%.2f",$_POST['meta'][''.$i.'_price']))."</div>"),
				array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[unit][]',sprintf("%.2f", $_POST['meta'][''.$i.'_unit']))."</div>"),
				array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[max][]',sprintf("%.2f", $_POST['meta'][''.$i.'_max']))."</div>"),
				array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
			]);
		}
		$html = "
		<div id='row-container'>".$zulu->table_render($table_row,0,array('class'=>'','js_table'=>false,'data_table'=>false,'html_id'=>'module-options','tbody'=>['id'=>'sortable-rows']))."</div>
		<div class=\"row\">
			<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
		</div>";
		$zulu->template->js_code[] = "
		$(document).ready(function() {
			$(\"#sortable-rows\").sortable();
			$(\"body\").on('click','#row-add',function() {
				$(\"#module-options\").append('<tr><td>".$form_edit->input_html('input','option[name][]')."</td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[price][]')."</div></td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[unit][]')."</div></td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[max][]')."</div></td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
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

		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);

		$zulu->meta_clear('module',$module_row['id']);
		$i = 1;
		foreach($_POST['option']['name'] as $key=>$val) {
			if($val != '') {
				$zulu->meta_update('module',$module_row['id'],$i.'_name',addslashes($val));
				$zulu->meta_update('module',$module_row['id'],$i.'_price',$_POST['option']['price'][$key]);
				$zulu->meta_update('module',$module_row['id'],$i.'_unit',$_POST['option']['unit'][$key]);
				$zulu->meta_update('module',$module_row['id'],$i.'_max',$_POST['option']['max'][$key]);
				$i++;
			}
		}
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html($config=[]) {
		global $class_module, $zulu, $form_edit;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		$price_arr = $this->checkout_ship_price(true);
		$count = count($module_meta) / $this->meta_field_count;
		for($i=1; $i<=$count; $i++) {
			$module_options[$i] = stripslashes($module_meta[$i.'_name'])." | ".LOCALE_currency.number_format($price_arr[$i],2);
		}
		$output = $form_edit->input_html('select','module_shipping_option['.$this->short.']',($config['selected']?$_SESSION['CHECKOUT']['SHIP']['OPTION']:null),['option'=>$module_options]);

		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null)."
        ".$output;
	}
	function checkout_ship_price($array=false) {
		global $class_module,$zulu,$class_sale,$class_product;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'field'=>'product_id, quantity'));

		$price = [];
		$count = count($module_meta) / $this->meta_field_count;
		$quan = $p_ship_price_total = $p_ship_unit_price_total = 0;
		foreach($cart_data as $cart_row) {
			$p_data = $class_product->product_data(['id'=>$cart_row['product_id']]);
			$p_meta = $zulu->meta_array($class_product->product_meta($cart_row['product_id']));

			$p_ship_price = $p_meta['ship_price'];
			$p_ship_unit_price = $p_meta['ship_price_unit'];

			if($p_data['type_variant']==2 && ($p_meta['ship_price']==NULL || $p_meta['ship_price_unit']==NULL)) {
				$parent_meta = $zulu->meta_array($class_product->product_meta($p_data['parent_id']));
				if($p_meta['ship_price'] == NULL) {
                    $p_ship_price = $parent_meta['ship_price'];
                }
				if($p_meta['ship_price_unit'] == NULL) {
                    $p_ship_unit_price = $parent_meta['ship_price_unit'];
                }
			}

			$quan += $cart_row['quantity'];
            if($p_ship_price > 0) {
                $p_ship_price_total += $p_ship_price * $cart_row['quantity'];
            }
            if($p_ship_unit_price > 0 && $cart_row['quantity'] > 1) {
                $p_ship_unit_price_total += $p_ship_unit_price * ($cart_row['quantity'] - 1);
            }
		}

        for($i=1; $i<=$count; $i++) {
			$total_price = $module_meta[$i.'_price'] + $p_ship_price_total + $p_ship_unit_price_total;
            if($module_meta[$i.'_unit'] > 0 && $quan > 1) {
                $total_price += $module_meta[$i.'_unit'] * ($quan - 1);
            }
			if($module_meta[$i.'_max'] > 0 && $total_price > $module_meta[$i.'_max']) {
                $total_price = $module_meta[$i.'_max'];
            }
			$price[$i] = $total_price;
		}

		if(!$array) {
			if($_SESSION['CHECKOUT']['SHIP']['OPTION'] > 0) {
                $price = sprintf("%.2f", $price[$_SESSION['CHECKOUT']['SHIP']['OPTION']]);
            }
			else $price = '0.00';
		}

		return $price;
	}
	function checkout_ship_process() {
		global $class_module,$zulu;
		$_SESSION['CHECKOUT']['SHIP']['OPTION'] = $_POST['module_shipping_option'][$this->short];
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		$_SESSION['CHECKOUT']['SHIP']['NAME'] = $module_meta[$_POST['module_shipping_option'][$this->short].'_name'];
		return true;
	}

}

?>
