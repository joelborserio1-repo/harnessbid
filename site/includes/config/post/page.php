<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: PAGE

if(MASTER_section=='admin') {

	//-- ADMIN
	if(PAGE_action==NULL) { //-- ACTION: MAIN PAGE	
	
	}
	
	if(PAGE_action=='edit') { //-- ACTION: EDIT PAGE
	
		//-- Slider Posts
		$slider_data = $class_post->post_data(['type'=>'slider','status'=>'published']);
		$slider_array[0] = "None";
		foreach($slider_data as $srow) {
			$slider_array[$srow['id']] = $srow['title'];	
		}
			
		//-- Fields
		$FIELD['sidebar'] = [
			'slider'=>
				[
					'label'=>'Slider',
					'help'=>"Select a default slider for this page.",
					'field'	=>	['name'=>'meta[slider_id]','type'=>'select','value'=>$_POST['meta']['slider_id'],'config'=>['option'=>$slider_array]]],
            'content_position' =>  [
                'label'     =>  'Content Position',
                'help'      =>  '',
                'field'	    =>	[
                    'name'  =>  'meta[content_position]',
                    'type'  =>  'select',
                    'value' =>  $_POST['meta']['content_position'],
                    'config'=>  [
                        'option'    =>  [
                            ''      =>  'Below the header',
                            'top'   =>  'Behind the header'
                        ]
                    ]
                ]
            ],
		];
		
	}
	
} else {
	
	//-- FRONTEND
	if($post_data['_meta']['slider_id']>0) {
		$slider_id = $post_data['_meta']['slider_id'];
		$slider_post_data = $class_post->post_data(['id'=>$slider_id]);
		$has_slider = true;
		$header_slider = $class_website->slider_build($slider_id);
	}
	
	//-- Google AMP (Incomplete)
	$post_image = $class_post->post_image();

	if($class_website->config->module->google_amp&&$zulu->template->google_amp) {
		$zulu->template->google_amp_schema = "<script type=\"application/ld+json\">
		  {
			\"@context\": \"http://schema.org\",
			\"@type\": \"WebPage\",
			\"name\": \"".$post->title."\",
			\"description\": \"".(trim(strip_tags($post->content))==NULL?$META_description:strip_tags($post->content))."\",
			\"datePublished\": \"".date('c',$post->stat_add)."\",
			\"lastReviewed\": \"".date('c',$post->stat_update)."\",
			".($post_image['file']!=NULL?"\"image\": [
			  \"".$post_image['file']."\"
			]":NULL)."
		  }
		</script>";	
	}
	
}