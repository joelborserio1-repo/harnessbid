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
//MODULE NAME: ZULU Shopfront - Standard Delivery

//Module Functions***********

class m_standard {

	function __construct($config=[]) {

		$this->type = 2;
		$this->class_name = 'm_standard';
		$this->filename = 'm_standard.php';
        $this->short = 'std';
		$this->title = 'Standard Delivery';
		$this->title_client = 'Standard Delivery';
		$this->info_client = 'Shipping time varies from 1 to 5 business days depending on location.';
		$this->meta_field_count = 2;
		$this->meta_column_count = 2;
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
				$zulu->meta_update('module',$result['id'],'1_name','');
				$zulu->meta_update('module',$result['id'],'1_price','');
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
			array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

		$count = (count($_POST['meta'])-$this->meta_field_count) / $this->meta_column_count;
		for($i=1; $i<=$count; $i++) {
			$table_row[] = array("content" => [
				array($form_edit->input_html('input','option[name][]',stripslashes($_POST['meta'][''.$i.'_name']))),
				array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[price][]',sprintf("%.2f",$_POST['meta'][''.$i.'_price']))."</div>"),
				array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')))
			]);
		}
		$html = "
		<div class=\"row\">
			<div class=\"col-md-4\">
				<label>Free Shipping Threshold</label>
				<div class=\"form-group input-group\">
					<span class=\"input-group-addon\">".LOCALE_currency_symbol."</span>
					".$form_edit->input_html('input','free_amount',$_POST['meta']['free_amount'],['placeholder'=>'0.00'])."
				</div>
			</div>
            <div class=\"col-md-4\">
				<label>Enable Free Shipping Threshold</label>
				<div class=\"form-group\">
					".$form_edit->input_html('select','free_amount_enable',$_POST['meta']['free_amount_enable'],['option'=>['0'=>'No','1'=>'Yes']])."
				</div>
			</div>
		</div>
		<div id='row-container'>".$zulu->table_render($table_row,0,array('class'=>'','js_table'=>false,'data_table'=>false,'html_id'=>'module-options','tbody'=>['id'=>'sortable-rows']))."</div>
		<div class=\"row\">
			<p class='text-center'><button type='button' id='row-add' class='btn btn-primary btn-xs' title='Add another option'><span class='fas fa-plus-circle'></span> Add another option</button></p>
		</div>";
		$zulu->template->js_code[] = "
		$(document).ready(function() {
			$(\"#sortable-rows\").sortable();
			$(\"body\").on('click','#row-add',function() {
				$(\"#module-options\").append('<tr><td>".$form_edit->input_html('input','option[name][]')."</td><td><div class=\"input-group\"><span class=\"input-group-addon\">$</span>".$form_edit->input_html('input','option[price][]')."</div></td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
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
		$zulu->meta_update('module',$module_row['id'],'free_amount',$zulu->dollar($_POST['free_amount']));
        $zulu->meta_update('module',$module_row['id'],'free_amount_enable',$_POST['free_amount_enable']);
		$i = 1;
		foreach($_POST['option']['name'] as $key=>$val) {
			if($val != '') {
				$zulu->meta_update('module',$module_row['id'],$i.'_name',addslashes($val));
				$zulu->meta_update('module',$module_row['id'],$i.'_price',$zulu->dollar($_POST['option']['price'][$key]));
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

		if(is_array($price_arr)) {
			$count = (count($module_meta)-$this->meta_field_count) / $this->meta_column_count;
            $module_options = [0=>'Select an option...'];
			for($i=1; $i<=$count; $i++) {
				$module_options[$i] = stripslashes($module_meta[$i.'_name'])." | ".LOCALE_currency_symbol.$zulu->dollar($price_arr[$i],true);
			}
			$output = $form_edit->input_html('select','module_shipping_option['.$this->short.']',($config['selected']?$_SESSION['CHECKOUT']['SHIP']['OPTION']:null),['option'=>$module_options, 'class'=>['module-option']]);
		} else {
			$output = "<p>Delivery cost is FREE.</p>";
		}

		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null)."
        ".$output;
	}
	function checkout_ship_price($array=false) {
		global $class_module,$zulu,$class_sale;

		unset($_SESSION['CHECKOUT']['SHIP']['RESELECT']);
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
		$sale_row = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN'],'field'=>'id']);
		$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id()));
		$cart_total = 0;
		foreach($cart_data as $cart_row) {
			$cart_total += $cart_row['price']*$cart_row['quantity'];
		}
		$cart_summary = $class_sale->payment_summary($cart_total,['tax_applied'=>true]);
		$sale_total = $cart_summary['total'];

		if($module_meta['free_amount_enable'] && $sale_total >= $module_meta['free_amount']) {
			$price = 0;
			unset($_SESSION['CHECKOUT']['SHIP']['NAME']);
		} else {
			$price = [];
			$count = (count($module_meta)-$this->meta_field_count) / $this->meta_column_count;
			for($i=1; $i<=$count; $i++) {
				$total_price = $module_meta[$i.'_price'];
				$price[$i] = $total_price;
			}

			if(!$array) {
				if($_SESSION['CHECKOUT']['SHIP']['OPTION']>0){
					$price = sprintf("%.2f", $price[$_SESSION['CHECKOUT']['SHIP']['OPTION']]);
				}else {
					$price = '0.00';
					$_SESSION['CHECKOUT']['SHIP']['RESELECT'] = true;
				}
			}
		}

		return $price;
	}
	function checkout_ship_process() {
		global $class_module,$zulu;

		if($_POST['module_shipping_option'][$this->short] > 0) {
			$_SESSION['CHECKOUT']['SHIP']['OPTION'] = $_POST['module_shipping_option'][$this->short];
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
			$_SESSION['CHECKOUT']['SHIP']['NAME'] = $module_meta[$_POST['module_shipping_option'][$this->short].'_name'];
		} else {
            unset($_SESSION['CHECKOUT']['SHIP']);
        }
		return true;
	}
	function checkout_ship_validate() {
        if(isset($_POST['module_shipping_option'][$this->short]) && $_POST['module_shipping_option'][$this->short] <= 0) {
            return ['success'=>false,'msg'=>"No delivery option was selected, please choose one to continue."];
        }
        return ['success'=>true];
    }

}

?>
