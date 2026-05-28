<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'currency');
define(PAGE_name,'Currencies');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section

	$zulu->template->head = "";
	$zulu->template->body = "";

	if(PAGE_action==NULL) {	//grid page

		$form_edit = new form;

        $start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);

		$query = Currency::query();
		$query_all = clone $query;
		$query->orderBy('sort', 'ASC');
		$query->orderBy('name', 'ASC');
		$currencies = $query->offset($start)->take(MAX_per_page)->get();
		$currencies_all = $query_all->get();

        $table_column = $table_row = [];
		$table_column[] = array("Currency",array('class'=>array('')));
		$table_column[] = array("Code",array('class'=>array('')));
		$table_column[] = array("Enabled",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

        foreach($currencies as $currency) {

            $buttons = [
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'edit','id'=>$currency->id))),'label'=>'Edit','icon'=>'edit','class'=>'primary'],
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'delete','id'=>$currency->id))),'label'=>'Remove','icon'=>'times','class'=>'danger','class_append'=>['confirm']]
            ];

            $table_row[] = array("content" => array(
                array($currency->name),
				array($currency->code),
				array(($currency->status?'Yes':'No')),
                array($zulu->button_render($buttons),array('class'=>array('right')))
            ));
        }

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'','data_table'=>false));
        $total_count = count($locations_all);
        $pagination = $zulu->pagination($_GET['Pg'],['count'=>$total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
        $zulu->nav->title = PAGE_name;
        $zulu->nav->breadcrumb[PAGE_name] = array();

	}

	if(PAGE_action=='delete') { //delete
		if(Currency::find(PAGE_id)->delete()) {
			$zulu->notification_set("Currency removed successfully.",1);
			header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true,'filter'=>['Action']]));
			exit;
		} else {
			$zulu->notification_set("A database error occurred.",2);
		}
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
