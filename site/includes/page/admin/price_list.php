<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'price_list');
define(PAGE_name,'Price List');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	if(PAGE_action==NULL) {	//grid page

		$form_edit = new form;

		$locations = Location::where('status', 1)->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();

		if($_POST['action']=='edit') {
			$form_edit->valid = true;

			if($form_edit->valid) {

				foreach($_POST['price_list'] as $price_key=>$location_data) {
					foreach($location_data as $location_id=>$price_list_data) {
						$price_list = PriceList::firstOrCreate(['code' => $price_key, 'location_id' => $location_id]);
						$price_list->price = $price_list_data['price'];
						$price_list->price_subscribe = $price_list_data['price_subscribe'];
						$price_list->save();
					}
				}

				$zulu->notification_set("Price List updated successfully.",1);
				header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true]));
				exit;
			}
		}

		if(!$_POST) {
			$_POST['price_list'] = [];
			foreach(PriceList::$price_options as $price_key=>$price_option) {
				$_POST['price_list'][$price_key] = [];
				foreach($locations as $location) {
					$price_list = PriceList::firstOrCreate(['code' => $price_key, 'location_id' => $location->id]);
					$_POST['price_list'][$price_key][$location->id] = $price_list->getAttributes();
				}
			}
		}

		$price_tables = [];
		foreach(PriceList::$price_options as $price_key=>$price_option) {
			$table_column = $table_row = [];
			$table_column[] = array("Location",array('class'=>array('')));
			$table_column[] = array("Price",array('class'=>array('')));
			$table_column[] = array("Subscribers Price",array('class'=>array('')));
			$table_row[] = array(
					"header"	=>	 true,
					"class"		=>	"",
					"content"	=>	$table_column);

			foreach($locations as $location) {
				$table_row[] = array("content" => array(
	                array($location->name),
					array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html("number","price_list[".$price_key."][".$location->id."][price]",$_POST['price_list'][$price_key][$location->id]['price'], ['placeholder'=>'0.00','custom'=>['min'=>'0','step'=>'any']])."</div>"),
					array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html("number","price_list[".$price_key."][".$location->id."][price_subscribe]",$_POST['price_list'][$price_key][$location->id]['price_subscribe'], ['placeholder'=>'0.00','custom'=>['min'=>'0','step'=>'any']])."</div>"),
	            ));
			}
			$price_tables[$price_key] = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false));
		}

        $zulu->nav->title = PAGE_name;
        $zulu->nav->breadcrumb[PAGE_name] = array();

	}

	if(PAGE_action=='edit') { //edit page
		$form_edit = new form;

		if(PAGE_id<1) {
			$id = 0;
			$new = true;

		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
            $new = false;

            $currency = Currency::find($id);
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

            if($form_edit->validate(['name','code'])) {
                $zulu->notification_set("Please enter all fields denoted *.",2);
                $form_edit->valid = false;

            }

			if($form_edit->valid) {

                $currency = Currency::firstOrNew(['id'=>$id]);
				$currency->name 			= $_POST['name'];
				$currency->code 			= $_POST['code'];
				$currency->status 			= $_POST['status'];
				$currency->sort 			= $_POST['sort'];
				$currency->save();

				if($currency->id > 0) {

					$zulu->notification_set("Currency ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true,'filter'=>['Action']]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

		if($new) {
            $zulu->nav->breadcrumb['New'] = array();
			$zulu->nav->title = "New";

			if(!$_POST) {
				$_POST['status'] = 1;
			}

        } else {
            $zulu->nav->breadcrumb['Edit'] = array();
			$zulu->nav->title = "Edit";

            if(!$_POST) {
				$_POST = $currency->getAttributes();
			}

        }

	}

}
