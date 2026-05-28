<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'customer');
define(PAGE_name,'Customers');
$zulu->nav->breadcrumb['Customers'] = array("link"=>$zulu->link_page('customer'));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	if(PAGE_action=='sf_customer'||PAGE_action=='sf_client') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND status = 1";
		if(strlen($key) <= 2) $sql_where[] = "(name_first LIKE '{$key}%' OR name_last LIKE '{$key}%' OR company LIKE '{$key}%')";
		else $sql_where[] = "(name_first LIKE '%{$key}%' OR name_last LIKE '%{$key}%' OR company LIKE '%{$key}%' OR CONCAT(name_first,' ',name_last) LIKE '%{$key}%')";
		$user_data = zulu::table_data("client",0,array("where"=>$sql_where,"fields"=>array("name_first","name_last","id","token"),"limit"=>20));
		if(count($user_data)>0) {
			foreach($user_data as $row) {
				if(trim($row['company'])!=NULL) {
					$name = $row['company'];
				} else {
					$name = $row['name_first']." ".$row['name_last'];
				}
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$name."\" href=\"#\">".$name."</a></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_project') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND (title LIKE '%{$key}%' OR reference = '{$key}' OR reference LIKE '{$key}%')";
		$user_data = zulu::table_data("job",0,array("where"=>$sql_where,"fields"=>array("title","id","status","reference","client_id"),'sort'=>'status ASC,title ASC'));

		if(count($user_data)>0) {
			foreach($user_data as $row) {
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$row['title']."\" href=\"#\">".($row['status']==1?"<span class=\"opt opt-success\">Complete</span>":"<span class=\"opt opt-warning\">In Production</span>")."<br>(".$class_project->config->reference_prefix.'-'.$row['reference'].") ".stripslashes($row['title'])." for ".$class_client->client_name($row['client_id'])."</a></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_sale') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND reference > 0 AND status = 1 AND (reference LIKE '%{$key}%' OR reference = '{$key}' OR name LIKE '{$key}%')";
		$user_data = zulu::table_data("sale",0,array("where"=>$sql_where,"fields"=>array("name","reference","status","client_id"),'sort'=>'id DESC'));

		if(count($user_data)>0) {
			foreach($user_data as $row) {
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"Sale ".$row['reference']."\" href=\"#\">Sale ".$row['reference']."<br>".$class_client->client_name($row['client_id'])." <span class=\"opt opt-grey\">".$class_sale->currency_symbol.$class_sale->sale_total($row['id'],['cached'=>true])."</span></a></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_product') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND type = 'product' AND status = 1 AND live = 1 AND sys = 0 AND (sku LIKE '%{$key}%' OR name LIKE '%{$key}%' OR description LIKE '%{$key}%')";
		// OR price = '{$key}' OR price = '{$key}'
		$user_data = zulu::table_data("product",0,array("where"=>$sql_where,"fields"=>array("name_first","name_last","id","token")));

		if(count($user_data)>0) {
			foreach($user_data as $row) {
				$price = $class_product->price($row['id']);
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$row['id']."\" href=\"#\">".$row['sku']." - ".$row['name']."</a> <span class=\"color-grey\">$".$zulu->dollar($price['price'],true)."</span></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
    if(PAGE_action=='sf_product_main') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND type = 'product' AND status = 1 AND live = 1 AND type_variant IN (0,1) AND sys = 0 AND (sku LIKE '%{$key}%' OR name LIKE '%{$key}%')";
		$user_data = zulu::table_data("product",0,array("where"=>$sql_where,"field"=>array("id","sku","name")));

		if(count($user_data)>0) {
			foreach($user_data as $row) {
				$price = $class_product->price($row['id']);
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$row['name']."\" href=\"#\">".($row['sku']!=null?$row['sku']." - ":null).$row['name']."</a> <span class=\"color-grey\">$".$zulu->dollar($price['price'],true)."</span></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_category') {
		$key = $db->escape_string($_GET['Value']);
		$sql_where[] = "user_id = '".$class_user->authorised->id."' AND type = 'category' AND status = 1 AND sys = 0 AND (sku LIKE '%{$key}%' OR name LIKE '%{$key}%' OR description LIKE '%{$key}%')";
		// OR price = '{$key}' OR price = '{$key}'
		$user_data = zulu::table_data("product",0,array("where"=>$sql_where,"fields"=>array("name_first","name_last","id","token")));

		if(count($user_data)>0) {
			foreach($user_data as $row) {
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$row['name']."\" href=\"#\">".stripslashes($row['name'])."</a></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_product_sku') {
		$key = $db->escape_string($_GET['Value']);
		$query = $db->mysqli->query("SELECT prod.id,token,sku,name FROM product AS prod JOIN product_meta AS meta ON prod.id=meta.identifier WHERE user_id = '".$class_user->authorised->id."' AND type = 'product' AND type_variant IN (0,2) AND status = 1 AND live = 1 AND sys = 0 AND sku != '' AND (sku LIKE '%{$key}%' OR name LIKE '%{$key}%' OR description LIKE '%{$key}%' OR (meta.value='{$key}' AND meta.field = 'barcode')) GROUP BY prod.id") or die($db->mysqli->error);
		if($query->num_rows>0) {
			$prod_arr = [];
			while($row = $query->fetch_assoc()) {
				$prod_arr[$row['sku']] = stripslashes($class_product->name($row['id']));
			}
			ksort($prod_arr);
			foreach($prod_arr as $sku=>$name) {
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$sku."\" data-raw=\"".$sku."\" href=\"#\">".$sku." - ".$name."</a></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
	if(PAGE_action=='sf_product_sku_id') {
		$key = $db->escape_string($_GET['Value']);
		$query = $db->mysqli->query("SELECT prod.id,token,sku,name,price,price_special FROM product AS prod JOIN product_meta AS meta ON prod.id=meta.identifier WHERE user_id = '".$class_user->authorised->id."' AND type = 'product' AND status = 1 AND live = 1 AND sys = 0 AND (sku LIKE '%{$key}%' OR name LIKE '%{$key}%' OR description LIKE '%{$key}%' OR (meta.value='{$key}' AND meta.field = 'barcode')) GROUP BY prod.id") or die($db->mysqli->error);
		if($query->num_rows>0) {
			while($row = $query->fetch_assoc()) {
				$price = $class_product->price($row['id']);
				echo "<li><a class=\"sf-selector\" data-populate=\"".$_GET['Populate']."\" data-id=\"".$row['id']."\" data-raw=\"".$row['id']."\" href=\"#\">".$row['sku']." - ".$row['name']."</a> <span class=\"color-grey\">$".$zulu->dollar($price['price'],true)."</span></li>";
			}
		} else {
			echo "<li>No results found...</li>";
		}
		exit;
	}
}
