<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: SALE
class sale {

	public $SQL_table_sale = 'sale';
	public $SQL_table_sale_meta = 'sale_meta';
	public $SQL_table_sale_line = 'sale_line';
	public $SQL_table_sale_cart = 'sale_cart';
	public $SQL_table_sale_refund = 'sale_refund';
	public $SQL_table_sale_coupon = 'sale_coupon';
	public $DEFAULT_due = 7;

	function __construct($config=[]) {
		global $db,$zulu,$class_setting;
		$this->db = $db;
		$this->config = new stdClass();

		$setting_data = $class_setting->data;

		$this->zulu = $zulu;
		$this->gst_disable = ($setting_data['tax_disable']>0?true:false);
		$this->gst_excl = ($setting_data['tax_method']<=0?true:false);
		$this->gst_rate = (is_numeric($setting_data['tax_rate'])?$setting_data['tax_rate']:0);
		$this->gst_label = $setting_data['tax_label'];
		$this->currency_symbol = $zulu->defaults->currency_symbol;

		if($this->gst_disable) {
			$this->gst_rate = 0;
		}

		$this->config->payment = ['online'=>'Credit Card','bank'=>'Bank Deposit','cash'=>'Cash on collection'];
		$this->config->ship = ['pickup'=>['cost'=>0,'label'=>'Collect at office'],'courier'=>['cost'=>5,'label'=>'Courier to me']];

		$this->coupon_types = ["percent"=>"Percent","fixed"=>"Fixed Amount","product"=>"Product","credit"=>"Credit"];
        $this->coupon_type = ["coupon"=>"Coupon","voucher"=>"Voucher"];
	}
	function construct() {
		global $zulu,$class_setting;

		$setting_data = $class_setting->data;

		$this->gst_disable = ($setting_data['tax_disable']>0?true:false);
		$this->gst_excl = ($setting_data['tax_method']<=0?true:false);;
		$this->gst_rate = (is_numeric($setting_data['tax_rate'])?$setting_data['tax_rate']:0);
		$this->gst_label = $setting_data['tax_label'];
		$this->currency_symbol = $zulu->defaults->currency_symbol;

		if($this->gst_disable) {
			$this->gst_rate = 0;
		}
	}
	function admin_link($id=0) {
		global $zulu;

		if($id==0) {
			$data = $this->vars->data_row;
		} else {
			$data = $this->sale_data(['id'=>$id]);
		}
		return "<a href=\"".$zulu->link_page('sale',array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\">Sale ".$data['reference']."</a>";
	}
	function sale_data($config=array()) {
		global $class_user,$zulu,$db;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['reference']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "reference = '".$config['reference']."'";
		}
		if($config['job_id']!=NULL) {
			$sql_config['where'][] = "job_id = '".$config['job_id']."'";
		}
		if($config['user_id']!=NULL) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		}
		if($config['date_max']>0) {
			$sql_config['where'][] = "date <= ".$config['date_max'];
		}
		if($config['date_min']>0) {
			$sql_config['where'][] = "date >= ".$config['date_min'];
		}
		if($config['client_id']>0) {
			$sql_config['where'][] = "client_id = ".$config['client_id'];
		}
		if($config['coupon_id']!=NULL) {
			$sql_config['where'][] = "coupon_id = '".$config['coupon_id']."'";
		}
		if(count($config['id_in'])>0) {
			$sql_config['where'][] = "id IN(".implode(',',$config['id_in']).")";
		}
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if($config['status']!=NULL) {
			if(is_array($config['status'])) {
				$sql_config['where'][] = "status IN(".implode(",",$config['status']).")";
			} else {
				$sql_config['where'][] = "status = '".$config['status']."'";
			}
		}
		if($config['status_exclude']!=NULL) {
			if(is_array($config['status_exclude'])) {
				$sql_config['where'][] = "status NOT IN(".implode(",",$config['status_exclude']).")";
			} else {
				$sql_config['where'][] = "status != '".$config['status_exclude']."'";
			}
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(isset($config['row_start'])) {
			$sql_config['start'] = $config['row_start'];
		}
		if(isset($config['row_limit'])) {
			$sql_config['limit'] = $config['row_limit'];
		}
		if($config['search']!=NULL) {
			$search = addslashes(strtolower($config['search']));
			$sql_config['where'][] = "(MATCH(name) AGAINST ('".$search."' IN BOOLEAN MODE) OR reference LIKE '%".$search."%' OR name LIKE '%".$search."%')";
		}
		if(isset($config['paid'])) {
			$sql_config['join'][] = "sale_meta AS sm_p ON sale.id = sm_p.identifier";
			if(!$config['paid']) {
				$sql_config['where'][] = "sm_p.field = 'stat_paid'";
				$sql_config['where'][] = "sm_p.value = '0'";
			} else {
				$sql_config['where'][] = "sm_p.field = 'stat_paid'";
				$sql_config['where'][] = "sm_p.value = '1'";
			}
			if(count($config['field'])<=0) {
				$sql_config['field'] = ['sale.id AS id','sale.*'];
			}
		}
		if(isset($config['xero'])) {
			if(!$config['xero']) {
				$sql_config['where'][] = "NOT EXISTS (SELECT id FROM ".$this->SQL_table_sale_meta." WHERE `field` = 'xero_link' AND `identifier` = `".$this->SQL_table_sale."`.`ID`)";
			} else {
				$sql_config['join_left'][] = "sale_meta AS sm_x ON sale.id = sm_x.identifier";
				$sql_config['where'][] = "sm_x.field = 'xero_link'";
				$sql_config['where'][] = "sm_x.value != ''";
			}
		}
		if(isset($config['sent'])) {
			if(!$config['sent']) {
				$sql_config['where'][] = "NOT EXISTS (SELECT id FROM ".$this->SQL_table_sale_meta." WHERE `field` = 'xero_sent' AND `identifier` = `".$this->SQL_table_sale."`.`ID`)";
			} else {
				$sql_config['join_left'][] = "sale_meta AS sm_s ON sale.id = sm_x.identifier";
				$sql_config['where'][] = "sm_s.field = 'xero_sent'";
				$sql_config['where'][] = "sm_s.value = '1'";
			}
		}
		if($config['order']!=NULL) {
			$sql_config['sort'] = $config['order'];
		} elseif($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'reference DESC, id DESC';
		}
		return $zulu->table_data($this->SQL_table_sale,$id,$sql_config);
	}
	function sale_line_parent($id,$config=[]) {
		if($config['object']!=NULL) {
			$id = 0;
			$arr = array('object'=>$config['object'],'object_id'=>$config['object_id']);
		} else {
			$arr = ['id'=>$id];
		}
		if($config['latest']) {
			$arr['latest'] = $config['latest'];
		}
		$data = $this->sale_line_data($arr);
		if($data[0]['id']>0) {
			$data = $data[0];
		}
		return $data['sale_id'];
	}
	function sale_line_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['sale_id']>0) {
			$sql_config['where'][] = "sale_id = '".$config['sale_id']."'";
		}
		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['sku']!=NULL) {
			$sql_config['where'][] = "sku = '".$config['sku']."'";
		}
		if($config['object_id']!=NULL) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['object']!=NULL) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		if($config['custom']!=NULL) {
			$sql_config['where'][] = "custom = '".$config['custom']."'";
		}
		if($config['dataset']=='product_id.quantity') {
			$sql_config['key_field'] = 'product_id';
			$sql_config['value_field'] = 'quantity';
			$sql_config['field'] = array('quantity','product_id');
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['latest']) {
			$sql_config['sort'] = 'id DESC';
		}
		//$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		$data = zulu::table_data($this->SQL_table_sale_line,$id,$sql_config);
		return $data;
	}
	function payment_has($id,$config=array()) {
		global $class_user;
		$sql_config['where'][] = "sale_id = '".$id."'";
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		$data = zulu::table_data('sale_payment',0,$sql_config);
		unset($sql_config);

		if($config['nil_override']) {
			return (count($data)<1?false:true);
		} else {
			$sql_config['where'][] = "sale_id = '".$id."'";
			$line_data = zulu::table_data('sale_line',0,$sql_config);

			foreach($line_data as $d) {
				if($d['total']>0 && count($data)<1) {
					return false;
				}
				return true;
			}
		}
	}
	function payment_summary($amount,$config=array()) {
		global $zulu;
		$this->construct();
		$tax_no = (isset($config['tax_disable'])?$config['tax_disable']:$this->gst_disable);
		$tax_meth = (isset($config['tax_method'])?($config['tax_method']<=0?true:false):$this->gst_excl);
		$tax_rate = (isset($config['tax_rate'])?$config['tax_rate']:$this->gst_rate);
		if(isset($config['sale_id']) && $config['sale_id'] > 0) {
			$sale_row = $this->sale_data(['id'=>$config['sale_id'],'ovr_user_id'=>true,'field'=>['tax_method','tax_rate','tax_disable']]);
			$tax_meth = ($sale_row['tax_method']<=0?true:false);
			$tax_rate = $sale_row['tax_rate'];
			$tax_no = ($sale_row['tax_disable']>0?true:false);
		}
		if(!$tax_no) {
			//-- Using tax YES
			if($tax_meth && (!isset($config['placed']) || !$config['placed'])) {
				$pay_tax = $amount * ($tax_rate / 100);
				$pay_subtotal = $amount;
                $pay_total = $amount + $pay_tax;
			} else {
				$pay_subtotal = $amount / (1 + ($tax_rate / 100));
				$pay_tax = $amount - $pay_subtotal;
                $pay_total = $amount;
			}
		} else {
			//-- Using tax NO
			$pay_subtotal = $amount;
			$pay_tax = 0;
            $pay_total = $amount;
		}
        $data = array(
            'subtotal_raw'  =>  $pay_subtotal,
            'tax_raw'       =>  $pay_tax,
            'total_raw'     =>  $pay_total,
            'subtotal'      =>  $zulu->dollar($pay_subtotal),
            'tax'           =>  $zulu->dollar($pay_tax),
            'total'         =>  $zulu->dollar($pay_total)
        );

		return $data;
	}
	function payment_create($config=array()) {
		global $class_user,$class_client,$class_book,$class_room,$class_setting,$class_subscribe,$class_product,$zulu;
		$array = ["tax_method"=>true]; //-- force payment as tax inclusive
		$pay_value = $this->payment_summary($config['pay_total'],$array);

		if($config['sale_id']<=0) {
			return ['success'=>false,'message'=>'No sale ID defined.'];
		}

		$query = "INSERT INTO sale_payment (token,sale_id,user_id,admin_id,date,info,reference,pay_subtotal,pay_tax,pay_total,method,method_id,method_data,module_id,module_data,valid,stat_ip,stat_add,stat_update) VALUES ('".zulu::serial()."','".$config['sale_id']."','".$class_user->authorised->id."','".$config['admin_id']."','".time()."','".$config['info']."','".$config['reference']."','".$pay_value['subtotal']."','".$pay_value['tax']."','".$pay_value['total']."','".$config['method']."','".$config['method_id']."','".$config['method_data']."','".$config['module_id']."','".$config['module_data']."','".($config['valid']?1:0)."','".$_SERVER['REMOTE_ADDR']."','".time()."','".time()."')";

		//Execute
		if($this->db->query($query)) {

			if($config['complete']) {
				$this->complete($config['sale_id']);
			}
			//Email?
			if($config['email_admin']) {
				$this->sale_receipt_mail($config['sale_id'],['admin'=>true]);
			}
			if($config['email_client']) {
				$this->sale_receipt_mail($config['sale_id']);
			}

			//--Update Meta
			$this->sale_gen_info($config['sale_id']);

			//Return
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function sale_gen_info($id) {
		global $zulu;
		if($id==0) {
			return false;
		}
		$balance = $this->sale_balance($id);
		$zulu->meta_update('sale',$id,'stat_paid_balance',$balance);
		$zulu->meta_update('sale',$id,'stat_total',$this->sale_total($id));
		$zulu->meta_update('sale',$id,'stat_paid_total',$this->sale_total_paid($id));
		$zulu->meta_update('sale',$id,'stat_paid',($balance<=0?'1':'0'));

		return true;
	}
	function payment_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['sale_id']!=NULL) {
			$sql_config['where'][] = "sale_id = '".$config['sale_id']."'";
		}
		if($config['module_id']!=NULL) {
			$sql_config['where'][] = "module_id = '".$config['module_id']."'";
		}
		if(!$config['ovr_user_id']) {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['where'][] = "valid = '1'";
		$sql_config['sort'] = "id DESC";
		return zulu::table_data('sale_payment',$id,$sql_config);
	}
	function sale_tax_setting($id) {
		$data = zulu::table_data($this->SQL_table_sale,$id,array("field"=>array("tax_rate","tax_method","tax_disable")));
		return ['tax_rate'=>$data['tax_rate'],'tax_method'=>$data['tax_method'],'tax_disable'=>($data['tax_disable']>0?true:false)];
	}
	function sale_total_paid($id) {
		global $zulu;
		$data = zulu::table_data('sale_payment',0,array('where'=>array("sale_id = '$id'","valid = 1"),"field"=>array("SUM(pay_total) AS master_total")));
		return $zulu->dollar(($data[0]['master_total']==NULL?0:$data[0]['master_total']));
	}
	function sale_total_discount($id) {
		global $zulu;
		$sale = $this->sale_data(array('id'=>$id));
        $sale_meta = $zulu->meta_array($this->sale_meta($id));
		if($sale['coupon_id'] > 0) {
			$coupon = $this->coupon_data(array('id'=>$sale['coupon_id']));
			if($coupon['discount_object_id'] == 0) {
                $data = zulu::table_data($this->SQL_table_sale_line,0,array('where'=>array("sale_id = '$id'"),"field"=>array("SUM(total) AS master_total")));
				$total = ($data[0]['master_total']==NULL?0:$data[0]['master_total']+$ship['value']);
				if($coupon['discount_type'] == 'percent') {
					$coupon_amount = $total*($coupon['discount_amount']/100);
				} else if($coupon['discount_type'] == 'fixed') {
                    if($sale_meta['coupon_amount'] > 0) {
                        $coupon_amount = $sale_meta['coupon_amount'];
                    } else {
                        $coupon_amount = $coupon['discount_amount'];
                        if($total < $coupon_amount) {
                            $coupon_amount = $total;
                        }
                    }
				}
			}
		}

		$data = zulu::table_data($this->SQL_table_sale_line,0,array('where'=>array("sale_id = '$id'"),"field"=>array("SUM(discount) AS master_total")));
		if($data[0]['master_total']>0) {
			$discount = $data[0]['master_total'];
		} else {
			$discount = $coupon_amount;
		}
		return $zulu->dollar($discount);
	}
	function sale_total($id,$config=[]) {
        global $zulu;

		$sale = $this->sale_data(array('id'=>$id));
        $sale_meta = $zulu->meta_array($this->sale_meta($id));
		$tax = $this->sale_tax_setting($id);

		if($config['cached']) { //-- loads meta value
			$total_meta = zulu::meta_value($this->SQL_table_sale,$id,'stat_total');
			$data[0]['master_total'] = $total_meta['value'];
		} else {
			$data = zulu::table_data($this->SQL_table_sale_line,0,array('where'=>array("sale_id = '$id'"),"field"=>array("SUM(total) AS master_total")));
		}
		$ship = zulu::meta_value($this->SQL_table_sale,$id,'ship_price');
		$coupon_amount = 0;
		$total = ($data[0]['master_total']==NULL?0:$data[0]['master_total']+$ship['value']);

		//Coupon
		if($sale['coupon_id'] > 0) {
			$coupon = $this->coupon_data(array('id'=>$sale['coupon_id']));
			if($coupon['discount_object_id'] == 0) {
				if($coupon['discount_type'] == 'percent') {
					$coupon_amount = $total*($coupon['discount_amount']/100);
				} else if($coupon['discount_type'] == 'fixed') {
                    if($sale_meta['coupon_amount'] > 0) {
                        $coupon_amount = $sale_meta['coupon_amount'];
                    } else {
                        if($coupon['discount_amount'] > $total) {
                            $coupon['discount_amount'] = $total;
                        }
                        $coupon_amount = $coupon['discount_amount'];
                    }
				}
			}
		}

		//Do tax on sale total val
		$subtotal = $total - $coupon_amount;
		$ps = $this->payment_summary($subtotal,$tax);
		return $ps['total'];
	}
	function sale_balance($id) {
		$sale_total = $this->sale_total($id);
		$sale_paid = $this->sale_total_paid($id);
		return ($sale_total-$sale_paid);
	}

	function sale_new($config=array()) {
		global $class_user,$class_setting,$zulu;

		$data['user_id'] = ($config['user_id']>0?$config['user_id']:$class_user->authorised->id);
		$data['token'] = $zulu->serial();
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['date'] = $config['date'];
		$data['tax_method'] = $class_setting->data['tax_method'];
		$data['tax_rate'] = $class_setting->data['tax_rate'];
		$data['tax_disable'] = $class_setting->data['tax_disable'];
		if(!isset($data['admin_id'])&&$data['admin_id']!=0) {
			$data['admin_id'] = $class_user->authorised->id;
		}

		$query = "INSERT INTO ".$this->SQL_table_sale." ".$this->db->build(2,$this->db->field_array($data),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function sale_line_function($id,$config=array()) {
		global $class_renew,$class_sale,$class_user,$class_client,$class_product,$zulu,$class_setting,$class_schedule,$class_book,$class_rule;

		$data = $this->sale_line($id);
		$sale_data = $this->sale_data(array("id"=>$id));
		$sale_balance = $this->sale_balance($sale_data['id']);
		$sale_meta = $zulu->meta_array($this->sale_meta($sale_data['id']));
		$client_data = $class_client->client_data(array("id"=>$sale_data['client_id']));

		foreach($data as $data_row) {
			$complete_count = $data_row['count_complete'];
			$p_filter = ($data_row['product_id']>0?"id = '".$data_row['product_id']."'":($data_row['sku']!=NULL?"sku = '".$data_row['sku']."'":NULL));
			$custom = unserialize($data_row['custom']);
			if($p_filter!=NULL) {
				$prod_data = zulu::table_data("product",0,array("where"=>array($p_filter,"user_id = '".$class_user->authorised->id."'")));
				$prod_meta = $class_product->product_meta($prod_data[0]['id']);
				$line_has_product = true;
			} else {
				$line_has_product = false;
			}

			$config_array = [
				'value'		=>	"-".$data_row['quantity'],
				'object'	=>	'sale',
				'object_id'=>	$id,
				'note'		=>	"Sale #".$sale_data['reference'],
			];
			if($complete_count<=0) {
				//-- count 0 statement --//
				if($line_has_product) {
					if($data_row['quantity_r']!=$data_row['quantity']) {
                        if($class_setting->data['ws_shop_stock_show']) {
                            $stock = $class_product->stock_adjust($prod_data[0]['id'],$config_array);
                        }
						$this->db->query("UPDATE sale_line SET quantity_r = '".$data_row['quantity']."' WHERE id='".$data_row['id']."'");
					}
					if($prod_meta['object']['value']=='membership') {		// make/renew subscription
						$template_data = $class_renew->template_data(array('id'=>$prod_meta['object_id']['value']));
						/*if($custom['renew_id'] > 0) { //-- renew membership
							$renew_data = $class_renew->renew_data(array('id'=>$custom['renew_id']));
							$curr_renew = ($renew_data['renew_next']>time()?$renew_data['renew_next']:time());
							$renew_next = strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']]."",$curr_renew);
							$config = ['renew_next'=>$renew_next];
							$result = $class_renew->renew_edit($custom['renew_id'],$config);
							$class_renew->renew_log_new($custom['renew_id'],['renew_time'=>time(),'renew_from'=>$renew_data['renew_next'],'renew_to'=>$renew_next,'object'=>'sale_line','object_id'=>$data_row['id']]);
							$this->db->query("UPDATE sale_line SET object='membership', object_id='".$custom['renew_id']."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
						} else if(!$config['token_bill']&&!isset($custom['switch']['current'])) { //-- create membership
							$config = array('user_id'=>$sale_data['user_id'],'client_id'=>$sale_data['client_id'],'template_id'=>$prod_meta['object_id']['value'],'product_id'=>$prod_data[0]['id'],'auto_renew'=>'1','status'=>'1','renew_next'=>strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']].""));
							$result = $class_renew->renew_edit(0,$config);
							$this->db->query("UPDATE sale_line SET object='membership', object_id='".$result['id']."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
							$insert_renew_id = $result['id'];
						}*/
						if(!$config['token_bill']&&!isset($custom['switch']['current'])&&!isset($custom['renew_id'])) { //-- create membership
							$config = array('user_id'=>$sale_data['user_id'],'client_id'=>$sale_data['client_id'],'template_id'=>$prod_meta['object_id']['value'],'product_id'=>$prod_data[0]['id'],'auto_renew'=>'1','status'=>0,'renew_next'=>strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']].""));
							$result = $class_renew->renew_edit(0,$config);
							$this->db->query("UPDATE sale_line SET object='membership', object_id='".$result['id']."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
							$insert_renew_id = $result['id'];
						}
					}
				}

				//-- switch membership
				/*if($custom['switch']['current'] != NULL) {
					$renew_data = $class_renew->renew_data(array('id'=>$custom['switch']['current']));
					$renew_new = ['status'=>3,'auto_renew'=>0,'renew_next'=>time()];
					$result = $class_renew->renew_edit($custom['switch']['current'],$renew_new);

					$renew_new = ['status'=>0,'auto_renew'=>1,'renew_next'=>$renew_data['renew_next'],'product_id'=>$prod_data[0]['id'],'template_id'=>$prod_meta['object_id']['value'],'client_id'=>$renew_data['client_id']];
					$result = $class_renew->renew_edit(0,$renew_new);
					$this->db->query("UPDATE sale_line SET object='membership', object_id='".$result['id']."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
					$insert_renew_id = $result['id'];
				}*/

				//-- Create Schedule Booking
				/*if($prod_meta['object']['value']=='sche_book'&&$data_row['object']!='sche_book'&&$class_setting->data['sche_pay_required']!=1) {
					$post = [
						'client_id'	=>	$sale_data['client_id'],
						'asset_id'	=>	$custom['asset_id'],
						'date_start'	=>	$custom['range'][0],
						'date_end'	=>	$custom['range'][1],
						'reference'	=>	$custom['reference'],
						'tentative'	=>	false,
						'_meta'			=>	[
							'fee_total'		=>	$custom['fee_total'],
							'sale_id'		=>	$sale_data['id'],
							'description'	=>	"Online booking - Sale #".$sale_data['reference']." - ",
						]
					];
					//-- Extra meta from custom arr
					if($custom['sche_meta']) {
						foreach($custom['sche_meta'] as $smk=>$smv) {
							$post['_meta'][$smk] = $smv;
						}
					}
					$new_book = $class_schedule->book_edit(0,$post,$config);
					$sche_id = $new_book['id'];
					if($sche_id>0) {
						$this->sale_line_edit($data_row['id'],[
							'object'	=>	'sche_book',
							'object_id'	=>	$sche_id,
						]);
						$zulu->meta_update('sale',$sale_data['id'],'type','sale');
						$zulu->meta_update('sale',$sale_data['id'],'book_id',$sche_id);
					}
				}*/
			}
			//-- end count 0 statement --//

			//-- balance 0 statement --//
			if($sale_balance<=0) {
				if(!$sale_meta['sale_paid_rule_generate']){
					$class_rule->generate_rule_from_auto('sale_paid', $sale_data['client_id']);
					$zulu->meta_update('sale',$sale_data['id'],'sale_paid_rule_generate', '1');
				}
				if($data_row['object']=='membership'&&$insert_renew_id<=0) {
					$renew_data = $class_renew->renew_data(array('id'=>$data_row['object_id']));
					if($renew_data['status']==0) {
						$insert_renew_id = $renew_data['id']; //sets renew id for objects that are status 0 (i.e. pending activation)
					}
				}

				//-- Make / Renew Subscription
				if($prod_meta['object']['value']=='membership') {
					$template_data = $class_renew->template_data(array('id'=>$prod_meta['object_id']['value']));
					if($insert_renew_id>0) { //-- set ACTIVE for subscription
						$renew_next = strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']]);
						$result = $class_renew->renew_edit($insert_renew_id,['status'=>1,'renew_first'=>time(),'renew_next'=>$renew_next]);
					}
				}
				if($custom['renew_id'] > 0 && $custom['renew_key']!='') { //-- renew membership
					$id_renew = $custom['renew_id'];
					$class_renew->get($id_renew);
					$class_renew->apply_renewal_period(['key'=>$custom['renew_key']]);
				}

				//-- Make / Renew Subscription
				/*if($prod_meta['object']['value']=='membership') {
					$template_data = $class_renew->template_data(array('id'=>$prod_meta['object_id']['value']));
					if($insert_renew_id>0) { //-- set ACTIVE for subscription
						$renew_next = strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']]);
						$result = $class_renew->renew_edit($insert_renew_id,['status'=>1,'renew_first'=>time(),'renew_next'=>$renew_next]);
					}
				}*/
				/*if($custom['renew_id'] > 0) { //-- renew membership
					$id_renew = $custom['renew_id'];
					$renew_data = $class_renew->renew_data(array('id'=>$custom['renew_id']));
					if($renew_data['template_id']>0) {
						$template_data = $class_renew->template_data(array('id'=>$renew_data['template_id']));
					}
					if(!isset($template_data['renew_interval'])) {
						$template_data['renew_interval'] = $renew_data['renew_interval'];
					}
					if(!isset($template_data['renew_scale'])) {
						$template_data['renew_scale'] = $renew_data['renew_scale'];
					}
					$curr_renew = ($renew_data['renew_next']>time()?$renew_data['renew_next']:time());
					$renew_next = strtotime("+".$template_data['renew_interval']." ".$class_renew->config->renew_scale[$template_data['renew_scale']]."",$curr_renew);
					$config = ['renew_next'=>$renew_next,'renew_last'=>$renew_data['renew_next']];
					$result = $class_renew->renew_edit($custom['renew_id'],$config);
					$class_renew->renew_log_new($custom['renew_id'],['renew_time'=>time(),'renew_from'=>$renew_data['renew_next'],'renew_to'=>$renew_next,'object'=>'sale_line','object_id'=>$data_row['id']]);
					unset($custom['renew_id']);

					$this->db->query("UPDATE sale_line SET custom = '".serialize($custom)."', object='membership', object_id='".$id_renew."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
				}*/

				//-- Create Schedule Booking
				if($prod_meta['object']['value']=='sche_book'&&$data_row['object']!='sche_book'&&$class_setting->data['sche_pay_required']==1) {
					$post = [
						'client_id'	=>	$sale_data['client_id'],
						'asset_id'	=>	$custom['asset_id'],
						'date_start'	=>	$custom['range'][0],
						'date_end'	=>	$custom['range'][1],
						'reference'	=>	$custom['reference'],
						'tentative'	=>	false,
						'_meta'			=>	[
							'fee_total'		=>	$custom['fee_total'],
							'sale_id'		=>	$sale_data['id'],
							'description'	=>	"Online booking - Sale #".$sale_data['reference']." - ",
						]
					];
					//-- Extra meta from custom arr
					if($custom['sche_meta']) {
						foreach($custom['sche_meta'] as $smk=>$smv) {
							$post['_meta'][$smk] = $smv;
						}
					}
					$new_book = $class_schedule->book_edit(0,$post,$config);
					$sche_id = $new_book['id'];
					if($sche_id>0) {
						$this->sale_line_edit($data_row['id'],[
							'object'	=>	'sche_book',
							'object_id'	=>	$sche_id,
						]);
						$zulu->meta_update('sale',$sale_data['id'],'type','sale');
						$zulu->meta_update('sale',$sale_data['id'],'book_id',$sche_id);
						$class_rule->generate_rule_from_auto('book_confirmed', $sale_data['client_id']);
						$zulu->meta_update('sche_book',$sche_id ,'book_confirmed_rule_generate', '1');

					}
				}
				//-- Create Tickets
				if($data_row['object']=='ticket_temp') {
					$ticket_data = $class_book->event_ticket_temp_data(array('id'=>$data_row['object_id']));
					if($ticket_data['client_id']<=0) {
						$class_book->event_ticket_temp_edit($data_row['object_id'],['client_id'=>$sale_data['client_id']]);
						$ticket_data = $class_book->event_ticket_temp_data(array('id'=>$data_row['object_id']));
					}
					$date_ticket_data = $class_book->event_date_ticket_data(array('id'=>$ticket_data['event_ticket_id']));
					$result = $class_book->event_ticket_edit(0,['client_id'=>$ticket_data['client_id'],'sale_id'=>$id,'renew_price_id'=>$ticket_data['renew_price_id'],'event_ticket_id'=>$ticket_data['event_ticket_id'],'event_id'=>$date_ticket_data['event_id'],'event_date_id'=>$date_ticket_data['event_date_id'],'name'=>$client_data['name'],'ticket_type_id'=>$date_ticket_data['ticket_type_id']]);

					$this->db->query("UPDATE sale_line SET object='ticket', object_id='".$result['id']."', stat_update='".time()."' WHERE id='".$data_row['id']."'");
					$class_book->event_ticket_temp_delete($data_row['object_id']);
				}
				if($prod_meta['supplier_id']['value']>0&&$prod_meta['supplier_alert']['value']>0) {
					$supplier_notify[$prod_meta['supplier_id']['value']][] = [$data_row['description'],$data_row['quantity']];
				}
				//-- end balance 0 statement --//
			}
			$this->db->query("UPDATE sale_line SET count_complete = count_complete+1 WHERE id = '".$data_row['id']."'");
		}
		//-- end loop

		//-- Post loop functions
		foreach($supplier_notify as $supplier_id=>$rows) {
			$supplier_data = $class_client->client_data(['id'=>$supplier_id,'field'=>['email','name_first','company','name_last']]);
			$supplier_email = filter_var($supplier_data['email'],FILTER_SANITIZE_EMAIL);
			if(filter_var($supplier_email,FILTER_VALIDATE_EMAIL)) {

				$line_table = NULL;
				foreach($rows as $row) {
					$line_table .= "<tr><td>".$row[0]."</td><td>".$row[1]."</td></tr>";
				}

				$mess = "Hello ".$supplier_data['name_first'].",<br><br>This is an automatic notification that your product with ".$class_setting->data['company']." has been sold to one of their customers / clients.<br><br><table><thead><tr><td>Product</td><td>Quantity</td></tr></thead>
			<tbody>".$line_table."</tbody>
			</table>";
				$zulu->mail_send($supplier_email,"Product Sold Alert",$mess,'',false,['client'=>true,'user_id'=>$client_data['user_id']]);
			}
		}

		return;
	}
	function sale_url($token,$fe=false) {
		return ($fe?MAIN_url."order":MAIN_url."sale")."/view/".$token."/";
	}
	function sale_line_count($id) {
		return count($this->sale_line($id));
	}
	function sale_line($id) {
		global $zulu;
		$line_data = $zulu->table_data($this->SQL_table_sale_line,0,array("where"=>array("sale_id = '$id'")));
		return $line_data;
	}
	function sale_line_sub($row,$config=[]) {
		$custom = unserialize($row['custom']);
		if(count($custom['input_label'])) {
			foreach($custom['input_label'] as $label) {
				$labels[] = $label;
			}
			$html .= "<b>Labels:</b> ".implode(" ",$labels);
		}
		if($config['plain']) {
			$html = strip_tags($html);
		}
		return $html;
	}
	function sale_line_delete($id) {
		$query = "DELETE FROM sale_line WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true);
		} else {
			return array("success"=>false);
		}
	}
	function sale_receipt_mail($id,$config=array()) {
		global $class_user,$class_client,$class_book,$class_room,$class_setting,$class_subscribe,$class_product,$zulu,$class_xero;

		$sale_data = $this->sale_data(array('id'=>$id));
		$sale_meta = $zulu->meta_array($this->sale_meta($id));
		$sale_line = $this->sale_line($id);

		$user_data = $class_client->client_data(array('id'=>$sale_data['client_id']));
		$sale_name = ($user_data['name']!=NULL?$class_client->client_name($user_data):$sale_data['name']);
		$extra = array();
		$alert_text = array();
		foreach($sale_line as $line) {
			$prod = $class_product->product_data(array('id'=>$line['product_id']));
			$prod_data = $prod;

			switch ($line['object']) {
			//switch ($prod_data['template']) {
				case 'book':
					$link_data = $class_book->reservation_data(array('id'=>$line['object_id']));
					$extra[] = array('title'=>$prod_data['name'].' Booking','info'=>'<i>Room:</i> '.$class_room->room_name($link_data['room_id']).' <i>Session Time:</i> '.date('d/m/Y h:ia',$link_data['time_start']));
					break;
				case 'voucher':
					$link_data = $this->coupon_data(array('sale_line_id'=>$line['id'],'first'=>true));
					$extra[] = array('title'=>'Coupon for "'.$line['description'].'"','info'=>'Your redemption code is <b>'.$link_data['code'].'</b>. '.($link_data['conf_expire']>0?' Expires <b>'.date('d/m/Y',$link_data['conf_expire']).'</b>.':NULL));
					$alert_text[] = "This order includes a voucher!";
					break;
				case 'trial':
					$link_data = $this->coupon_data(array('sale_line_id'=>$line['id'],'first'=>true));
					$extra[] = array('title'=>'Coupon for '.$line['description'],'info'=>'Your redemption code is <b>'.$link_data['code'].'</b>. '.($link_data['conf_expire']>0?' Expires <b>'.date('d/m/Y',$link_data['conf_expire']).'</b>.':NULL));
					break;
				case 'membership':
					//$link_data = $class_subscribe->user_subscription($sale_data['user_id']);
					break;
				case 'ticket':
					$ticket = $class_book->event_ticket_data(['id'=>$line['object_id']]);
					$ticket_info = $class_book->ticket_merge_filter($class_book->event_date_ticket_data(['id'=>$ticket['event_ticket_id']]));
					$url = $class_book->ticket_url($ticket['token'],true);

					$extra[] = ['title'=>"Your Ticket: ".$ticket_info['name'],'info'=>"Your order has tickets enclosed, you can view /print them via the link below:<br><a href=\"".$url."\">".$url."</a>"];
					break;
				case 'ticket_temp':
					if(!$temp_loop) {
						$temp_loop = true;
						$extra[] = ['title'=>"Tickets Pending",'info'=>"You have tickets in this order, they will be issued once payment is completed. <b>Please note, tickets are not held prior to payment, to reserve your ticket you should pay as soon as possible.</b>"];
					}
					break;
			}
			$sale_line_sub = $this->sale_line_sub($line);
			if(trim($sale_line_sub)!=NULL) {
				$sale_line_sub = "<br><small class=\"opt opt-grey\">".$sale_line_sub."</small>";
			}

			$line_table .= "<tr><td>".stripslashes($line['description']).$sale_line_sub."</td><td align='center'>".number_format($line['quantity'],0)."</td><td align='center'>$".$line['price']."</td><td align='right'>$".$line['total']."</td></tr>";
		}

		if($config['email']!=NULL) {
			$user_data['email'] = $config['email'];
		}
		if($config['name']!=NULL) {
			$sale_name = $config['name'];
		}
		if(count($extra)>0) {
			$extra_html = "<hr><h3>Attached Items</h3>";
			foreach($extra as $ehr) {
				$extra_html .= "<p><b>".$ehr['title']."</b><br>".$ehr['info']."</p>";
			}
		}
		$invoice_url = $this->sale_url($sale_data['token'],true);
		if($sale_meta['xero_link']!=NULL&&$config['xero']) {
			//$xero_invoice = $class_xero->get_invoice($sale_meta['xero_link']);
			$xero_link = $class_xero->get_invoice_public_url($sale_meta['xero_link']);
			if($xero_link['success']) {
				$invoice_url = $xero_link['invoice_url'];
			}
		}
		if($sale_data['coupon_id'] > 0) {
			$coupon_data = $this->coupon_data(['id'=>$sale_data['coupon_id']]);
			$coupon_code = $coupon_data['code'];
		}
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
		$bill = stripslashes($zulu->compile("<br>",$bill_arr));
		$ship = stripslashes($zulu->compile("<br>",$ship_arr));
		$sale_discount = $this->sale_total_discount($sale_data['id']);
		if($config['admin']) { // send to admin
			$mess = "<p>Hello <b>Admin</b>,<br><br>
			Here is a summary of the order.</p>

			<p>
				<table class=\"nice tbl-label-val\">
					<tr>
						<td>Order Date</td><td>".$zulu->date($sale_data['stat_add'],'d/m/Y h:ia')."</td>
					</tr>
					<tr>
						<td>Reference</td><td>{$sale_data['reference']}</td>
					</tr>
					<tr>
						<td>Customer</td><td>{$sale_name}</td>
					</tr>
					".($sale_data['pay_method']!=NULL?"
					<tr>
						<td>Payment Method</td><td>".stripslashes($sale_data['pay_method'])."</td>
					</tr>
					":NULL)."
					".($sale_meta['ship_price']>0?"
					<tr>
						<td>Shipping Price</td><td>$".$zulu->dollar($sale_meta['ship_price'],true)." ".($sale_meta['ship_method']!=NULL?'('.stripslashes($sale_meta['ship_method']).')':NULL)."</td>
					</tr>
					":NULL)."
					".(trim($sale_meta['ship_price'])==NULL||$sale_meta['ship_price']<=0&&$sale_meta['ship_method']!=NULL?"
					<tr>
						<td>Shipping</td><td>".stripslashes($sale_meta['ship_method'])."</td>
					</tr>
					":NULL)."
					".($sale_data['email']!=NULL?"
					<tr>
						<td>Email Address</td><td>".$sale_data['email']."</td>
					</tr>
					":NULL)."
					".($sale_meta['phone']!=NULL?"
					<tr>
						<td>Phone Number</td><td>".$sale_meta['phone']."</td>
					</tr>
					":NULL)."
					".($coupon_code!=NULL?"
					<tr>
						<td>Promotion Code</td><td>".$coupon_code."</td>
					</tr>
					":NULL)."
				</table>
			</p>
			<hr />

			".($sale_meta['web_order']?"<div class='coltable col2'><div class='col'><p><b>Billing Details</b><br>".$bill."</p></div>".($ship!=NULL?"<div class='col'><p><b>Shipping Details</b><br>".$ship."</p></div>":NULL)."</div><hr>":NULL)."
			".($sale_meta['ship_notes']!=NULL?"<p><b>Shipping Notes</b><br>".stripslashes($sale_meta['ship_notes'])."</p><hr>":NULL)."
			<h3>Order Contents</h3>
			<table class=\"nice tbl-order\"><thead><tr><td>Product</td><td align='center'>Quantity</td><td align='center'>Price</td><td align='right'>Subtotal</td></tr></thead>
			<tbody>".$line_table."</tbody>
			</table>
			<hr>
			<p>
			".($sale_discount>0?"<b>Discount</b> $".number_format($sale_discount,2)."<br>":NULL)."
			<b>Total Price:</b> $".number_format($this->sale_total($sale_data['id']),2)."<br>
			<b>Total Paid:</b> $".number_format($this->sale_total_paid($sale_data['id']),2)."<br>
			<b>Total Remaining:</b> $".number_format($this->sale_balance($sale_data['id']),2)."</p>

			<p><b>View Sale Receipt / Invoice:</b> <a href=\"".$invoice_url."\">".$invoice_url."</a></p>

			<p class=\"bold red\">".implode("<br>",$alert_text)."</p>

			{$extra_html}
			<hr>

			<p>Please contact the customer to complete any required actions.</p>";
			$to = $class_setting->data['contact_email'];

		} else {
			$mess = "<p>Hello <b>".$sale_name."</b>,<br><br>
			Here is a summary of your order.</p>
			".($config['message']!=NULL?"<p>".$config['message']."</p>":NULL)."<hr>

			<p>
				<table class=\"nice tbl-label-val\">
					<tr>
						<td>Order Date</td><td>".$zulu->date($sale_data['stat_add'],'d/m/Y h:ia')."</td>
					</tr>
					<tr>
						<td>Reference</td><td>{$sale_data['reference']}</td>
					</tr>
					".($sale_data['pay_method']!=NULL?"
					<tr>
						<td>Payment Method</td><td>".stripslashes($sale_data['pay_method'])."</td>
					</tr>
					":NULL)."
					".($sale_meta['ship_price']>0?"
					<tr>
						<td>Shipping Price</td><td>$".$zulu->dollar($sale_meta['ship_price'],true)." ".($sale_meta['ship_method']!=NULL?'('.stripslashes($sale_meta['ship_method']).')':NULL)."</td>
					</tr>
					":NULL)."
					".((trim($sale_meta['ship_price'])==NULL||$sale_meta['ship_price']<=0)&&$sale_meta['ship_method']!=NULL?"
					<tr>
						<td>Shipping</td><td>".stripslashes($sale_meta['ship_method'])."</td>
					</tr>
					":NULL)."
					".($coupon_code!=NULL?"
					<tr>
						<td>Promotion Code</td><td>".$coupon_code."</td>
					</tr>
					":NULL)."
				</table>
			</p>
			<hr />

			".($sale_meta['web_order']?"<div class='coltable col2'><div class='col'><p><b>Billing Details</b><br>".$bill."</p></div>".($ship!=NULL?"<div class='col'><p><b>Shipping Details</b><br>".$ship."</p></div>":NULL)."</div><hr>":NULL)."
			".($sale_meta['ship_notes']!=NULL?"<p><b>Shipping Notes</b><br>".stripslashes($sale_meta['ship_notes'])."</p><hr>":NULL)."

			<h3>Your Order</h3>
			<table class=\"nice tbl-order\"><thead><tr><td>Product</td><td align='center'>Quantity</td><td align='center'>Price</td><td align='right'>Subtotal</td></tr></thead>
			<tbody>".$line_table."</tbody>
			</table>
			<hr>
			<p>
			".($sale_discount>0?"<b>Discount</b> $".number_format($sale_discount,2)."<br>":NULL)."
			<b>Total Price:</b> $".number_format($this->sale_total($sale_data['id']),2)."<br>
			<b>Total Paid:</b> $".number_format($this->sale_total_paid($sale_data['id']),2)."<br>
			<b>Total Remaining:</b> $".number_format($this->sale_balance($sale_data['id']),2)."</p>

			<p><b>View Sale Receipt / Invoice:</b> ".$invoice_url."</p>

			<p class=\"bold red\">".implode("<br>",$alert_text)."</p>

			{$extra_html}
			<hr>

			<h3>Have questions?</h3>
			<p>You can contact us on ".$class_setting->data['contact_email'].($class_setting->data['contact_phone']!=NULL?" or ".$class_setting->data['contact_phone']:NULL)."</p>

			<hr>

			<p>Thank you for your order.</p>";
			$toggle = 1;
			$to = ($sale_data['email']!=NULL?$sale_data['email']:$user_data['email']);
		}
		if($config['email']!=NULL) {
			$to = $config['email'];
		}

		if(filter_var($to,FILTER_VALIDATE_EMAIL)) {
			$zulu->mail_send($to,"Order #".$sale_data['reference']." Summary",$mess,'',false,array('client'=>true,'user_id'=>$sale_data['user_id'],'object'=>'sale','object_id'=>$sale_data['id'],'toggle'=>$toggle));
			return true;
		} else {
			return false;
		}
	}
	function payment_delete($id) {
		$query = "DELETE FROM sale_payment WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true);
		} else {
			return array("success"=>false);
		}
	}
	function sale_line_edit($id,$config) {
		global $class_user,$zulu,$class_product;

		//Check Existing
		if($id>0) {
			$new = false;
		} else {
			$new = true;
		}

		//Empty
		if(trim($config['description'])==NULL&&trim($config['sku'])==NULL&&$new) {
			return array("success"=>false);
		} else {
			//Convert Data
			foreach($config as $key=>$val) {
				if($key=='line_id') {
					continue;
				}
				if($key=='attr') {
					continue;
				}
				$data[$key] = $val;
				$fields[$key] = $key;
			}

			if(!isset($config['discount'])) {
				$config['discount'] = 0;
			}

			//Extra Data
			$fields['stat_update'] = 'stat_update';
			$data['stat_update'] = time();

			//Total
			if(isset($config['price'])) {
				$line_sub = ($config['price']*$config['quantity']);
				$line_total = $line_sub-$config['discount'];
				$line_total += $config['extra'];
				$fields[] = 'total';
				$data['total'] = $line_total;
			}

			//Unset vars if class is not sale
			if(get_called_class()=='porder') {
				unset($data['discount'],$fields['discount']);
			}

			//Do Query
			if($new) {
				if($config['product_id']>0) {
					$find_product = true;
					$p_filter['id'] = $config['product_id'];
				} else {
					if($config['sku']!=NULL) {
						$find_product = true;
						$p_filter['sku'] = $config['sku'];
					} else {
						//-- nothing
					}
				}
				if($find_product) {
					$prod_data = $class_product->product_data($p_filter);
					$prod_data[0] = $prod_data;
				}
				if($config['object_id']==NULL) {
					$data['object'] = 'product';
					$data['object_id'] = $prod_data['id'];
					$fields[] = 'object';
					$fields[] = 'object_id';
				}
				if(($config['product_id']<=0||!isset($config['product_id']))&&$prod_data['id']>0) {
					//PRODUCT_ID
					$data['product_id'] = $prod_data['id'];
					$fields[] = 'product_id';
				}
				if(($config['sku']==''||(!isset($config['sku']))&&$prod_data['id']>0)) {
					//SKU
					$data['sku'] = $prod_data['sku'];
					if(!in_array('sku',$fields)) {
						$fields[] = 'sku';
					}
				}
				$fields[] = 'token';
				$data['token'] = $zulu->serial(10);
				$fields[] = 'stat_add';
				$data['stat_add'] = time();

				foreach($fields as $f) {
					$nfields[$f] = $f;
				}
				$fields = $nfields;

				$query = "INSERT INTO ".$this->SQL_table_sale_line." ".$this->db->build(2,$fields,$data);
			} else {
				$query = "UPDATE ".$this->SQL_table_sale_line." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
			}

			if($this->db->query($query)) {
				if($id <= 0) $id = $this->db->insert_id;
				return array("success"=>true,'id'=>$id);
			} else {
				return array("success"=>false);
			}
		}
	}
	function sale_status_info($status,$config=array()) {
		global $zulu;

		if($config['id']>0) {
			$sale_data = $this->sale_data(['id'=>$config['id'],'field'=>['status','id']]);
			$status = $sale_data['status'];
			$complete_meta = $zulu->meta_value('sale',$config['id'],'complete');
			$complete_value = $complete_meta['value'];
		}
		switch($status) {
			case '0':
				$data['class'] = 'opt opt-grey';
				$data['icon'] = 'fa-pause';
				$data['label'] = 'Draft';
				$data['tag'] = 'parked';
				break;
			case '1':
				$data['class'] = 'opt opt-success';
				$data['icon'] = 'fa-check';
				$data['label'] = 'Approved';
				if($sale_data['id']>0) {
					if($this->sale_balance($sale_data['id'])>0) {
						$data['label'] = 'Pending';
						$data['class'] = 'opt opt-warning';
						$data['tag'] = 'pending';
						$data['long'] = 'Pending ($'.$this->sale_balance($sale_data['id']).' due)';
					} elseif($complete_value==1) {
						$data['label'] = 'Completed';
						$data['tag'] = 'paid';
						$data['long'] = 'Complete & Paid ($'.$this->sale_total($sale_data['id']).')';
					} else {
						$data['label'] = 'Paid';
						$data['tag'] = 'paid';
						$data['long'] = 'Paid ($'.$this->sale_total($sale_data['id']).')';
					}
				}
				break;
			case '2':
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Voided';
				$data['tag'] = 'void';
				break;
		}
		return $data;
	}
	function sale_delete($id) {
		global $class_user,$class_xero;

		$query = "UPDATE sale SET status = 2, stat_update = '".time()."' WHERE id = '{$id}'";

		if($class_user->has_perm_redir('sale_delete')) {
			if($this->db->query($query)) {
				$class_xero->invoice_void([$id]);
				return array("success"=>true);
			} else {
				return array("success"=>false);
			}
		} else {
			return array("success"=>false);
		}
	}
	function sale_edit($id,$salepost=array(),$config=array()) {
		global $class_user,$zulu,$class_rule;

		if($id<1) {
			$data = $this->sale_new(array('user_id'=>$salepost['user_id']));
			$id = $data['id'];
		}

		unset($salepost['reference']);
		foreach($salepost as $key=>$val) {
			if(!in_array($key,array('line')) && !in_array($key,array('meta'))) {
				$data[$key] = $val;
				$fields[] = $key;
			}
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$data['date_due'] = $zulu->dateEncode($data['date_due']);
		$data['date'] = $zulu->dateEncode($data['date']);

		$query = "UPDATE ".$this->SQL_table_sale." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id = '".($salepost['user_id']>0?$salepost['user_id']:$class_user->authorised->id)."'";
		if($this->db->query($query)) {
			if(count($salepost['line'])>0) {
				foreach($salepost['line'] as $line_data_raw) {
					$line_data_raw['sale_id'] = $id;
					$res = $this->sale_line_edit($line_data_raw['line_id'],$line_data_raw);
					$custom = unserialize($line_data_raw['custom']);
					if($custom['sale_cart_id'] > 0 && $res['id'] > 0) {
						$this->cart_edit($custom['sale_cart_id'],['sale_line_id'=>$res['id']]);
					}
					if($line_data_raw['line_id']>0) {
						$complete_arr[] = $line_data_raw['line_id'];
					}
				}
			}
			if(count($salepost['meta'])>0) {
				foreach($salepost['meta'] as $key=>$val) {
					$zulu->meta_update('sale',$id,$key,$val);
				}
			}
			$data = $this->sale_data(array('id'=>$id));
			if($config['complete']&&$data['user_id']>0&&$this->sale_line_count($id)>0) {
				$this->complete($id,'',array('token_bill'=>($config['token_bill']?true:false),'user_id'=>($salepost['user_id']>0?$salepost['user_id']:0)));
				$data['status'] = 1;
			}

			//--Update Meta
			$this->sale_gen_info($id);

			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function sale_meta($id,$field=NULL) {
		return zulu::meta_value($this->SQL_table_sale,$id,$field);
	}
	function is_complete($id) {
		$data = $this->sale_data(array('id'=>$id));
		return ($data['status']==1&&$data['user_id']>0&&$this->sale_line_count($id)>0?true:false);
	}
	function is_locked($id) {
		return ($this->payment_has($id)?true:false);
	}
	function complete($id,$pay_method="",$config=array()) {
		global $class_user,$class_xero,$class_setting,$class_rule,$zulu;

		//-- Load sale data
		$sale_data = $this->sale_data(array('id'=>$id));
		$pay_method = ($pay_method!=""?$pay_method:$sale_data['pay_method']);
		$this->db->query("UPDATE ".$this->SQL_table_sale." SET status = '1', pay_method='".($pay_method!=NULL?$pay_method:'')."' WHERE id = '".$id."' AND user_id = '".($config['user_id']>0?$config['user_id']:$class_user->authorised->id)."'");
		$ref = $this->reference_check($id,$config['user_id']);

		//-- Rule: Sale New Create
		$snrg = $zulu->meta_value('sale',$id,'sale_new_rule_generate');
		if($snrg['value']<=0 && $sale_data['client_id']>0){
			$class_rule->generate_rule_from_auto('sale_new', $sale_data['client_id']);
			$zulu->meta_update('sale',$id,'sale_new_rule_generate', '1');
		}

		//-- Rule: Sale Abandoned Remove
		$sarg = $zulu->meta_value('sale',$id,'sale_abandoned_rule_generate');
		if($sarg['value']>0 && $sale_data['client_id']>0){
			$sarg_ids = explode(',',$sarg['value']);
			foreach($sarg_ids as $s_id) {
				$class_rule->delete($s_id);
			}
			$zulu->meta_remove('sale',$id,'sale_abandoned_rule_generate');
		}

		//-- Loop through sale functions
		$this->sale_line_function($id,array('token_bill'=>($config['token_bill']?true:false),'generate'=>($config['generate']?true:false)));

		//-- Export sale
		if($class_setting->data['xero_sale_post']>0) {
			if($class_setting->data['xero_sale_post_approved']>0) {
				$extra_config = ['Status'=>'AUTHORISED'];
			}
			$xml_invoices = $class_xero->invoice_build([$id],$extra_config);
			$xml = $xml_invoices['xml'];
			$invoice_row = $xml_invoices['count'];

			 if(count($invoice_row)>0) {
				$response = $class_xero->invoice_run($xml,$xml_invoices['sale_id_array']);
				$xero_response = ['success'=>true,'message'=>$response['message']."<br><br>LOG:<BR>".implode("<br>",$xml_invoices['log']),($response['success']?1:'0')];
			} else {
				 $xero_response = ['success'=>false];
			}
		}

		return $ref;
	}
	function sale_generate($data=array()) {
		$result = $this->sale_edit(0,$data);
		$id = $result['id'];
		$sale_data = $this->sale_data(array('id'=>$id));
		return array('id'=>$id,'token'=>$sale_data['token']);
	}
	function delete($id,$identifier='id') {
		return $this->product_delete($id,$identifier);
	}
	function reference_check($id,$user_id=0) {
		global $class_user;
		$data = $this->sale_data(array('id'=>$id));
		if($data['reference']<=0&&$data['status']==1) {
			$ref = $this->reference_gen($user_id);
			$this->db->query("UPDATE ".$this->SQL_table_sale." SET reference = '".$ref."' WHERE id = '".$id."' AND user_id = '".($user_id>0?$user_id:$class_user->authorised->id)."'");
			return $ref;
		}
		return true;
	}
	function reference_gen($user_id=0) {
		global $class_user;
		$data = zulu::table_data($this->SQL_table_sale,0,array("where"=>array("reference > 0","user_id='".($user_id>0?$user_id:$class_user->authorised->id)."'"),"sort"=>"reference DESC","limit"=>1));
		if(count($data)>0) {
			$data = $data[0];
			return $data['reference']+1;
		} else {
			return '1';
		}
	}
	function dps_find_billing_id($id=0) {
		$data = zulu::table_data("sale_payment_paystation",0,array("first"=>true,"where"=>array("sale_id = '$id'")));
		return $data['bill_id'];
	}
	function dps_payment_edit($id=0,$config=array()) {
		foreach($config as $key=>$val) {
			$fields[] = $key;
			$data[$key] = $val;
		}
		if($id<=0) {
			$fields[] = 'stat_add';
			$data['stat_add'] = time();
			$fields[] = 'stat_ip';
			$data['stat_ip'] = $_SERVER['REMOTE_ADDR'];
			$query = "INSERT INTO sale_payment_dps ".$this->db->build(2,$fields,$data);
		} else {
			$query = "UPDATE sale_payment_dps SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		}
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>($id>0?$id:$this->db->insert_id));
		} else {
			return array("success"=>false);
		}
	}
	//Coupon Functions
	function coupon_data($config=array()) {
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['first']==true) {
			$sql_config['first'] = true;
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['code']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "code = '".$config['code']."'";
		}
		if($config['sale_line_id']!=NULL) {
			$sql_config['where'][] = "sale_line_id = '".$config['sale_line_id']."'";
		}
		if($config['discount_object']!=NULL) {
			$sql_config['where'][] = "discount_object = '".$config['discount_object']."'";
		}
		if($config['discount_type']!=NULL) {
			$sql_config['where'][] = "discount_type = '".$config['discount_type']."'";
		}
		if($config['user_id']!=NULL) {
			$sql_config['where'][] = "conf_member_id = '".$config['user_id']."' || (
									conf_member LIKE '".$config['user_id'].",%' ||
									conf_member LIKE '%,".$config['user_id'].",%' ||
									conf_member LIKE '%,".$config['user_id']."' ||
									conf_member LIKE '".$config['user_id']."')";
		}
		$sql_config['sort'] = 'stat_add DESC';
		return zulu::table_data('sale_coupon',$id,$sql_config);
	}
	function coupon_redemption($id,$mid=0) {
		//$meta_data = zulu::table_data("sale",0,array("where"=>array("coupon_id = '{$id}'")));
		//return count($meta_data);
		return $this->coupon_used_count($id,$mid);
	}
	function coupon_status_info($id) {

		$coupon_data = $this->coupon_data(array('id'=>$id));
		$redemptions = $this->coupon_redemption($id);
		$status = $coupon_data['status'];

		if($status==1) {

			$data['class'] = 'opt opt-success';
			$data['icon'] = 'fa-check';
			$data['label'] = 'Active';

			if($coupon_data['conf_expire']>0&&$coupon_data['conf_expire']<time()) {
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-clock';
				$data['label'] = 'Expired';
			}
			if($coupon_data['conf_start']>0&&$coupon_data['conf_start']>time()) {
				$data['class'] = 'opt opt-grey';
				$data['icon'] = 'fa-pause';
				$data['label'] = 'Pending';
			}
			if(($redemptions>=$coupon_data['conf_max']&&$coupon_data['conf_max']>0) || ($coupon_data['type'] == 'voucher' && $coupon_data['discount_remain'] <= 0)) {
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Total Limit';
			}

		} else {
			switch($status) {
				case '0':
				$data['class'] = 'opt opt-grey';
				$data['icon'] = 'fa-pause';
				$data['label'] = 'Draft';
				break;
				case '2':
				$data['class'] = 'opt opt-danger';
				$data['icon'] = 'fa-times';
				$data['label'] = 'Deleted';
				break;
			}
		}

		return $data;
	}
	function coupon_delete($id) {
		$query = "UPDATE sale_coupon SET status = '2', stat_update = '".time()."' WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true);
		} else {
			return array("success"=>false);
		}
	}
	function coupon_new($config=array()) {
		global $class_user;

		$data['token'] = zulu::serial();
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['admin_id'] = $class_user->authorised->id;

		$query = "INSERT INTO sale_coupon ".$this->db->build(2,array('token','stat_add','stat_update'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function coupon_exists($field='id',$id,$maxfalse=1) {
		$sql_config['where'][] = "{$field} = '{$id}'";
		$data = zulu::table_data('sale_coupon',0,$sql_config);
		return (count($data)>=$maxfalse?true:false);
	}
	function coupon_edit($id,$config=array()) {
		global $class_user,$zulu;
		if($id<1) {
            if($config['code'] == NULL) {
                $config['code'] = $this->coupon_gen($config['code_prefix']);
            }
			$data = $this->coupon_new();
			$id = $data['id'];
            unset($config['code_prefix']);
		}
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		if($class_user->authorised->role=='admin') {
			$data['admin_id'] = $class_user->authorised->id;
			$fields[] = 'admin_id';
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$query = "UPDATE ".$this->SQL_table_sale_coupon." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id,'code'=>$config['code']);
		} else {
			return array("success"=>false);
		}
	}
	function coupon_product_count_allowed($id,$pid,$mid,$sale_apply=false) {
		global $class_product;

		$coupon_data = $this->coupon_data(array('id'=>$id));
		$prod_data = $class_product->product_data(array('id'=>$pid));
		//

		if($coupon_data['conf_max_sale']>0&&$sale_apply) {
			$return = $coupon_data['conf_max_sale'];
		} else {
			if($coupon_data['conf_max']>0 && $coupon_data['conf_max_member']==0) {
				$return = ($coupon_data['discount_remain']>0?$coupon_data['discount_remain']:0);
			} else if($coupon_data['conf_max_member']>0) {
				$coupon_count = $this->coupon_used_count($id,$mid);
				if($coupon_data['conf_max_member']>$coupon_count) {
					$left = $coupon_data['conf_max_member'] - $coupon_count;
					if($coupon_data['conf_max']>0) {
						/*if($coupon_data['discount_remain']<$left) {
							$return = $coupon_data['discount_remain'];
						}
						else $return = $left;	*/
						$return = $left;
					}
					else $return = $left;
				} else $return = 0;
			} else {
				$return = -1;
			}
		}
		return $return;
	}

	function coupon_used_count($coupon_id,$mid=0) {
		$sale_data = ($mid>0?$this->sale_data(array('client_id'=>$mid,'coupon_id'=>$coupon_id)):$this->sale_data(array('coupon_id'=>$coupon_id)));

		$coupon_count = 0;
		foreach($sale_data as $sale) {

			$sale_meta = $this->sale_meta($sale['id']);
			if($sale['coupon_id'] == $coupon_id) $coupon_count += $sale_meta['coupon_used']['value'];
		}
		return $coupon_count;
	}

	function coupon_check($code="",$id=0,$mid,$config=array()) {
		global $class_product;

		$data = array();
		if($id > 0) {
			$data = $this->coupon_data(array('id'=>$id));
		} else {
			$data = $this->coupon_data(array('code'=>$code));
			$id = $data['id'];
		}

		if($data['id'] > 0) { //has data
			$date_start = mktime(0,0,0,date('m',$data['conf_start']),date('d',$data['conf_start']),date('Y',$data['conf_start']));
			$date_end = mktime(23,59,59,date('m',$data['conf_expire']),date('d',$data['conf_expire']),date('Y',$data['conf_expire']));
			if($data['status']==1 && $date_start<=time() && $date_end>time()) { //within time
				$return = array('success'=>true,'id'=>$id,'err'=>"This coupon is vaild and will be applied to your order.",'err_class'=>'1');
				$mids = explode(',',$data['conf_member']);
				$coupon_count = $this->coupon_used_count($id); //$this->coupon_used_count($id,$mid); was this but removed because not right?
				$coupon_count_member = $this->coupon_used_count($id,$mid);

				if((count($mids)>0 && in_array($mid,$mids)) || ($data['conf_member_id']>0 && $data['conf_member_id']==$mid) || (($data['conf_member']==NULL||$data['conf_member']==0) && $data['conf_member_id']==0)) { } else {
					$return = array('success'=>false,'err'=>"You are not authorised to use this coupon.",'err_class'=>'2');
				}

				if(($data['conf_max']>0 && $data['conf_max']>$coupon_count && ($data['conf_max_member']==0 || $data['conf_max_member']>$coupon_count_member)) ||
				($data['conf_max']>0 && $data['conf_max_member']>$coupon_count_member && $data['conf_max']>$coupon_count) ||
				($data['conf_max_member']>0 && $data['conf_max_member']>$coupon_count_member && $data['conf_max']==0) ||
				($data['conf_max']==0 && $data['conf_max_member']==0) || ($data['conf_max']==0&&$data['conf_max_member']>0&&$data['conf_max_member']>$coupon_count_member)) { } else {
					$return = array('success'=>false,'err'=>"This coupon has already been redeemed it's max number of times.",'err_class'=>'2');
				}

				if($data['discount_object_id']>0) {

					if($config['sale_id']>0 || count($config['object_check'])>0) {
						$product_check_id = (count($config['object_check'])>0?$config['object_check']:$this->sale_line_data(['id'=>$config['sale_id'], 'dataset'=>'product_id.quantity']));
					}

					if(count($product_check_id)>0) {
						$discount_object_pass = false;

						foreach($product_check_id as $pid=>$check_data) {
							$p_check_arr = [$pid];
							if($class_product->has_children($pid)) {
								foreach($class_product->vars->children_array as $child) {
									$p_check_arr[] = $child['id'];
								}
							} else {
								$pdata = $class_product->product_data(array('id'=>$pid));
								if($pdata['type_variant'] == '2') {
									$p_check_arr[] = $pdata['parent_id'];
								}
							}
							if(in_array($data['discount_object_id'],$p_check_arr) && $data['discount_object']==$check_data['object']) {
								$discount_object_pass = true;
								$discount_object_count += $check_data['quan'];
							}
							unset($p_check_arr,$pdata);
						}

						if($data['discount_object_min']>0&&$discount_object_count<$data['discount_object_min']) {
							if($data['discount_object'] == 'event_ticket') {
								$pdata = $class_book->event_ticket_type_data(array('id'=>$data['discount_object_id']));
							} else {
								$pdata = $class_product->product_data(array('id'=>$data['discount_object_id']));
							}
							$return = array('success'=>false,'err'=>"You must purchase at least ".$data['discount_object_min']."x ".stripslashes($pdata['name'])."'s to get this discount.",'err_class'=>'2');
						}
						if(!$discount_object_pass) {
							$return = array('success'=>false,'err'=>"Your order is not eligible for this coupon.",'err_class'=>'2');
						}
					} else {
						$return = array('success'=>false,'err'=>"Your order is not eligible for this coupon.",'err_class'=>'2');
					}
				} elseif($data['type'] == 'voucher' && $data['discount_remain'] <= 0) {
                    $return = array('success'=>false,'err'=>"This voucher has been fully used up.",'err_class'=>'2');
                }
				//--
			} else {
				$return = array('success'=>false,'err'=>"The coupon you entered is inactive.",'err_class'=>'2');
			}
		} else { //no data
			$return = array('success'=>false,'err'=>"The coupon you entered is invalid.",'err_class'=>'2');
		}
		return $return;
	}

	function coupon_price($id,$pid,$quantity,$price,$config=[]) {
		global $class_product, $class_user;

		$allowed = 0;
		$coupon_data = $this->coupon_data(array('id'=>$id));
		$discount_price = 0;
		if(count($coupon_data) > 0) {
			$coupon_product = $class_product->product_data(array('id'=>$coupon_data['discount_object_id']));
			$p_check_arr = [$pid];
			if($class_product->has_children($pid)) {
				foreach($class_product->vars->children_array as $child) {
					$p_check_arr[] = $child['id'];
				}
			} else {
				$check_product = $class_product->product_data(array('id'=>$pid));
				if($check_product['type_variant'] == '2') {
					$p_check_arr[] = $check_product['parent_id'];
				}
			}

			if(in_array($coupon_data['discount_object_id'],$p_check_arr) || ($coupon_product['type']=='category' && $class_product->is_parent($pid,$coupon_data['discount_object_id']))) {
				switch ($coupon_data['discount_type']) {
					case 'percent':
						$discount_price = $price*($coupon_data['discount_amount']/100);
						break;
					case 'fixed':
                        if($coupon_data['type'] == 'voucher') {
                            $discount_price = $coupon_data['discount_remain'];
                        } else {
                            $discount_price = $coupon_data['discount_amount'];
                        }
						break;
					case 'credit':
						$discount_price = $price;
						break;
				}

				$allowed = $this->coupon_product_count_allowed($coupon_data['id'],$pid,($config['client_id']>0?$config['client_id']:$class_user->authorised->id));
				$allowed = ($allowed<0?$quantity:$allowed);
				$discount_quantity = ($allowed<$quantity?$allowed:$quantity);
				$discount_total = $discount_price*$discount_quantity;
				return array('total'=>$discount_total,'quantity'=>$discount_quantity,'discount'=>$discount_price);
			}
		}
		return false;
	}

	function coupon_apply($sale_id, $coupon_id) {
		global $zulu, $class_product, $class_book;

		$sale_data = $this->sale_data(['id'=>$sale_id]);
		$sale_lines = $this->sale_line($sale_id);
		$coupon_data = $this->coupon_data(['id'=>$coupon_id]);
		foreach($sale_lines as $sale_line) {
			$custom = unserialize($sale_line['custom']);
			if($sale_line['product_id']>0) {
				$product_data = $class_product->product_data(['id'=>$sale_line['product_id']]);
				$product_meta = $zulu->meta_array($class_product->product_meta($product_data['id']));
			}
			$allowed = $this->coupon_product_count_allowed($coupon_data['id'],$sale_line['product_id'],$sale_data['client_id'],true);
			if($product_meta['object_id'] > 0) {
				for($i=1; $i<=$sale_line['quantity']; $i++) {
					if(isset($custom['renew_id'])) {
						$custom_row = ['renew_id'=>array_shift($custom['renew_id'])];
						unset($custom['renew_id']);
					}
					if(is_array($custom) && is_array($custom_row)) {
						$custom_row = array_merge($custom_row,$custom);
					}

					$p_check_arr = [$sale_line['product_id']];
					if($class_product->has_children($sale_line['product_id'])) {
						foreach($class_product->vars->children_array as $child) {
							$p_check_arr[] = $child['id'];
						}
					} elseif($product_data['type_variant'] == '2') {
						$p_check_arr[] = $product_data['parent_id'];
					}

					if(in_array($coupon_data['discount_object_id'],$p_check_arr) && ($i<=$allowed || $allowed==-1)) {
						$coupon_price = $this->coupon_price($coupon_data['id'],$sale_line['product_id'],1,$sale_line['price'],['client_id'=>$sale_data['client_id']]);
						$total_quantity += 1;
					}

					$sale_update['line'][] = array('line_id'=>$sale_line['id'],'product_id'=>$sale_line['product_id'],'sku'=>$product_data['sku'],'description'=>$sale_line['description'],'quantity'=>'1','price'=>$sale_line['price'],'discount'=>$coupon_price['total'],'custom'=>(is_array($custom_row)?serialize($custom_row):serialize($custom)));
					unset($custom_row,$coupon_price);
				}
			} else if($sale_line['product_id'] != $class_product->ticket_id) {
				$coupon_price = $this->coupon_price($coupon_data['id'],$sale_line['product_id'],$sale_line['quantity'],$sale_line['price'],['client_id'=>$sale_data['client_id']]);
				$total_quantity += $coupon_price['quantity'];
				$discount = $coupon_price['total'];
				$custom_row = serialize($custom);
				$sale_update['line'][] = array('line_id'=>$sale_line['id'],'quantity'=>$sale_line['quantity'],'price'=>$sale_line['price'],'discount'=>$discount,'custom'=>$custom_row);
			} else {
				$temp_tickets = $custom;
				$i = 1;
				foreach($temp_tickets['ticket_temp'] as $temp_ticket) {
					$temp_row = $class_book->event_ticket_temp_data(['id'=>$temp_ticket]);
					$ticket_row = $class_book->event_date_ticket_data(['id'=>$temp_row['event_ticket_id']]);

					if($coupon_data['discount_object']=='event_ticket' && $coupon_data['discount_object_id']==$ticket_row['ticket_type_id'] && ($i<=$allowed || $allowed==-1)) {
						$coupon_price = $this->coupon_price($coupon_data['id'],$ticket_row['ticket_type_id'],1,$sale_line['price'],['client_id'=>$sale_data['client_id']]);
						$total_quantity += 1;
						$i++;
					}
					unset($temp_tickets['ticket_temp']);
					$sale_update['line'][] = array('product_id'=>$sale_line['product_id'],'sku'=>$product_data['sku'],'description'=>$sale_line['description'],'quantity'=>'1','price'=>$sale_line['price'],'discount'=>$coupon_price['total'],'object'=>'ticket_temp','object_id'=>$temp_ticket,'custom'=>serialize($temp_tickets));
				}
			}
			unset($product_data,$product_meta,$discount,$coupon_price,$p_check_arr);
		}
		$sale_update['coupon_id'] = $coupon_data['id'];
		$sale_update['meta'] = ['coupon'=>$coupon_data['id'],'coupon_used'=>($total_quantity>0?$total_quantity:1)];

        if($coupon_data['discount_type'] == 'fixed' && $coupon_data['discount_object_id'] <= 0) {
            $discount_price = 0;
            if($coupon_data['type'] == 'voucher') {
                $discount_price = $coupon_data['discount_remain'];
            } else {
                $discount_price = $coupon_data['discount_amount'];
            }
            $sale_total = $this->sale_total($sale_id);
            if($discount_price > $sale_total) {
                $discount_price = $sale_total;
            }
            $sale_update['meta']['coupon_amount'] = $zulu->dollar($discount_price);
            if($coupon_data['type'] == 'voucher') {
                $this->coupon_edit($coupon_data['id'], ['discount_remain'=>($coupon_data['discount_remain']-$discount_price)]);
            }
        }

		$this->sale_edit($sale_id,$sale_update);
	}

	function remove_coupon($id) {
        global $zulu;

        $sale_row = $this->sale_data(['id'=>$id]);
        $sale_meta = $zulu->meta_array($this->sale_meta($id));
        $coupon_row = $this->coupon_data(['id'=>$sale_row['coupon_id']]);
		$this->sale_edit($id,array('coupon_id'=>0));
		$lines = $this->sale_line($id);
		foreach($lines as $line) {
			if($line['discount']>0) {
				$this->sale_line_edit($line['id'],array('discount'=>0,'quantity'=>$line['quantity'],'price'=>$line['price'],'extra'=>$line['extra']));
			}
		}
        $zulu->meta_update('sale',$id,'coupon','');
        $zulu->meta_update('sale',$id,'coupon_amount','');

        if($coupon_row['type'] == 'voucher') {
            $this->coupon_edit($coupon_row['id'],['discount_remain'=>($coupon_row['discount_remain']+$sale_meta['coupon_amount'])]);
        }
		return;
	}

	function coupon_gen($start='') {
		global $zulu;
		$code = $start.strtoupper($zulu->serial(8));
		$data = $this->coupon_data(array('code'=>$code));
		if($data['code']!=NULL) {
			return $this->coupon_gen($start);
		}
		return $code;
	}

	function has_product($id,$product_id) {
		$lines = $this->sale_line($id);
		foreach($lines as $line) {
			if($line['product_id']==$product_id) return true;
		}
		return false;
	}
	function has_object($id,$object) {
		$lines = $this->sale_line($id);
		foreach($lines as $line) {
			if($line['object']==$object) return true;
		}
		return false;
	}
	function is_booking($id) {
		$lines = $this->sale_line($id);
		foreach($lines as $line) {
			if($line['object']=='book') return true;
		}
		return false;
	}
	function has_membership($id) {
		global $class_product;
		$sale_line = $this->sale_line_data(array('id'=>$id));
		if($sale_line['object_id'] > 0) {
			$prod_data = $class_product->product_data(array('id'=>$sale_line['object_id']));
			if($prod_data['template']=='membership' || $prod_data['template']=='trial') {
				return true;
			}
		}
		return false;
	}

	function sale_has_membership($id) {
		global $class_product;
		$lines = $this->sale_line($id);
		foreach($lines as $line) {
			if($line['object_id'] > 0) {
				$prod_data = $class_product->product_data(array('id'=>$line['object_id']));
				if($prod_data['template']=='membership' || $prod_data['template']=='trial') {
					return true;
				}
			}
		}

		return false;
	}

	function cart_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
			unset($config['session']);
		}
		if($config['session']!=NULL) {
			$sql_config['where'][] = "session = '".$config['session']."'";
		}
		if($config['checkout_token']!=NULL) {
			$sql_config['where'][] = "checkout_token = '".$config['checkout_token']."'";
		}
		if($config['sale_id']!=NULL) {
			$sql_config['where'][] = "sale_id = '".$config['sale_id']."'";
		}
		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if(isset($config['gift'])) {
			$sql_config['where'][] = "gift = '".$config['gift']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['summary']) {
			$sql_config['field'] = ['id','price','quantity'];
		}

		$sql_config['sort'] = 'id DESC';
		$data = zulu::table_data('sale_cart',$id,$sql_config);

		if($config['summary']) {
			foreach($data as $data_row) {
				$sm['total'] += $data_row['price']*$data_row['quantity'];
				$sm['unit'] += $data_row['quantity'];
			}
			$data['_data'] = [
				'cart_total'	=>	$sm['total'],
				'cart_unit'		=>	$sm['unit'],
			];
		}

		return $data;
	}
	function cart_new($config=array()) {
		global $class_product;
		global $class_setting;
		$data['stat_add'] = time();
		$data['session'] = session_id();
		$data['product_id'] = $config['product_id'];
		$data['quantity'] = $config['quantity'];
		$data['tax_method'] = $class_setting->data['tax_method'];
		$data['tax_rate'] = $class_setting->data['tax_rate'];
		if($config['client_id'] > 0) {
			$data['client_id'] = $config['client_id'];
		} else {
			$data['client_id'] = $_SESSION['user']['id'];
		}

		$query = "INSERT INTO sale_cart ".$this->db->build(2,array('stat_add','session','product_id','quantity','tax_method','tax_rate','client_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function cart_edit($id,$config=array()) {
		if($id<1) {
			$data = $this->cart_new();
			$id = $data['id'];
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE sale_cart SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function cart_add($config) {
		global $class_product,$zulu,$class_user,$class_setting,$class_renew,$class_client;

		//Renew
		if($config['renew']!=NULL) {
			$renew_data = $class_renew->renew_data(['token'=>$config['renew']]);
			$class_renew->vars->data_row = $renew_data;
			$config['product'] = $class_renew->renew_product_id();
		}

		//Quan
		$quan = $config['qty'];
		$quantity = $quan;
		$pid = $config['product'];

		//Attributes? If so look up product or return null if not existent
		if(count($_POST['attribute'])>0) {
			$data_option = $class_product->product_option_data(['parent_id'=>$pid,'attribute'=>$_POST['attribute']]);
			if(count($data_option)!=1) {
				$error[] = "Please select a valid combination variant.";
			}
			foreach($_POST['attribute'] as $attr_label) {
				$attribute_label[] = stripslashes($attr_label);
			}
			$pid = $data_option[0];
		}

		//Load Product Data
		$row_PROD = $class_product->product_data(array('id'=>$pid));
		$row_META = $zulu->meta_array($class_product->product_meta($pid));

		//Variant Check
		if($row_PROD['type_variant']==1) {
			$error[] = "Please select a variant of this product.";
		}
		if($row_PROD['type_variant']==2&&count($_POST['attribute'])<=0) {
			$option_data = $class_product->product_option_data(['product_id'=>$row_PROD['id']]);
			foreach($option_data as $optrow) {
				$attribute_label[] = stripslashes($optrow['value']);
			}
		}

		if($row_META['object']=='membership') {
			if(isset($_SESSION['user']) && $class_client->client_subscribed($_SESSION['user']['id'])&&!$config['renew']&&!$config['switch']&&$class_setting->data['renew_multi']!=1) {
				$zulu->notification_set("You are already using a subscription, please visit <a href=\"".FE_rel."members/\">your account</a> to view more.",2);
				header("Location: ".FE_rel."browse/");
				exit;
			}
		}

		$cart_data = $this->cart_data(array('product_id'=>$pid,'client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'first'=>true));
		$custom = unserialize($cart_data['custom']);
		if($config['custom']['attribute_option']!=NULL) {
			$cart_data = $this->cart_data(array('product_id'=>$pid,'client_id'=>$_SESSION['user']['id'],'session'=>session_id()));
			foreach($cart_data as $cart_row) {
				$custom = unserialize($cart_row['custom']);
				if($config['custom']['attribute_option'] == $custom['attribute_option']) {
					$cart_data = $cart_row;
					$attr_opt_match = true;
					break;
				}
			}
			if(!$attr_opt_match) {
				unset($cart_data);
				$custom = [];
				foreach($config['custom']['attribute_option'] as $attr_slug=>$attr_opts) {
					$attr_row = $class_product->attribute_data(['product_id'=>($row_PROD['type_variant']==2?$row_PROD['parent_id']:$pid),'slug'=>$attr_slug,'first'=>true]);
					$attr_options = explode(',',$attr_row['options']);
					$attr_options_price = explode(',',$attr_row['options_price']);
					foreach($attr_opts as $opt) {
						$custom['attribute_option'][$attr_slug][] = ['name'=>$opt,'price'=>$attr_options_price[array_search($opt,$attr_options)]];
					}
				}
			}
		}
		$product_price = $class_product->price($pid, ['quantity'=>$quantity]);
		$price = $product_price['price'];

		//Renewing MEMBERSHIP?
		if($row_META['object']=='membership') {
			if($config['renew'] != NULL) {
				$token = mysqli_escape_string($config['renew']);
				$renew_data = $class_renew->renew_data(['token'=>$token]);
				$custom['renew_id'][$renew_data['id']] = $renew_data['id'];
			} elseif(!$class_setting->data['renew_multi'] && $custom['renew_id']==NULL && $custom['switch']==NULL && $_SESSION['user']['id']>0) {
				$renew_data = $class_renew->renew_data(['client_id'=>$_SESSION['user']['id'],'product_id'=>$pid,'template_id'=>$row_META['object_id'],'status'=>'1','sort'=>'renew_next DESC','first'=>true]);
				if($renew_data['id'] > 0) {
					$custom['renew_id'][$renew_data['id']] = $renew_data['id'];
				}
			}
		}

		if($config['switch']!=NULL) {
			$token = mysqli_escape_string($config['switch']);
			$renew_data = $class_renew->renew_data(['token'=>$token]);
			$custom['switch']['current'] = $renew_data['id']; //current renew row
			$switch_row = true;

			if($class_setting->data['renew_switch_fee']>0) { //-- figure out prorate cost
				$price += $class_setting->data['renew_switch_fee'];
					$this->vars->cart_add_nfc[] = "A switch fee applies and has been added to your membership switch cost, this fee is $".number_format($class_setting->data['renew_switch_fee'],2).".";
			}
			if($class_setting->data['renew_prorate']==1&&$renew_data['renew_next']>time()) { //-- figure out prorate cost
				$now = time();
				$next = $renew_data['renew_next'];
				$last = ($renew_data['renew_last']<=0?($renew_data['renew_first']>0?$renew_data['renew_first']:$renew_data['stat_add']):$renew_data['renew_last']);
				$sofar = $now-$last;
				$gap = $next-$last;
				$diff = number_format($sofar/$gap,7);
				$diff_credit_perc = 1-$diff;
				$sale_line = $this->sale_line_data(['first'=>true,'latest'=>true,'object'=>'membership','object_id'=>$renew_data['id']]);
				if($sale_line['total']>0&&$this->sale_balance($sale_line['sale_id'])<=0&&$diff_credit_perc>0) {
					$total_credit = $sale_line['total']*$diff_credit_perc;
					$this->vars->cart_add_nfc[] = "A credit of $".number_format($total_credit,2)." was applied to your switch from your existing memberships remaining credit.";
				} else {
					$total_credit = 0;
				}
				$price -= $total_credit;
			}
		}

		if($class_setting->data['renew_multi'] || (!$row_META['object']=='membership'&&!$row_META['object']=='pitexit')) {
			$quan = ($quan+$cart_data['quantity']);
		}
		if(count($renew_data['id'])>0) {
			$renew_row = true;
		}

		//If ordering membership
		switch($row_META['object']) {
			case 'membership':
			$template_data = $class_renew->template_data(['id'=>$row_META['object_id']]);
			$change_from = explode(",",$template_data['change_from']);
			$change_to = explode(",",$template_data['change_to']);
			if($template_data['opt_switch']==1&&(!$switch_row&&!$renew_row)) {
				$error[] = "Requires an existing membership to subscribe.";
			}
			break;
		}

		//-- Final data
		if($row_PROD['type_variant']==2&&trim($row_PROD['name'])==NULL) {
			$title = stripslashes($class_product->name($row_PROD['parent_id']));
			//-- load text of attribute.
			if(count($attribute_label)>0) {
				$title .= " (".implode(", ",$attribute_label).")";
			}
		} else {
			$title = stripslashes($row_PROD['name']);
		}
		if(count($custom['attribute_option']) > 0) {
			foreach($custom['attribute_option'] as $attr_slug=>$attr_opts) {
				foreach($attr_opts as $attr_opt) {
					$attr_names[] = stripslashes($attr_opt['name']);
				}
			}
			if(count($attr_names) > 0) {
				$title .= " [".implode(',',$attr_names)."]";
			}
		}

		$custom = serialize($custom);
		//-- Check errors and update
		if(count($error)<=0) {
			if($row_META['quantity_group_stop']>0) {
				unset($cart_data['id']);
			}
			if($cart_data['id']>0) {
				$quantity += $cart_data['quantity'];
			}
			$output = $this->cart_edit($cart_data['id'],array('product_id'=>$pid,'quantity'=>$quantity,'title'=>$title,'price'=>$price,'custom'=>$custom,'attribute'=>$attribute,'weight'=>$row_META['weight'],'volume'=>$row_META['volume']));
		} else {
			$output = ['success'=>false,'msg'=>implode("<br>",$error)];
		}
		return $output;
	}
	function cart_delete($id,$identifier='id') {
		$query = "DELETE FROM sale_cart WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function cart_dump($config=[]) {
		global $class_user,$zulu;

		if($_SESSION['user']['id']>0&&!isset($config['client_id'])) {
			$identifier = "client_id = '".$_SESSION['user']['id']."'";
		} elseif($config['client_id']>0) {
			$identifier = "client_id = '".$config['client_id']."'";
		} else {
			$identifier = "session = '".session_id()."'";
		}
		$query = "DELETE FROM sale_cart WHERE sale_id = 0 AND ".$identifier;
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function cart_move($where,$id) {
		switch($where) {
			case 'client':
			$set = "client_id = '".$id."'";
			$where = "session = '".session_id()."'";
			break;
			default:
			$set = "session = '".session_id()."'";
			$where = "client_id = '".$id."'";
			break;
		}
		if($this->db->query("UPDATE ".$this->SQL_table_sale_cart." SET ".$set." WHERE ".$where)) {
			return true;
		} else {
			return false;
		}
	}
	function cart_check() {
		$config = [];
		if($_SESSION['user']['id'] > 0) {
			$config['client_id'] = $_SESSION['user']['id'];
		} else {
			$config['session']= session_id();
		}
		$cart_data = $this->cart_data([$config]);
		if(count($cart_data) > 0) {
			return true;
		}
		return false;
	}

	/* Custom Functions */
	function pay_config() {
		global $zulu,$class_dps;

		$sale_row = $this->sale_data(['id'=>$this->vars->sale_id]);
		if($sale_row['coupon_id'] > 0) {
			$coupon = $this->coupon_data(array('id'=>$sale_row['coupon_id']));
			if($coupon['discount_object_id'] == 0) {
				$do_coupon = true;
			}
		}

		$lines = $this->vars->sale_line;
		$line_count = count($lines);
		foreach($lines as $data) {
			$sale_id = $data['sale_id'];
			$custom = unserialize($data['custom']);
			$profile = (isset($this->pay_profile[$custom['pay']])?$custom['pay']:$this->pay_default);
			if($coupon['discount_type'] == 'percent' && $do_coupon) {
				$coupon_amount = $data['total']*($coupon['discount_amount']/100);
			} elseif($coupon['discount_type'] == 'fixed' && $do_coupon) {
				$coupon_amount = $coupon['discount_amount']/$line_count;
			}
			$breakdown[$profile]['due'] += ($data['total'] - $coupon_amount);
			unset($coupon_amount);
		}

		$payments = $this->payment_data(['sale_id'=>$sale_id]);
		foreach($payments as $pay) {
			$method = unserialize($pay['method_data']);
			if(isset($this->pay_profile[$method['pay']])) {
				$breakdown[$method['pay']]['paid'] += $pay['pay_total'];
			} else {
				$breakdown[$this->pay_default]['paid'] += $pay['pay_total'];
			}
		}

		foreach($breakdown as $key=>$row) {
			$breakdown[$key]['balance'] = $row['due']-$row['paid'];
		}

		return $breakdown;
	}
	function link_object_selector($type,$config=[]) {
		global $class_book,$zulu,$class_schedule,$class_client;

		if($type=='book') {
			$event_data = $class_book->event_data(['sort'=>'name ASC']);
			foreach($event_data as $evrow) {
				$evdate = $class_book->event_date_options($evrow['id'],4,true,$config['value']);
				foreach($evdate as $date_id=>$date) {
					$sub_options[] = "<option value=\"{$date_id}\" ".($date_id==$config['value']?'selected':NULL).">".$date."</option>";
				}
				if(count($sub_options)>0) {
					$options[] = "<optgroup label=\"".$evrow['name']."\">{$next_ev}".implode("\n",$sub_options)."</optgroup>";
				}
				unset($sub_options);
			}
		} elseif($type=='schedule') {
			$bookings = $class_schedule->book_data(['date_start_min'=>strtotime("-3 months"),'date_start_max'=>strtotime("+6 months")]);
			foreach($bookings as $evrow) {
				if($evrow['client_id']>0) {
					$options[] = "<option value=\"".$evrow['id']."\" ".($evrow['id']==$config['value']?'selected':NULL).">".$zulu->date($evrow['date_start'],'d/m/Y g:ia')." - ".$class_client->client_name($evrow['client_id'])." ".($evrow['reference']!=NULL?'('.$evrow['reference'].')':NULL)."</option>";
					if($evrow['date_start']>time()&&!$future) {
						$next_ev = "<option value=\"".$evrow['id']."\" ".($evrow['id']==$config['value']?'selected':NULL).">NEXT: ".$zulu->date($evrow['date_start'],'d/m/Y g:ia')." - ".$class_client->client_name($evrow['client_id'])." ".($evrow['reference']!=NULL?'('.$evrow['reference'].')':NULL)."</option><option disabled>---</option>";
						$future = true;
					}
				}
			}
			$options = [$next_ev]+$options;
		}

		return $options;
	}
	function link_object_find($object,$object_id) {
		global $zulu;
		$sale_ids =[];
		$data = $zulu->table_data('sale_meta sm',0,['field'=>['sm.identifier'],'sort'=>'sm.id ASC','join'=>'sale_meta sm2 ON sm.identifier = sm2.identifier','where'=>['sm.field = "link_object"','sm.value = "book"','sm2.field = "link_object_id"','sm2.value = "'.$object_id.'"']]);
		foreach($data as $row) {
			$sale_ids[] = $row['identifier'];
		}
		return $sale_ids;
	}
	function process_refund($amount, $date, $reference, $payment_id, $line_id, $sale_id) {
		$refund_data = [];
		if($line_id>0){
			$refund_data['sale_line_id']= $line_id;
		}
		if($sale_id>0){
			$refund_data['sale_id']= $sale_id;
			if($amount>0){
				if($payment_id>0){
					$pay_data = $this->payment_data(array('id'=>$payment_id));
					if($pay_data['id']>0){
						$current_total_refunded = 0;
						$current_refund_data = $this->refund_data(['sale_payment_id'=>$pay_data['id']]);
						foreach($current_refund_data as $refund){
							$current_total_refunded += $refund['amount'];
						}
						if($current_total_refunded == $pay_data['pay_total']){
							$result = ['success'=>false,'msg'=>'Sorry, this transaction has already been fully refunded.'];
						}elseif($amount > $pay_data['pay_total']){
							$result = ['success'=>false,'msg'=>'Sorry, there is only $'.number_format($pay_data['pay_total'],2).' available to refund.'];
						}else{
							//do the refund
							$refund_data['amount'] 			= $amount;
							$refund_data['date'] 			= $date;
							$refund_data['reference'] 		= $reference;
							$refund_data['sale_payment_id'] = $pay_data['id'];

							$return = $this->refund_edit(0,$refund_data);
							if($return['success']){
								$result = ['success'=>true,'msg'=>'Refund processed and linked to payment successfully.'];
							}else{
								$result = ['success'=>false,'msg'=>'Database error occured'];
							}

						}
					}
				}else{
					//do the refund
					$refund_data['amount'] 			= $amount;
					$refund_data['date'] 			= $date;
					$refund_data['reference'] 		= $reference;

					$return = $this->refund_edit(0,$refund_data);
					if($return['success']){
						$result = ['success'=>true,'msg'=>'Refund processed successfully.'];
					}else{
						$result = ['success'=>false,'msg'=>'Database error occured'];
					}
				}
			}else{
				$result = ['success'=>false,'msg'=>'Sorry, amount must be greater than 0.00'];
			}
		}else{
			$result = ['success'=>false,'msg'=>'No Sale ID provided.'];
		}
		return $result;
	}

	function refund_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['client_id']!=NULL) {
			$sql_config['where'][] = "client_id = '".$config['client_id']."'";
		}
		if($config['sale_line_id']!=NULL) {
			$sql_config['where'][] = "sale_line_id = '".$config['sale_line_id']."'";
		}
		if($config['sale_payment_id']!=NULL) {
			$sql_config['where'][] = "sale_payment_id = '".$config['sale_payment_id']."'";
		}
		if($config['sale_id']!=NULL) {
			$sql_config['where'][] = "sale_id = '".$config['sale_id']."'";
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}

		$sql_config['sort'] = 'id DESC';
		$data = zulu::table_data('sale_refund',$id,$sql_config);

		return $data;
	}
	function refund_new($config=array()) {
		global $class_user, $zulu;
		$data['token'] = $zulu->serial();
		$data['stat_add'] = time();
		$data['user_id'] = $class_user->authorised->id;
		$data['admin_id'] = $class_user->authorised->child_id;

		$query = "INSERT INTO sale_refund ".$this->db->build(2,array('stat_add', 'user_id', 'admin_id', 'token'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function refund_edit($id,$config=array()) {
		if($id<1) {
			$data = $this->refund_new();
			$id = $data['id'];
		}

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE sale_refund SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function refund_delete($id) {
		$query = "DELETE FROM sale_refund WHERE id = '{$id}'";
		if($this->db->query($query)) {
			return array("success"=>true);
		} else {
			return array("success"=>false);
		}
	}

	function currencyCode($sale_id) {
		$sale_row = $this->sale_data(['id'=>$sale_id]);
		$currency_id = $sale_row['currency_id'];
		if($currency_id <= 0) {
			$currency_id = PriceList::$default_location_id;

		}
		$currency = Currency::find($currency_id);
		$currency_code = $currency->code;

		return $currency_code;
	}

	function reference($reference) {
		if(trim($reference)==NULL) $reference = 0;
		/*if(is_numeric($reference)) {
			$reference = str_pad($reference, 6, '0', STR_PAD_LEFT);
		}*/
		return $reference;
	}

	function name() {
		global $class_setting;

		switch($this->SQL_table_sale) {
			case 'sale':
				$output = ($class_setting->data['sale_name']!=NULL?stripslashes($class_setting->data['sale_name']):'Sale');
				break;
			case 'porder':
				$output = 'Purchase Order';
				break;
			case 'sale_cn':
				$output = 'Credit Note';
				break;
			case 'sorder':
				$output = 'Sales Order';
				break;
			case 'sale_quote':
				$output = 'Sales Quote';
				break;
			case 'shipment':
			case 'ship':
				$output = 'Shipment';
				break;
			case 'product_stock_transfer':
				$output = 'Stock Transfer';
				break;
			case 'product_delivery':
				$output = 'Product Delivery';
				break;
			case 'return':
			case 'product_return':
				$output = 'Product Return';
				break;
			default:
				$output = 'Sale';
				break;
		}
		return $output;
	}

	function sale_id_from_line_id($line_id) {
		$sale_line_data = $this->sale_line_data(['id'=>$this->db->escape_string($line_id),'field'=>['sale_id']]);
		return ($sale_line_data['sale_id']>0?$sale_line_data['sale_id']:0);
	}

	function date_due_default_unix($client_id=0,$from=0) {
		global $zulu,$class_setting;
		$offset = 0;
		$term = NULL;
		if($client_id>0) {
			$client_payment_term = $zulu->meta_value("client",$client_id,'payment_term');
			if(trim($client_payment_term['value'])!=NULL) {
				$term = $client_payment_term['value'];
			}
		}
		if($term==NULL) {
			if(trim($class_setting->data['sale_payment_term'])!=NULL) {
				$term = $class_setting->data['sale_payment_term'];
			} else {
				$term = '7day';
			}
		}

		switch($term) {
			default:
			case 'eom':
				$strto = 'last day of this month 00:00:00';
				break;
			case '7day':
				$strto = '+7 days';
				break;
			case '20month':
				if(date('d',$from)>20) {
					$strto = 'first day of next month 00:00:00';
				} else {
					$strto = 'first day of this month 00:00:00';
				}
				$offset = 86400*19;
				break;
			case 'lastmonth':
				$strto = 'last day of next month 00:00:00';
				$offset = 0;
				break;
			case '20nextmonth':
				$strto = 'first day of next month 00:00:00';
				$offset = 86400*19;
				break;
			case 'lastnextmonth':
				$strto = 'last day of next month 00:00:00';
				$offset = 0;
				break;
			case 'now':
				$strto = 'today 00:00:00';
				break;
		}
		//echo date('j M',strtotime($strto)+$offset);exit;
		if($from>0) {
			return strtotime($strto,$from)+$offset;
		} else {
			return strtotime($strto)+$offset;
		}
	}

}
$class_sale = new sale($MAIN_config);
