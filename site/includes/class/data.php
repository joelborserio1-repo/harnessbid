<?php
//--DATA
//--Manages the IMPORT / EXPORT functionality for the site main classes

class data {
	function __construct() {
		global $db;
		$this->db = $db;
		
		$this->template = array(
			"client"=> //-- client config
			array(
				"name"						=>	"Clients",
				"table"						=>	"client",
				"page"						=>	"client",
				"import"					=>	true,
				"import_duplicate"		=>	array("company",array("name_first","name_last")),
				"import_duplicate_status"		=>	1,
				"import_require"			=>	array(),
				"import_require_either"	=>	array("company","name_first","name_last"),
				"import_fields"			=>
				array(
					"root"=>
					array(
						"company"=>NULL,
						"name_first"=>NULL,
						"name_last"=>NULL,
						"email"=>array("validate"=>"email"),
						"phone"=>NULL,
						"website"=>NULL,
						"notes"=>NULL,
						"hourly_rate"=>array("format"=>"currency"),
						"refer"=>NULL,
						"supplier"=>array("match"=>array(1=>"Yes",0=>"No")),
						"type"=>array("match"=>array(1=>"Client",2=>"Lead",3=>"Cancelled"))
					),
					"meta"=>
					array(
						"ship_address"=>NULL,
						"ship_suburb"=>NULL,
						"ship_city"=>NULL,
						"ship_post"=>NULL,
						"ship_country"=>NULL,
						"bill_address"=>NULL,
						"bill_suburb"=>NULL,
						"bill_city"=>NULL,
						"bill_post"=>NULL,
						"bill_country"=>NULL,
					)
				),
				"export"	=>	true,
				"export_fields"	=>
				array(
					"root"=>
					array(
						"company"=>NULL,
						"name_first"=>NULL,
						"name_last"=>NULL,
						"email"=>NULL,
						"phone"=>NULL,
						"website"=>NULL,
						//"notes"=>NULL,
						"hourly_rate"=>NULL,
						"refer"=>NULL,
						"supplier"=>array("match"=>array(1=>"Yes",0=>"No")),
						"type"=>array("name"=>"Client Type","match"=>array(1=>"Client",2=>"Lead",3=>"Cancelled"))
					),
					"meta"=>
					array(
						"ship_address"=>NULL,
						"ship_suburb"=>NULL,
						"ship_city"=>NULL,
						"ship_post"=>NULL,
						"ship_country"=>NULL,
						"bill_address"=>NULL,
						"bill_suburb"=>NULL,
						"bill_city"=>NULL,
						"bill_post"=>NULL,
						"bill_country"=>NULL,
					)
				),
			),"product"=> //-- product config
			array(
				"name"						=>	"Products",
				"table"						=>	"product",
				"page"						=>	"product",
				"import"					=>	true,
				"import_duplicate"			=>	array("sku"),
				"import_require"			=>	array(),
				"import_require_either"	=>	array("sku","name"),
				"import_fields"			=>
				array(
					"root"=>
					array(
						"sku"=>array("name"=>"SKU"),
						"name"=>array("name"=>"Product Title"),
						"description"=>NULL,
						"price"=>array("format"=>"currency"),
						"price_special"=>array("name"=>"Special Price (0 = none)","format"=>"currency"),
						"status"=>array("match"=>array(1=>"Active",0=>"Inactive"))
					),
					"meta"=>
					array(
						"stock"=>array("format"=>"number")
					)
				),
				"export"	=>	true,
				"export_fields"	=>
				array(
					"root"=>
					array(
						"sku"=>NULL,
						"name"=>NULL,
						"price"=>NULL,
						"price_special"=>NULL,
						"description"=>NULL
					),
					"meta"=>[]
				),
			),
			"sale"=> //-- sale config
			array(
				"name"		=>	"Sales",
				"table"		=>	"sale",
				"page"		=>	"sale",
				"import"	=>	false,
				"export"	=>	true,
				"export_fields"	=>
				array(
					"root"=>
					array(
						"reference"=>NULL,
						"name"=>NULL,
						"date"=>array("convert"=>"date"),
						"date_due"=>array("convert"=>"date"),
						"email"=>NULL,
						"coupon"=>NULL,
						"pay_method"=>NULL,
						"refer"=>NULL,
						"status"=>array("match"=>array(0=>"Draft",1=>"Complete",2=>"Voided"))
					),
					"meta"=>
					array(
						//Meta fields
						"ship_method"=>NULL,
						"bill_address"=>NULL,
						"bill_suburb"=>NULL,
						"bill_city"=>NULL,
						"bill_post"=>NULL,
						"ship_address"=>NULL,
						"ship_suburb"=>NULL,
						"ship_city"=>NULL,
						"ship_post"=>NULL,
						"ship_notes"=>NULL
					),
					"custom"=>
					array(
						//Custom values
						"sale_total"=>['data_function'=>'sale_total'],
						"sale_balance"=>['data_function'=>'sale_balance'],
						"sale_line_string"=>['data_function'=>'sale_line_string'],
					),
				),
			),
		);	
	}
	
	//-- Template Type Array
	function template_array($type=NULL) {
		foreach($this->template as $key=>$row) {
			if($type==NULL) {
				$data[$key] = $row['name'];	
			} elseif($row[$type]) {
				$data[$key] = $row['name'];	
			}
		}
		return $data;	
	}
	
	//-- Field Label
	function field_label($ref,$config) {
		if($config['name']!=NULL) {
		 	return $config['name'];	
		} else {
			return ucwords(strtolower(str_replace("_"," ",$ref)));
		}
	}
	
	//-- Field Array
	function field_array($template,$type='import') {
		$data_set = $this->template[$template];
		
		if($data_set[$type]) { //only execute if enabled
			if(count($data_set[$type.'_fields']['root'])>0) {
				foreach($data_set[$type.'_fields']['root'] as $ref=>$config) {
					$data[$ref] = $this->field_label($ref,$config);
				}
			}
			if(count($data_set[$type.'_fields']['meta'])>0) {
				foreach($data_set[$type.'_fields']['meta'] as $ref=>$config) {
					$data['m_'.$ref] = $this->field_label($ref,$config);
				}
			}
		}
		return $data;	
	}
	
	//--DATA: check_duplicate
	function check_duplicate($data,$fields,$match,$config=[]) {
		global $zulu;
		
		foreach($fields as $field) {
			if(is_array($field)) {
				foreach($field as $sub_field) {
					if(trim($data[$match[$sub_field]])!=NULL) {
						$sub_where[] = $sub_field." = '".$data[$match[$sub_field]]."'";	
					}
				}
				$sql[] = "(".implode(" AND ",$sub_where).")";
			} else {
				if(trim($data[$match[$field]])!=NULL) {
					$sql[] = $field." = '".$data[$match[$field]]."'";	
				}
			}
		}
		$where = "(".implode(" OR ",$sql).")".(isset($config['status'])?" AND status = '".$config['status']."'":NULL);

		$result = $this->db->mysqli->query("SELECT id FROM ".$this->vars->table." WHERE {$where}");
		$row = $result->fetch_array();
		return ($result->num_rows>0&&$row['id']>0?$row['id']:false);
	}
	
	//--DATA: data_validate
	function data_validate($data,$type) {
		switch($type) {
			case 'email':
			$out = (filter_var($data, FILTER_VALIDATE_EMAIL)?true:false);
			$msg = "Should be in format joe@example.com.";
			break;
			case 'number':
			$out = (is_numeric($data)?true:false);
			$msg = "Should only contain 0123456789.";
			break;
		}
		return array('success'=>$out,'message'=>$msg);
	}
	
	//--DATA: data_validate
	function data_format($data,$type) {
		switch($type) {
			case 'currency':
			$val = sprintf("%.2f",preg_replace("/[^0-9\.-]/", "",$data));
			break;
			case 'number':
			$val = preg_replace('/\D/', '', $data);
			break;
		}
		return $val;
	}
	
	//--DATA: custom_data
	function custom_data($data,$type) {
		global $class_sale;
		
		switch($type) {
			case 'sale_line_string':
				$sale_id = $data['id'];
				$sale_line_data = $class_sale->sale_line($sale_id);
				$sale_items=[];
				foreach($sale_line_data as $sale_line){
					$sale_items[] = str_replace(',', ' ' , $sale_line['description']);
				}
				$val = implode(' / ',$sale_items);
				break;
			case 'sale_total':
				$val = $this->data_format($class_sale->sale_total($data['id']),'currency');
				break;
			case 'sale_balance':
				$val = $this->data_format($class_sale->sale_balance($data['id']),'currency');
				break;
		}
		return $val;
	}
	
	//--DATA: data_decode
	function data_decode($data,$type) {
		switch($type) {
			case 'currency':
			$val = "$".sprintf("%.2f",$data);
			break;
			case 'date':
			$val = date('d/m/Y', $data);
			break;
		}
		return $val;
	}
}