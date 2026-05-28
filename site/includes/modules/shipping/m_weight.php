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

class m_weight {

	function __construct($config=[]) {

		$this->type = 2;
		$this->class_name = 'm_weight';
		$this->filename = 'm_weight.php';
		$this->title = 'Weight Based Delivery';
		$this->title_client = 'Standard Delivery';
		$this->info_client = 'Shipping time varies from 1 to 5 business days depending on location.';
		$this->meta_field_count = 3;
        $this->shipping_detail = true;
	}

	//##################################### ADMIN FUNCTIONS
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
				$zulu->meta_update('module',$result['id'],'1_price','');
				$zulu->meta_update('module',$result['id'],'1_weight','');
				$zulu->meta_update('module',$result['id'],'1_volume','');
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
			array("Option",array('class'=>array('center'))),
			array("Price",array('class'=>array(''))),
			array("Weight",array('class'=>array(''))),
			array("Volume",array('class'=>array(''))),
			array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

		$count = count($_POST['meta']) / $this->meta_field_count;
		for($i=1; $i<=$count; $i++) {
			$table_row[] = array("content" => [
				array("<span class='order'>".$i."</span>",array('class'=>array('center'))),
				array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[price][]',sprintf("%.2f", $_POST['meta'][''.$i.'_price']))."</div>"),
				array("<div class='input-group'>".$form_edit->input_html('input','option[weight][]',$_POST['meta'][''.$i.'_weight'])."<span class='input-group-addon'>kg</span></div>"),
				array("<div class='input-group'>".$form_edit->input_html('input','option[volume][]',$_POST['meta'][''.$i.'_volume'])."<span class='input-group-addon'>m<sup>3</sup></span></div>"),
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
			$(\"#sortable-rows\").sortable( {
				update: function(event, ui) {
					renumber();
				}
			});
			$(\"body\").on('click','#row-add',function() {
				$(\"#module-options\").append('<tr><td class=\"center\"><span class=\"order\"></span></td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[price][]')."</div></td><td><div class=\"input-group\">".$form_edit->input_html('input','option[weight][]')."<span class=\"input-group-addon\">kg</span></div></div></td><td><div class=\"input-group\">".$form_edit->input_html('input','option[volume][]')."<span class=\"input-group-addon\">m<sup>3</sup></span></div></td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
				renumber();
			});
			$('body').on('click','.clear-row',function() {
				var trow = $(this).parent().parent();
				$(trow).find('input').val('');
				return false;
			});
			$('body').on('click','.remove-row',function() {
				var trow = $(this).parent().parent();
				$(trow).remove();
				renumber();
				return false;
			});
			function renumber() {
				var i = 1;
				$('table#module-options tbody tr').each(function() {
					$(this).first().find('span.order').html(i);
					i++;
				});
			}
		});
		";

		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;
		$module_row = $class_module->module_data(['class'=>$this->class_name]);

		$zulu->meta_clear('module',$module_row['id']);
		foreach($_POST['option']['price'] as $key=>$val) {
			if($val == '') unset($_POST['option']['price'][$key]);
		}
		$i = 1;
		foreach($_POST['option']['price'] as $key=>$val) {
			$zulu->meta_update('module',$module_row['id'],$i.'_price',sprintf("%.2f", $val));
			$zulu->meta_update('module',$module_row['id'],$i.'_weight',$_POST['option']['weight'][$key]);
			$zulu->meta_update('module',$module_row['id'],$i.'_volume',$_POST['option']['volume'][$key]);
			$i++;
		}
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null)."
        <p>Delivery cost is ".LOCALE_currency.number_format($this->checkout_ship_price(),2)." based on your orders weight.</p>";
	}
	function checkout_ship_price() {
		global $class_module,$zulu,$class_sale;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'field'=>'weight,volume,quantity'));
		foreach($cart_data as $cart_row) {
			$total_weight += $cart_row['weight']*$cart_row['quantity'];
			$total_volume += $cart_row['volume']*$cart_row['quantity'];
		}

		$count = count($module_meta) / $this->meta_field_count;
		for($i=1; $i<=$count; $i++) {
			if(($total_weight>$module_meta[$i."_weight"] || $total_volume>$module_meta[$i."_volume"]) && $i<$count) {
				continue;
			}
			$price = $module_meta[$i."_price"];
			break;
		}

		return $price;
	}
	function checkout_ship_process() {
		unset($_SESSION['CHECKOUT']['SHIP']);
		return true;
	}

}

?>
