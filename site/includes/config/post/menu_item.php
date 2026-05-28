<?php
//(C)2016 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//POST CONFIG: MENU ITEM

if(MASTER_section=='admin') {

	//-- ADMIN
	if(PAGE_action==NULL) { //-- ACTION: MAIN PAGE	
	
	}
	
	if(PAGE_action=='edit') { //-- ACTION: EDIT PAGE
			
		//-- Fields
		$FIELD['sidebar'] = [
			'class'=>
				[
					'label'=>'Icon',
					'help'=>"Enter icon name from FontAwesome icons. Suffix after 'fa-'.",
					'field'	=>	['name'=>'meta[icon]','type'=>'input','value'=>$_POST['meta']['icon'],'config'=>['placeholder'=>"Example: thumbs-up, check"]]],
		];
		$FIELD['head'] = [
			'title'=>
				[
					'class'=>['col-sm-12'],
					'label'=>'Link Title',
					'help'=>"This is the text that will appear in the navigation item.",
					'field'	=>	['name'=>'extra[title]','type'=>'input','value'=>$_POST['title'],'config'=>['id'=>'input-title']]],
			'object'=>
				[
					'label'=>'Link Type',
					'help'=>"You can automatically link to an object on the website or specify a custom URL.",
					'field'	=>	['name'=>'meta[object]','type'=>'select','value'=>$_POST['meta']['object'],'config'=>['id'=>'input-type','placeholder'=>"Example: thumbs-up, check",'option'=>['custom'=>'Custom URL']+$form_edit->make_array($class_website_menu->object_types->options,['label'=>'title'])]]],
			'object_id'=>
				[
					'class'=>['col-sm-6'],
					'label'=>'Link To',
					'help'=>"Select the object on the website to link to.",
					'field'	=>	['name'=>'meta[object_id]','type'=>'select','value'=>$_POST['meta']['object_id'],'config'=>['id'=>'input-object','option'=>'-']]],
				'redirect_link'=>
				[
					'class'=>['col-sm-6'],
					'label'=>'Custom URL',
					'help'=>"Enter the full URL if an external site or absolute path from the root directory.",
					'field'	=>	['name'=>'meta[redirect_link]','type'=>'input','value'=>$_POST['meta']['redirect_link'],'config'=>['id'=>'input-custom','placeholder'=>"http://example.com"]]],
				'redirect_location'=>
				[
					'class'=>['col-sm-3'],
					'label'=>'Open link in',
					'help'=>"You can select how the link reacts once clicked; for example, a new window can be opened from the link.",
					'field'	=>	['name'=>'meta[redirect_location]','type'=>'select','value'=>$_POST['meta']['redirect_location'],'config'=>['option'=>['_self'=>'Same Tab','_blank'=>'New Tab','_parent'=>'Parent Tab']]]],
		];
		$FIELD['body'] = [];
		
		//-- Field toggles
		$zulu->template->post['field_title'] = false;
		$zulu->template->post['panel_body'] = false;
		
		//-- Form HTML
		$zulu->template->post['html_body'] = $form_edit->input_html("hidden","extra[parent_id]",($_GET['field']['parent_id']>0?$_GET['field']['parent_id']:$_POST['parent_id']));
		$zulu->template->js_code[] = "
			var page_load = true;
			var object_id = ".($_POST['meta']['object_id']>0?$_POST['meta']['object_id']:'0').";
			var extra_url = '';
			
			$(document).on('change','#input-type',function() {
				if($(this).val()=='custom') {
					$('#input-object').parent().parent().hide(500);
					$('#input-custom').parent().parent().show(500);
					$('#input-title').attr('placeholder','Required - For example: Home, About, Contact Us...');
				} else {
					$('#input-object').parent().parent().show(500);
					$('#input-custom').parent().parent().hide(500);
					$('#input-title').attr('placeholder','Optional - For example: Home, About, Contact Us...');
					
					if(page_load) {
						extra_url = '&Selected=' + object_id;	
						page_load = false;
					}
					
					$(\"#input-object\").html('');
					var object = $(this).val();
					$.get(\"".MAIN_rel."includes/page/ajax.php?Ajax=menu_item_object&Object=\" + object + extra_url,function(data) {
						$(\"#input-object\").html(data);
						console.log(data);
					});
				}
				return false;
			});
			$(\"#input-type\").trigger('change');
		";
	}
	
} else {
	
	//-- FRONTEND
	
}