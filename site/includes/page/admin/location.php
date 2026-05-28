<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'location');
define(PAGE_name,'Locations');
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

		$query = Location::query();
		$query_all = clone $query;
		$query->orderBy('sort', 'ASC');
		$query->orderBy('name', 'ASC');
		$locations = $query->offset($start)->take(MAX_per_page)->get();
		$locations_all = $query_all->get();

        $table_column = $table_row = [];
		$table_column[] = array("Location",array('class'=>array('')));
		$table_column[] = array("Enabled",array('class'=>array('')));
		$table_column[] = array("Currency",array('class'=>array('')));
		$table_column[] = array("Tax Enabled",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

        foreach($locations as $location) {

			$currency = $location->currency;

            $buttons = [
				['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'region','Location'=>$location->id))),'label'=>'Regions','icon'=>'bars','class'=>'info'],
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'edit','id'=>$location->id))),'label'=>'Edit','icon'=>'edit','class'=>'primary'],
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'delete','id'=>$location->id))),'label'=>'Remove','icon'=>'times','class'=>'danger','class_append'=>['confirm']]
            ];

            $table_row[] = array("content" => array(
                array($location->name),
				array(($location->status?'Yes':'No')),
				array(($currency?$currency->name:'')),
				array(($location->tax_disable?'No':'Yes')),
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
		if(Location::find(PAGE_id)->delete()) {
			$zulu->notification_set("Location removed successfully.",1);
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

            if(!$_POST) {
                $temp_folder = date("Ymd")."-".$zulu->serial(8);
            } else {
                $temp_folder = $_POST['temp_folder'];
            }
            $main_path = 'temp/'.$temp_folder.'/';

		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
            $new = false;

            $location = Location::find($id);
            $main_path = $location->token.'/';
		}

		if($_GET['Do'] == 'ClearImage') {
			@unlink(Location::$file_path.$location->token."/".$location->image);
            $location->image = '';
            $location->save();
            header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'filter'=>['Do']]));
            exit;
		}

		//Form Submit
		if($_POST['action']=='edit') {
			$form_edit->valid = true;

            if($form_edit->validate(['name','currency_id'])) {
                $zulu->notification_set("Please enter all fields denoted *.",2);
                $form_edit->valid = false;

            }

			if($form_edit->valid) {

                $location = Location::firstOrNew(['id'=>$id]);
				$location->name 			= $_POST['name'];
				$location->currency_id 		= $_POST['currency_id'];
				$location->status 			= $_POST['status'];
				$location->sort 			= $_POST['sort'];
				$location->tax_disable 		= $_POST['tax_disable'];
				$location->tax_method 		= $_POST['tax_method'];
				$location->tax_rate 		= $_POST['tax_rate'];
				$location->pedigree_link 	= $_POST['pedigree_link'];
				$location->usta_lookup 		= $_POST['usta_lookup'];
				$location->save();

				if($location->id > 0) {

                    if($new) {
                        $temp_folder = Location::$file_path.$main_path;
                        $file_content = glob($temp_folder."*");
                        if(count($file_content) > 0) {
                            $main_path = $location->token.'/';
                            @mkdir(Location::$file_path.$main_path);
                            foreach($file_content as $file) {
                                $basename = basename($file);
                                @copy($file, Location::$file_path.$main_path.$basename);
                                @unlink($file);
                                $location->image = $basename;
                                $location->save();
                            }
                        }
                        @rmdir($temp_folder);
                    }

					$zulu->notification_set("Location ".(!$new?"updated":"created")." successfully.",1);
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
				$_POST['tax_disable'] = 1;
				$_POST['status'] = 1;
			}

        } else {
            $zulu->nav->breadcrumb['Edit'] = array();
			$zulu->nav->title = "Edit";

            if(!$_POST) {
				$_POST = $location->getAttributes();
			}

            $image = $location->image();
        }

		@mkdir(Location::$file_path);
        @mkdir(Location::$file_path.$main_path);
        $class_file->uploadifive_new("image_main",['preview'=>false,'post'=>['action'=>'location_image','location_id'=>PAGE_id,'file_name'=>'','path'=>Location::$file_folder.$main_path],'setting'=>['multi'=>false,'queueSizeLimit'=>1],'event'=>['complete'=>'']]);

		$currency_options = Currency::optionArray();

	}

	if(PAGE_action=='region') {

        /*$redirect = false;
        if(PAGE_id <= 0) {
            $redirect = true;
        } else {
            $location = Location::find(PAGE_id);
            if($location == null) {
                $redirect = true;
            }
        }
        if($redirect) {
            header("Location: ".$zulu->link_page(PAGE_file));
            exit;
        }*/

		$form_edit = new form;
		$start = ($_GET['Pg']>1?MAX_per_page*($_GET['Pg']-1):0);
		$query = LocationRegion::query();

        if(isset($_GET['Search']) && $_GET['Search'] != null) {
            $query->where('name', 'like', "%".$_GET['Search']."%");
        }
		if(isset($_GET['Location']) && $_GET['Location'] > 0) {
            $query->where('location_id', $_GET['Location']);
        }

		$query_all = clone $query;
		$query->orderBy('sort', 'ASC');
		$query->orderBy('name', 'ASC');
		$regions = $query->offset($start)->take(MAX_per_page)->get();
		$regions_all = $query_all->get();

        $table_column = $table_row = [];
		$table_column[] = array("Location",array('class'=>array('')));
		$table_column[] = array("Region",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

        foreach($regions as $region) {

			$location = $region->location;

            $buttons = [
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'region_edit','id'=>$region->id))),'label'=>'Edit','icon'=>'edit','class'=>'primary'],
                ['link'=>$zulu->link_page(PAGE_file,array('self'=>true,'query'=>array('Action'=>'region_delete','id'=>$region->id))),'label'=>'Remove','icon'=>'times','class'=>'danger','class_append'=>['confirm']]
            ];

            $table_row[] = array("content" => array(
                array(($location?$location->name:'')),
				array($region->name),
                array($zulu->button_render($buttons),array('class'=>array('right')))
            ));
        }

		$zulu->template->body = $zulu->table_render($table_row,0,array('class'=>'sortable','data_table'=>false));
        $total_count = count($regions_all);
        $pagination = $zulu->pagination($_GET['Pg'],['count'=>$total_count,'link'=>$zulu->link_page(PAGE_file,array('self'=>true))]);
        $zulu->nav->title = 'Regions';
        //$zulu->nav->breadcrumb[$league->title] = array();
        $zulu->nav->breadcrumb['Regions'] = array();

		$location_options = Location::optionArray();

		$zulu->template->jquery[] = "
		$('select').change(function() {
			$(this).closest('form').submit();
		});
		";

	}

	if(PAGE_action=='region_delete') {
        $region = LocationRegion::find(PAGE_id);
		if($region->delete()) {
			$zulu->notification_set("Region removed successfully.",1);
		} else {
			$zulu->notification_set($result['msg'],2);
		}
        header("Location: ".$zulu->link_page(PAGE_file,['self'=>'true','query'=>['Action'=>'region']]));
        exit;
	}

	if(PAGE_action=='region_edit') {
		$form_edit = new form;

		if(PAGE_id<1) {
			$id = 0;
			$new = true;

            /*if($_GET['location'] <= 0) {
                header("Location: ".$zulu->link_page(PAGE_file));
                exit;
            }
            $league = League::find($_GET['league']);*/

		} else {
			$id = ($_POST['id']>0?$_POST['id']:($_GET['id']>0?$_GET['id']:0));
            $new = false;
            $region = LocationRegion::find($id);
		}

		if($_POST['action']=='edit') {
			$form_edit->valid = true;

            if($form_edit->validate(['name','location_id'])) {
                $zulu->notification_set("Please enter all fields denoted *.",2);
                $form_edit->valid = false;

            }

			if($form_edit->valid) {

				if($new) {
				    $region = new LocationRegion();
				}
                $region->name 			= $_POST['name'];
				$region->location_id    = $_POST['location_id'];
                $region->status    		= $_POST['status'];
				$region->sort    		= (int)$_POST['sort'];
				$region->save();

				if($region->id > 0) {

					$zulu->notification_set("Region ".(!$new?"updated":"created")." successfully.",1);
					header("Location: ".$zulu->link_page(PAGE_file, ['self'=>true, 'query'=>['Action'=>'region']]));
					exit;
				} else {
					$zulu->notification_set("A database error occurred.",2);
				}
			}
		}

        if($new) {
            $zulu->nav->breadcrumb['Regions'] = array('link'=>$zulu->link_page(PAGE_file,['query'=>['Action'=>'region']]));
            $zulu->nav->breadcrumb['New'] = array();
			$zulu->nav->title = "New";

			if(!$_POST) {
				$_POST['status'] = 1;
			}

        } else {

            $zulu->nav->breadcrumb['Edit'] = array();
			$zulu->nav->title = "Edit";

            if(!$_POST) {
				$_POST = $region->getAttributes();
			}
        }

		$location_options = Location::optionArray();

	}

}
