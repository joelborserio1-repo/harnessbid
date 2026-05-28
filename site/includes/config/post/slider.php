<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: SLIDER

if(MASTER_section=='admin') {

	//-- ADMIN
	if(PAGE_action==NULL) { //-- ACTION: MAIN PAGE	
	
	}
	
	if(PAGE_action=='edit') { //-- ACTION: EDIT PAGE
			
		//-- Fields
		$FIELD['head'] = [
			'display_type'=>
				[
					'label'=>'Display Format',
					'help'=>"If you have static images, then select 'Image slider'. If you want content overlaid on background images, select 'Content slider'.",
					'field'	=>	['name'=>'meta[display_type]','type'=>'select','value'=>$_POST['meta']['display_type'],'config'=>['option'=>['image'=>'Image Slider','content'=>'Content Slider']]]],
		];
		
	}
	
}