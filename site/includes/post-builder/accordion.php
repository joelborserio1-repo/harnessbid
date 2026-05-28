<?php

class pb_accordion {

	function __construct($config=[]) {
        global $class_post, $zulu;

        $this->zulu = $zulu;
        $this->cpost = $class_post;

		$this->class_name = 'pb_accordion';
		$this->filename = 'accordion.php';
		$this->title = 'Accordion';
		$this->icon = 'bars';
		$this->css_control = true;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $table_column = [
            array("Title",array('class'=>array(''))),
            array("Actions",array('class'=>array('right')))
        ];
        $table_row = [["header" => true, "class" => "", "content" => $table_column]];

        $status = (isset($config['status'])?$config['status']:'published');
        $item_data = $class_post->post_data(['type'=>'post_builder_block_item','parent_id'=>$data['id'],'status'=>$status,'sort'=>'sort ASC, id ASC']);
        $item_count = count($item_data);
        $item_fields = "";

        foreach($item_data as $item_row) {
            $table_row[] = array("content" => [
                array("<span class='pb-item-table-title'>".stripslashes($item_row['title'])."</span>"),
                array("<a href=\"#\" class=\"btn btn-primary btn-xs pb-item-edit-row\" title='Edit Item' data-id='".$item_row['id']."'><i class=\"fas fa-edit\"></i> Edit</a> <a href=\"#\" class=\"btn btn-danger btn-xs pb-item-remove-row\" title='Remove Item'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w100')))
            ], 'data'=>['id'=>$item_row['id']], 'class'=>($item_row['_meta']['hide']?'inactive':''));
            $item_fields .= $this->admin_item_edit_html($item_row['id'], $item_row);
        }

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12\">
               <div class='pb-item-table-container' data-type='accordion'>
                    <div class='row-container'>".$zulu->table_render($table_row,0,array('class'=>'accordion-options'.($item_count<=0?' hide':null),'js_table'=>false,'data_table'=>false))."</div>
                    <div class=\"row\">
                        <p class='text-center'><button type='button' class='btn btn-primary pb-item-row-add".($item_count>0?' btn-xs':null)."' title='Add an item'><span class='fas fa-plus-circle'></span> Add New Accordion Item</button></p>
                    </div>
                </div>
            </div>
        </div>
        ";

        $return['extra_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12 pb-item-edit-container\" data-type='accordion'>
                ".$item_fields."
            </div>
        </div>
        ";

        $return['js_code'] = "
        pb_item_accordion_init_sortable();
        ";

        return $return;
    }

    function admin_item_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $item_fields = "
        <div class='pb-item-block' data-id='".$data['id']."' data-save='0'".(isset($config['new'])&&$config['new']?" data-new='1'":null).">
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='form-group'>
                        <label>Title</label>
                        ".$form_edit->input_html("input","pb_post[".$data['id']."][title]",$data['title'],['placeholder'=>(isset($config['new'])&&$config['new']?"New Item":null),'class'=>['pb-item-title']])."
                    </div>
                    <div class='form-group'>
                        <label>Content</label>
                        ".$form_edit->input_html("htmlarea","pb_post[".$data['id']."][content]",$data['content'],['id'=>"pb-item-content-".$data['id'],'ajax'=>(isset($config['ajax'])&&$config['ajax']?true:false)])."
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
        global $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }
        $html = "";

        $items = $class_post->post_data(['type'=>'post_builder_block_item','parent_id'=>$data['id'],'status'=>'published']);
        foreach($items as $item) {
            if($item['_meta']['hide']) continue;
            $html .= "
            <div class=\"post-wrap post-type-accordion box-container".($item['_meta']['css_class']?' '.$item['_meta']['css_class']:NULL)."\"".($item['_meta']['css_style']!=NULL?" style='".$item['_meta']['css_style']."'":NULL).($item['_meta']['css_id']!=NULL?" id='".$item['_meta']['css_id']."'":NULL).">
                <div class=\"box accordion\" itemscope itemtype=\"http://schema.org/Question\">
                    <h3 itemprop=\"name\">".stripslashes($item['title'])."</h3>
                    <div class=\"accordion-inner\" itemprop=\"text\">
                        ".stripslashes($item['content'])."
                    </div>
                </div>
            </div>
            ";
        }
        $zulu->template->js_code['accordion_base'] = "
        $('.accordion .accordion-inner').hide();
        ";
        $zulu->template->jquery_code['accordion_base'] = "
		$(document).on('click', '.accordion', function() {
            $(this).find('.accordion-inner').toggle();
            return false;
        });
        ";

        return $html;
    }

}

?>
