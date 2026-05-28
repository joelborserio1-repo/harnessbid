<?php

class pb_button {

	function __construct($config=[]) {
        global $class_post, $zulu, $form_edit, $class_website_menu;

        $this->zulu = $zulu;
        $this->cpost = $class_post;

		$this->class_name = 'pb_button';
		$this->filename = 'button.php';
		$this->title = 'Buttons';
		$this->icon = 'rocket';
		$this->css_control = true;

		$this->config = new stdClass();
		$this->config->theme_option = [
			''          =>  'Default',
			'variant-1' =>  'Variant 1',
			'variant-2' =>  'Variant 2',
			'variant-3' =>  'Variant 3',
			'variant-4' =>  'Variant 4',
			'variant-5' =>  'Variant 5',
		];
		$this->config->target_option = [
			'_self'     =>  'Same Tab',
			'_blank'    =>  'New Tab',
			'_parent'   =>  'Parent Tab'
		];
		$this->config->menu_type_option = ['custom'=>'Custom URL']+$form_edit->make_array($class_website_menu->object_types->options,['label'=>'title']);
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$table_column = [
			array("Title",array('class'=>array(''))),
			array("Link",array('class'=>array(''))),
			array("Actions",array('class'=>array('right')))
		];
		$table_row = [["header" => true, "class" => "", "content" => $table_column]];

		$class_website_menu = new website_menu;
        $status = (isset($config['status'])?$config['status']:'published');
		$item_data = $class_post->post_data(['type'=>'post_builder_block_item','parent_id'=>$data['id'],'status'=>$status,'sort'=>'sort ASC, id ASC']);
		$item_count = count($item_data);
		$item_fields = "";

        foreach($item_data as $item_row) {
			$class_website_menu->data->row = [
				'_meta'	=>	[
					'object'		=>	$item_row['_meta']['object'],
					'object_id'		=>	$item_row['_meta']['object_id'],
					'redirect_link'	=>	$item_row['_meta']['redirect_link'],
				]
			];
            $title = '';
            if($item_row['title'] != NULL) {
                $title = stripslashes($item_row['title']);
            } elseif($item_row['_meta']['object'] != 'custom') {
                $title = $class_website_menu->menu_title(0);
            }
			$table_row[] = array("content" => [
				array("<span class='pb-item-table-title'>".$title."</span>"),
				array("<span class='pb-item-table-link'>".($item_row['_meta']['object']=='custom'?$item_row['_meta']['redirect_link']:$class_website_menu->menu_title(0))."</span>"),
				array("<a href=\"#\" class=\"btn btn-primary btn-xs pb-item-edit-row\" title='Edit Item' data-id='".$item_row['id']."'><i class=\"fas fa-edit\"></i> Edit</a> <a href=\"#\" class=\"btn btn-danger btn-xs pb-item-remove-row\" title='Remove Item'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w100')))
			], 'data'=>['id'=>$item_row['id']], 'class'=>($item_row['_meta']['hide']?'inactive':''));

            $item_fields .= $this->admin_item_edit_html($item_row['id'], $item_row);
        }

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12\">
               <div class='pb-item-table-container' data-type='button'>
                    <div class='row-container'>".$zulu->table_render($table_row,0,array('class'=>'button-options'.($item_count<=0?' hide':null),'js_table'=>false,'data_table'=>false))."</div>
                    <div class=\"row\">
                        <p class='text-center'><button type='button' class='btn btn-primary pb-item-row-add".($item_count>0?' btn-xs':null)."' title='Add an item'><span class='fas fa-plus-circle'></span> Add A New Button</button></p>
                    </div>
                </div>
            </div>
        </div>
        ";

        $return['extra_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12 pb-item-edit-container\" data-type='button'>
                ".$item_fields."
            </div>
        </div>
        ";

        $return['js_code'] = "
        pb_item_button_init_sortable();
		$(\"#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id='".$data['id']."'][data-type='button'] .pb-item-edit-container .pb-button-input-type\").trigger('change');
        ";

        return $return;
    }

    function admin_item_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $new = false;
		if(isset($config['new']) && $config['new']) {
			$new = true;
		}

        $item_fields = "
        <div class='pb-item-block' data-id='".$data['id']."' data-save='0'".(isset($config['new'])&&$config['new']?" data-new='1'":null).">
            <div class='row'>
                    <div class='col-sm-9'>
						<div class='form-group'>
							<label>Title</label>
							".$form_edit->input_html("input","pb_post[".$data['id']."][title]",$data['title'],['placeholder'=>'New Button','class'=>['pb-button-input-title']])."
						</div>
					</div>
					<div class='col-sm-3'>
						<div class='form-group'>
							<label>Icon ".$form_edit->icon_help("Enter icon name from FontAwesome icons. Suffix after 'fa-'.")."</label>
							".$form_edit->input_html("input","pb_post[".$data['id']."][meta][icon]",$data['_meta']['icon'],['class'=>['pb-button-input-icon']])."
						</div>
					</div>
					<div class=\"col-md-4\">
						<div class=\"form-group\">
							<label>Theme</label>
							".$form_edit->input_html('select',"pb_post[".$data['id']."][meta][theme]",$data['_meta']['theme'],['class'=>['pb-button-input-theme'],'option'=>$this->config->theme_option])."
						</div>
					</div>
					<div class=\"col-md-4\">
						<div class=\"form-group\">
							<label>Link Type</label>
							".$form_edit->input_html('select',"pb_post[".$data['id']."][meta][object]",$data['_meta']['object'],['class'=>['pb-button-input-type'],'option'=>$this->config->menu_type_option])."
						</div>
					</div>
					<div class=\"col-md-4\">
						<div class=\"form-group\">
							<label>Link Item</label>
							".$form_edit->input_html('select',"pb_post[".$data['id']."][meta][object_id]",$data['_meta']['object_id'],['class'=>['pb-button-input-object-id'],'custom'=>['data-object-type'=>$data['_meta']['object'],'data-object-id'=>$data['_meta']['object_id']],'style'=>($new?["display:none;"]:null)])."
							".$form_edit->input_html('input',"pb_post[".$data['id']."][meta][redirect_link]",$data['_meta']['redirect_link'],['class'=>['pb-button-input-custom'],'placeholder'=>'http://example.com'])."
						</div>
					</div>
					<div class=\"col-md-4\">
						<div class=\"form-group\">
							<label>Link Target</label>
							".$form_edit->input_html('select',"pb_post[".$data['id']."][meta][redirect_location]",$data['_meta']['redirect_location'],['class'=>['pb-button-input-target'],'option'=>$this->config->target_option])."
						</div>
					</div>
                </div>
                <div class='row'>
                    <div class=\"col-sm-6\">
                        <div class=\"form-group\">
                            <label>Custom CSS Class</label>
                            ".$form_edit->input_html('input',"pb_post[".$data['id']."][meta][css_class]",$data['_meta']['css_class'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class=\"col-sm-6\">
                        <div class=\"form-group\">
                            <label>Custom CSS ID</label>
                            ".$form_edit->input_html('input',"pb_post[".$data['id']."][meta][css_id]",$data['_meta']['css_id'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                            <label>Custom Style</label>
                            ".$form_edit->input_html('input',"pb_post[".$data['id']."][meta][css_style]",$data['_meta']['css_style'])."
                        </div>
                    </div>
                    <div class=\"col-md-4\">
                        <div class=\"form-group\">
                            <label>Hide</label>
                            ".$form_edit->input_html('select',"pb_post[".$data['id']."][meta][hide]",$data['_meta']['hide'],['option'=>['0'=>'No','1'=>'Yes'],'class'=>['pb-item-hide']])."
                        </div>
                    </div>
                </div>
            ".$form_edit->input_html("hidden","pb_post[".$data['id']."][sort]",$data['sort'],['class'=>['pb-item-sort']])."
            ".$form_edit->input_html('hidden','pb_post['.$data['id'].'][remove]','0',['class'=>['pb-item-input-remove']])."
        </div>
        ";

        return $item_fields;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu, $class_website_menu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $items = $class_post->post_data(['type'=>'post_builder_block_item','parent_id'=>$data['id'],'status'=>'published']);
        $buttons = [];
        foreach($items as $item) {
            if($item['_meta']['hide']) continue;
            $class_website_menu->data->row = [
                '_meta'	=>	[
                    'object'		=>	$item['_meta']['object'],
                    'object_id'		=>	$item['_meta']['object_id'],
                    'redirect_link'	=>	$item['_meta']['redirect_link'],
                ]
            ];
            $title = '';
            if($item['title'] != NULL) {
                $title = " ".stripslashes($item['title']);
            } elseif($item['_meta']['object'] != null) {
                $title = " ".$class_website_menu->menu_title(0);
            }

            $icon = null;
            if($item['_meta']['icon'] != null) {
                $icon_parts = explode(' ',$item['_meta']['icon']);
                if(count($icon_parts) <= 1) {
                    $item['_meta']['icon'] = "fas fa-".$item['_meta']['icon'];
                }
                $icon = "<i class='".$item['_meta']['icon']."'></i>";
            }

            $buttons[] = "
            <a href=\"".$class_website_menu->menu_link(0)."\" class=\"button".($item['_meta']['theme']!=NULL?" btn-".$item['_meta']['theme']:NULL).($item['_meta']['css_class']?' '.$item['_meta']['css_class']:NULL)."\"".($item['_meta']['css_style']!=NULL?" style='".$item['_meta']['css_style']."'":NULL).($item['_meta']['css_id']!=NULL?" id='".$item['_meta']['css_id']."'":NULL).($item['_meta']['redirect_location']!=NULL?" target=\"".$item['_meta']['redirect_location']."\"":NULL).">
                ".$icon.$title."
            </a>";
        }
        $html = "<div class='button-wrapper'>".implode(" ",$buttons)."</div>";

        return $html;
    }

}

?>
