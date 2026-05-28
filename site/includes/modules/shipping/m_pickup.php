<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//*********************NOTE:**********************//
//This is a open-source module built into Zulu    //
//Shopfront. It is highly illegal to resell this  //
//module.										  //
//************************************************//

//MODULE TYPE: Shipping
//MODULE NAME: ZULU Shopfront - Default Pickup Shipping

//Module Functions***********

class m_pickup {

	function __construct($config=[]) {

		$this->type = 2;
		$this->class_name = 'm_pickup';
		$this->filename = 'm_pickup.php';
		$this->title = 'Pickup';
		$this->title_client = 'Pickup';
		$this->info_client = 'Please allow 3 days before collection.';
		$this->meta_field_count = 0;
		$this->meta_column_count = 2;
        $this->shipping_detail = false;
	}

	//##################################### INSTALL/UNINSTALL
	function install() {
		global $class_module,$zulu,$class_setting;

		$check = $class_module->module_data(['class'=>$this->class_name]);
		if($check['id'] <= 0) {
			$result = $class_module->module_edit(0,['type'=>$this->type,'name'=>$this->title,'name_client'=>$this->title_client,'info_client'=>$this->info_client,'class'=>$this->class_name,'file'=>$this->filename]);
			if($result['success']) {

				//Load default rule
                $company = $address = '';
                if($class_setting->data['ws_site_name']) {
                    $company = $class_setting->data['ws_site_name'];
                }
                if($class_setting->data['ws_addr_addr']) {
                    $address = $zulu->compile(', ', [$class_setting->data['ws_addr_addr'], $class_setting->data['ws_addr_suburb'], $class_setting->data['ws_addr_city']]);
                }
                $zulu->meta_update('module',$result['id'],'1_name',$company);
				$zulu->meta_update('module',$result['id'],'1_address',$address);

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
			array("Address",array('class'=>array(''))),
			array("Actions",array('class'=>array('right')))
		];
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];

		$count = (count($_POST['meta'])-$this->meta_field_count) / $this->meta_column_count;
		for($i=1; $i<=$count; $i++) {
			$table_row[] = array("content" => [
				array($form_edit->input_html('input','option[name][]',stripslashes($_POST['meta'][''.$i.'_name']))),
				array($form_edit->input_html('input','option[address][]',stripslashes($_POST['meta'][''.$i.'_address']))),
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
				$(\"#module-options\").append('<tr><td>".$form_edit->input_html('input','option[name][]')."</td><td>".$form_edit->input_html('input','option[address][]')."</td><td class=\"right w80\"><a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title=\"Clear row\"><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title=\"Remove row\"><i class=\"fas fa-times\"></i></a></td></tr>');
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
				$zulu->meta_update('module',$module_row['id'],$i.'_address',addslashes($_POST['option']['address'][$key]));
				$i++;
			}
		}
		return ['success'=>true];
	}

	//##################################### CHECKOUT FUNCTIONS
	function checkout_select_html() {
		global $class_module, $zulu, $form_edit;

		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

		$price_arr = $this->checkout_ship_price(true);
        $count = (count($module_meta)-$this->meta_field_count) / $this->meta_column_count;
        $module_options = [0=>'Select a location...'];
        for($i=1; $i<=$count; $i++) {
            $module_options[$i] = stripslashes($module_meta[$i.'_name'])." | ".stripslashes($module_meta[$i.'_address']);
        }
        $output = $form_edit->input_html('select','module_shipping_option[poa]',$_SESSION['CHECKOUT']['SHIP']['OPTION'],['option'=>$module_options]);

		return "
			<h3>".stripslashes($module_row['name_client'])."</h3>
			<p>".stripslashes($module_row['info_client'])."</p>
			".$output;
	}
	function checkout_ship_price($array=false) {
		global $class_module,$zulu,$class_sale;

		unset($_SESSION['CHECKOUT']['SHIP']['RESELECT']);
		$module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        $price = [];
        $count = (count($module_meta)-$this->meta_field_count) / $this->meta_column_count;
        for($i=1; $i<=$count; $i++) {
            $price[$i] = 0;
        }

        if(!$array) {
            if($_SESSION['CHECKOUT']['SHIP']['OPTION']>0){
                $price = sprintf("%.2f", $price[$_SESSION['CHECKOUT']['SHIP']['OPTION']]);
            }else {
                $price = '0.00';
                $_SESSION['CHECKOUT']['SHIP']['RESELECT'] = true;
            }
        }

		return $price;
	}
	function checkout_ship_process() {
		global $class_module,$zulu;

		if($_POST['module_shipping_option']['poa'] > 0) {
			$_SESSION['CHECKOUT']['SHIP']['OPTION'] = $_POST['module_shipping_option']['poa'];
			$module_row = $class_module->module_data(['class'=>$this->class_name]);
			$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));
			$_SESSION['CHECKOUT']['SHIP']['NAME'] = $module_meta[$_POST['module_shipping_option']['poa'].'_name'];
		}
		return true;
	}
	function checkout_ship_validate() {
        if($_POST['module_shipping_option']['poa'] <= 0) {
            return ['success'=>false,'msg'=>"No pickup location was selected, please choose one to continue."];
        }
        return ['success'=>true];
    }
    function checkout_pickup_locations() {
        global $class_module, $zulu, $form_edit;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        $options = [];
        $count = (count($module_meta)-$this->meta_field_count) / $this->meta_column_count;
        for($i=1; $i<=$count; $i++) {
            $serial = $zulu->serial();
            $options[] = "<li class='list-group-item'>
                <div class='lgi-radio'>".$form_edit->input_html('radio', 'pickup_option', $module_row['id'].'-'.$i, ['checked'=>($_POST['pickup_option']==$module_row['id'].'-'.$i?true:false), 'id'=>'pickup-location-'.$serial])."</div>
                <div class='lgi-label'><label for='pickup-location-".$serial."'>".$module_meta[$i."_name"]."<br><small>".$module_meta[$i."_address"]."</small></label></div>
            </li>";
        }

        return implode('',$options);
    }
    function checkout_pickup_location($option) {
        global $class_module, $zulu, $form_edit;

        $module_row = $class_module->module_data(['class'=>$this->class_name]);
		$module_meta = $zulu->meta_array($class_module->module_meta($module_row['id']));

        if(isset($module_meta[$option."_name"])) {
            $return = [
                'name'  =>  $module_row['name_client'].": ".$module_meta[$option."_name"],
                'note'  =>  $module_meta[$option."_address"],
            ];
        } else {
            $return = null;
        }

        return $return;
    }

}

?>
