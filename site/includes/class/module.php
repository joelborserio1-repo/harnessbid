<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: MODULE
class module {

	public $SQL_table = 'module';
	public $SQL_table_meta = 'module_meta';

	function __construct($config=[]) {
		global $db,$zulu;
		$this->db = $db;
		$this->zulu = $zulu;

		$this->include_path = [
			'payment'	=>	$_SERVER['DOCUMENT_ROOT'].MAIN_rel."includes/modules/payment/",
			'shipping'	=>	$_SERVER['DOCUMENT_ROOT'].MAIN_rel."includes/modules/shipping/"
		];
		$this->module_type = [
			1	=>	'payment',
			2	=>	'shipping'
		];

		$this->module_types = ['payment'=>['label'=>'Payment','code'=>'1'],'shipping'=>['label'=>'Shipping','code'=>'2']];
	}
	function module_data($config=array()) {
		global $class_user,$zulu;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['status']!=NULL || $config['status']=='0') {
			$sql_config['where'][] = "status = '".$config['status']."'";
		}
		if($config['type']!=NULL) {
			$sql_config['where'][] = "type = '".$config['type']."'";
		}
		if($config['class']!=NULL) {
			$sql_config['where'][] = "class = '".$config['class']."'";
			$sql_config['first'] = true;
		}
		if($config['token']!=NULL) {
			$sql_config['where'][] = "token = '".$config['token']."'";
			$sql_config['first'] = true;
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'sort ASC, status DESC, name ASC';
		}
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}

		return $zulu->table_data($this->SQL_table,$id,$sql_config);
	}
	function delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			if($identifier=='id') {
				$this->db->query("DELETE FROM ".$this->SQL_table_meta." WHERE identifier = '".$id."'");
			}
			return true;
		} else {
			return false;
		}
	}
	function module_new($config=array()) {
		global $class_user,$zulu;
		$data['user_id'] = $class_user->authorised->id;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['token'] = $zulu->serial();

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,array('token','stat_add','user_id','stat_update'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function module_edit($id,$config=array()) {
		global $class_user;
		$config['stat_update'] = time();
		if($id<1) {
			$data = $this->module_new();
			$id = $data['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function module_meta($id,$field=NULL) {
		return zulu::meta_value($this->SQL_table,$id,$field);
	}
	function module_status_info($status,$config=[]) {
		if($config['id']>0) {
			$module_row = $this->module_data(['id'=>$config['id']]);
			$status = $module_row['status'];
		}
		switch($status) {
			case '':
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Uninstalled';
				$data['label_change'] = 'Install';
				$data['class_change'] = 'success';
				break;
			case '0':
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Inactive';
				$data['label_change'] = 'Activate';
				$data['class_change'] = 'success';
				break;
			case '1':
				$data['class'] = 'opt opt-success';
				$data['icon'] = 'fa-check';
				$data['label'] = 'Active';
				$data['label_change'] = 'Deactivate';
				$data['class_change'] = 'danger';
				break;
		}
		return $data;
	}
	function include_path($id) {
		$module_row = $this->module_data(['id'=>$id]);
		return $this->include_path[$this->module_type[$module_row['type']]]."/".$module_row['class'].".php";
	}
	function order_pay_block($config=[]) {
		$module_data = $this->module_data(['type'=>1,'status'=>1]);
		$html = "";
		foreach($module_data as $module_row) {
			require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();
            if(method_exists($module,'checkout_toggle_module')) {
                $toggle_module = $module->checkout_toggle_module(['sale_total'=>$config['sale_total']]);
            } else {
                $toggle_module = true;
            }
            if($toggle_module) {
                if(method_exists($module, 'order_pay_now')) {
                    $html .= "<div class=\"col text-center\"><div class=\"box text-center\">".$module->order_pay_now()."</div></div>";
                }
            }
		}
		if($html != "") $html = "<div class=\"coltable col3 padcol float\">".$html."</div>";
		else $html = false;

		return $html;
	}
	function checkout_select($type=1,$config=[]) {
		global $zulu, $form_edit;

		$module_data = $this->module_data(['type'=>$type,'status'=>1]);
		$count = 0;
		foreach($module_data as $key=>$module_row) {
			$meta = $zulu->meta_array($this->module_meta($module_row['id']));
			require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();

			if($type == 2 && !$module->shipping_detail) {
                continue;
            }
            if(method_exists($module,'checkout_toggle_module')) {
                $toggle_module = $module->checkout_toggle_module(['sale_total'=>$config['sale_total']]);
            } else {
                $toggle_module = true;
            }
			if($toggle_module) {
				$serial = $zulu->serial(6);
                $selected = false;
				if(($_POST['module_'.$this->module_type[$type]] <= 0 && $_POST[$this->module_type[$type]] <= 0 && $key == 0) || $_POST['module_'.$this->module_type[$type]] == $module_row['id'] || $_POST[$this->module_type[$type]] == $module_row['id']) {
                    $selected = true;
                }
				$detail_content = $module->checkout_select_html(['selected'=>$selected]);

				$module_html .= "
                <li class='list-group-item".($selected?' selected':NULL)."'>
                    <div class='lgi-radio'>".$form_edit->input_html('radio', $this->module_type[$type], $module_row['id'], ['checked'=>$selected, 'id'=>'shipping-method-'.$serial])."</div>
                    <div class='lgi-label'><label for='shipping-method-".$serial."'>".stripslashes($module_row['name_client'])."</label></div>
                </li>
                ".(trim($detail_content)?"<li class='list-group-item item-detail".($selected?' open':NULL)."'>
                    ".$module->checkout_select_html(['selected'=>$selected])."
                </li>":null);
				$count++;
			}
		}

		return ['html'=>$module_html,'count'=>$count];
	}

	function basket_extra($config=[]) {
		global $zulu;

		$module_data = $this->module_data(['type'=>1,'status'=>1]);
		$count = 0;
		$module_html = '';
		foreach($module_data as $key=>$module_row) {
			$meta = $zulu->meta_array($this->module_meta($module_row['id']));
			require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();

			if(method_exists($module,'basket_view')) {

				if(method_exists($module,'checkout_toggle_module')) {
					$toggle_module = $module->checkout_toggle_module(['sale_total'=>$config['sale_total']]);
				} else {
					$toggle_module = true;
				}
				if($toggle_module) {
					$module_html .= "<div class='price-extra-row'>".$module->basket_view($config)."</div>";
					$count++;
				}
			}
		}

		return ['html'=>$module_html,'count'=>$count];
	}

	function pos_select($config=[]) {
		global $zulu;

		$hide_card = false;
		$hide_cash = false;

		$module_data = $this->module_data(['type'=>1,'status'=>1]);
		foreach($module_data as $key=>$module_row) {
			$meta = $zulu->meta_array($this->module_meta($module_row['id']));
			require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();

			if($module->pointofsale) {
				$button_data = $module->pos_button();
				$module_html .= "<div class=\"col-sm-6\"><button data-module-class=\"".$module->class_name."\" data-module=\"".$module_row['id']."\" data-val=\"".$module_row['name_client']."\" data-prompt=\"1\" type=\"button\" class=\"bt-method btn btn-block btn-default btn-pay-module btn-".$module->class_name."\"><i class=\"fas fa-".$button_data['icon']."\"></i> ".$button_data['label']."</button></div>";
				if($button_data['event']['select']!=NULL) {
					$pos_js .= "$('.btn-".$module->class_name."').click(function() { ".$button_data['event']['select']."(); return false; }); ";
				}
				if(method_exists($module,'pos_js')) {
					$pos_js .= $module->pos_js();
				}
				if($module->pointofsale_replace_card) {
					$hide_card = true;
				}
				if($module->pointofsale_replace_cash) {
					$hide_cash = true;
				}
			} else {
				$m_offset += 1;
			}
		}

		return ['html'=>$module_html,'hide_card'=>$hide_card,'hide_cash'=>$hide_cash,'count'=>count($module_data)-$m_offset,'js'=>$pos_js];
	}
    function ipn_check() {
        $module_data = $this->module_data(['type'=>1,'status'=>1]);
        foreach($module_data as $module_row) {
			require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();
			if(method_exists($module, 'ipn_validate')) {
				$result = $module->ipn_validate();
                if($result['success']) {
                    return $result;
                }
			}
		}
        return false;
    }

	function delivery_options() {
        $return = ['ship'=>false, 'pickup'=>false];

        $module_data = $this->module_data(['type'=>2,'status'=>1]);
        foreach($module_data as $module_row) {
            require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();
            if($module->shipping_detail) {
                $return['ship'] = true;
            } else {
                $return['pickup'] = true;
            }
        }

        return $return;
    }

    function pickup_option_html() {
        $options = "";
        $module_data = $this->module_data(['type'=>2,'status'=>1]);
        foreach($module_data as $module_row) {
            require_once $this->include_path($module_row['id']);
			$module = new $module_row['class']();
            if(!$module->shipping_detail && method_exists($module, 'checkout_pickup_locations')) {
                $options .= $module->checkout_pickup_locations();
            }
        }
        return $options;
    }

	function init($module_id) {
		global $zulu;
		$module_payment_row = $this->module_data(['id'=>$module_id]);
		if($module_payment_row['id']<=0 || $module_payment_row['status']!=1) {
			$zulu->fatal_error("Module error","The linked module to this is inactive / does not exist.");
		}

		//-- Include Module
		if(!class_exists($module_payment_row['class'])) {
			require_once $this->include_path($module_payment_row['id']);
		}
		return new $module_payment_row['class']();
	}

}
