<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: TESTIMONIAL

if(MASTER_section=='admin') {

	//-- ADMIN
	$confform = new form;
	
	//-- Fields
	$FIELD['head'] = [
		'from'=>
			[
				'label'=>'Testimonial From',
				'required'=>true,
				'field'	=>	['name'=>'meta[from]','type'=>'input','value'=>$_POST['meta']['from']]],
		'scale'=>
			[
				'label'=>'Rating out of 5',
				'field'	=>	['name'=>'meta[rating]','type'=>'input','value'=>$_POST['meta']['rating']]],
	];
	
} else {
	
	//-- FRONTEND
	
}