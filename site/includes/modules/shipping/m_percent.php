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

class m_percent {

	function __construct($config=[]) {

		$this->type = 2;
		$this->class_name = 'm_percent';
		$this->filename = 'm_percent.php';
		$this->title = 'Percentage Based Shipping';
		$this->title_client = 'Standard Delivery';
		$this->info_client = 'Shipping time varies from 1 to 5 business days depending on location.';
		$this->meta_field_count = 3;
        $this->shipping_detail = true;
	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {
				$zulu->meta_update('module',$result['id'],'percent','');
				$zulu->meta_update('module',$result['id'],'charge_min','');
				$zulu->meta_update('module',$result['id'],'charge_max','');
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

		$html = "
			<div class=\"row\">
				<div class=\"col-md-4\">
					<label>Percentage amount</label>
					<div class=\"form-group input-group\">
						".$form_edit->input_html('input','percent',$_POST['meta']['percent'],['placeholder'=>'0.00'])."
						<span class=\"input-group-addon\">%</span>
					</div>
				</div>
				<div class=\"col-md-4\">
					<label>Min charge</label>
					<div class=\"form-group input-group\">
						<span class=\"input-group-addon\">".LOCALE_currency_symbol."</span>
					".$form_edit->input_html('input','charge_min',$_POST['meta']['charge_min'],['placeholder'=>'0.00'])."
					</div>
				</div>
				<div class=\"col-md-4\">
					<label>Max</label>
					<div class=\"form-group input-group\">
						<span class=\"input-group-addon\">".LOCALE_currency_symbol."</span>
					".$form_edit->input_html('input','charge_max',$_POST['meta']['charge_max'],['placeholder'=>'0.00'])."
					</div>
				</div>
			</div>
		";

		return $html;
	}
	function admin_form_process() {
		global $class_module,$zulu;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$zulu->meta_update('module',$module_row['id'],'percent',sprintf("%.2f", $_POST['percent']));
		$zulu->meta_update('module',$module_row['id'],'charge_min',$_POST['charge_min']);
		$zulu->meta_update('module',$module_row['id'],'charge_max',$_POST['charge_max']);

		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		return ($module_row['info_client']?"<p>".stripslashes($module_row['info_client'])."</p>":null)."
        <p>Delivery cost is ".LOCALE_currency.number_format($this->checkout_ship_price(),2).".</p>";
	}
	function checkout_ship_price() {
		global $class_module,$zulu,$class_sale;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'field'=>'price,quantity'));
		foreach($cart_data as $cart_row) {
			$total_cart += ($cart_row['price']*$cart_row['quantity']);
		}
		if($total_cart>0&&$module_meta['percent']>0) {
			$perc = $module_meta['percent']/100;
			$ship_charge = $total_cart*$perc;
			if($ship_charge>$module_meta['charge_max']&&$module_meta['charge_max']>0) {
				$ship_charge = $module_meta['charge_max'];
			}
			if($ship_charge<$module_meta['charge_min']) {
				$ship_charge = $module_meta['charge_min'];
			}
		} else {
			$ship_charge = 0;
		}

		return $ship_charge;
	}
	function checkout_ship_process() {
		unset($_SESSION['CHECKOUT']['SHIP']);
		return true;
	}

}

?>
