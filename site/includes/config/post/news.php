<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: NEWS

if(MASTER_section=='admin') {

	//-- ADMIN
	$confform = new form;
	
	//-- Random
	$auths = (array)$confform->userOptionForm();
	
	//-- Fields
	$FIELD['head'] = [
		'author'		=>
			[
				'label'=>'Author',
				'field'	=>	['name'=>'extra[author_id]','type'=>'select','value'=>(isset($_POST['author_id'])?$_POST['author_id']:($class_user->authorised->role!='admin'?$class_user->authorised->child_id:$class_user->authorised->id)),'config'=>['option'=>['0'=>'None']+$auths]]],
		'publish_date'=>
			[
				'label'=>'Publish Date',
				'field'	=>	['name'=>'meta[date]','type'=>'input','value'=>$_POST['meta']['date'],'config'=>['class'=>['input-date']]]],
	];
	$FIELD['sidebar'] = [];
	$FIELD['body'] = [
		'excerpt'	=>
			[
				'label'=>'Excerpt',
				'class'	=>	['col-md-12'],
				'help'	=>	"This is shown on the news listing page as a summary of the articles contents.",
				'field'	=>	['name'=>'meta[excerpt]','type'=>'textarea','value'=>$_POST['meta']['excerpt']]
			]
	];
	
	//---------------//
	//-- Post Save --//
	//---------------//
	//-- Update vars: $meta & $data ONLY
	//-- Return FALSE creates error message
	function postc_save() {
		global $FIELD,$data,$meta,$zulu;
		
		if($zulu->dateEncode($meta['date'])<=0) {
			$zulu->notification_set("Please enter a valid 'publish date'. For example: ".date("d/m/Y"),2);
			return false;
		} else {
			$meta['date'] = $zulu->dateEncode($meta['date']);
			return true;
		}
	}
	
	
	//---------------//
	//-- Post Load --//
	//---------------//
	//-- Update vars: $_POST
	function postc_load() {
		global $FIELD,$zulu,$new;
		
		if($_POST['action']!='edit'&&!$new) {
			$_POST['meta']['date'] = $zulu->dateDecode($_POST['meta']['date']);
		}
		
		return;
	}
	
} else {
	
	//-- FRONTEND
	
}