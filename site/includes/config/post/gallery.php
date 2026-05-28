<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: NEWS

if(MASTER_section=='admin') {

	//-- ADMIN
	$confform = new form;
	
	//-- Random
	//Get categories if any
	$category_data = $class_post->post_data(['type'=>'gallery_category']);
	$category_options = [];
	$category_options[0] = "None";
	foreach($category_data as $cat){
		$category_options[$cat['id']] = $cat['title'];
	}

	//-- Fields
	$FIELD['head'] = [
		
	];
	
	$FIELD['sidebar'] = [
		'gallery_category'=>
			[
				'label'=>'Category',
				'field'	=>	['name'=>'meta[gallery_category]','type'=>'select','value'=>$_POST['meta']['gallery_category'],'config'=>['option'=>$category_options]]],
	];
	
	$FIELD['body'] = [
		
	];
	
	//---------------//
	//-- Post Save --//
	//---------------//
	//-- Update vars: $meta & $data ONLY
	//-- Return FALSE creates error message
	function postc_save() {
		global $FIELD,$data,$meta,$zulu;
		
		return true;
	}
	
	
	//---------------//
	//-- Post Load --//
	//---------------//
	//-- Update vars: $_POST
	function postc_load() {
		global $FIELD,$zulu,$new;
		
		return;
	}
	
} else {
	
	//-- FRONTEND
	
}