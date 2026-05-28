<?php

class pb_image {

	function __construct($config=[]) {
        global $class_post, $zulu;

        $this->zulu = $zulu;
        $this->cpost = $class_post;

		$this->class_name = 'pb_image';
		$this->filename = 'image.php';
		$this->title = 'Image';
        $this->icon = 'image';
		$this->css_control = true;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu, $class_file;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }
        $timestamp = $zulu->serial(16);
        $queue_id = $zulu->serial(6);
        $class_file->form_data['image_file-'.$data['id']] = ['queue_id'=>$queue_id];
        $has_image = ($data['_meta']['image_file']!=null?true:false);

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"row\">
                    <div class=\"col-md-6\">
                        <div class=\"form-group\">
                            <label>Image</label>
                            <span class='image-upl-wrapper'>".$class_file->uploadifive_input('image_file-'.$data['id'])."</span>
                        </div>
                    </div>
                    <div class=\"col-md-6 image-container\">
                        <div class=\"image".(!$has_image?' hide':null)."\" data-path='".MAIN_rel."file/post/".$data['token']."/'>
                            <img src='".($has_image?MAIN_rel."file/post/".$data['token']."/".$data['_meta']['image_file']."?".time():null)."' alt='Content image' />
                        </div>
                    </div>
                    ".$form_edit->input_html('hidden','pb_post['.$data['id'].'][meta][image_file]',$data['_meta']['image_file'],['id'=>'input-image-file-'.$data['id']])."
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>Alternative Text</label>
                    ".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][image_alt]',$data['_meta']['image_alt'],['custom'=>['data-meta'=>'1']])."
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>Title Text</label>
                    ".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][image_title]',$data['_meta']['image_title'],['custom'=>['data-meta'=>'1']])."
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>Link URL</label>
                    ".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][image_link]',$data['_meta']['image_link'],['custom'=>['data-meta'=>'1']])."
                </div>
            </div>
            <div class=\"col-md-6\">
                <div class=\"form-group\">
                    <label>URL Opens</label>
                    ".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][image_link_location]',$data['_meta']['image_link_location'],['option'=>['_self'=>'In the Same Window','_blank'=>'In a New Tab'],'custom'=>['data-meta'=>'1']])."
                </div>
            </div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "
        $('#upl_image_file-".$data['id']."').uploadifive({
            'auto'			: true,
            'multi' 		: false,
            'queueSizeLimit': 1,
            'formData'      : {
                'action' 	: 'post_builder_image',
                'post_id' 	: '".$data['id']."',
                'file_name' : '',
                'path' 		: 'post/".$data['token']."/',
                'chk_time' 	: '".$timestamp."',
                'chk_serial': '".md5('ZuLu2000' . $timestamp)."',
            },
            'queueID'          : 'queue_".$queue_id."',
            'uploadScript'     : '".$class_file->vars->uploadifive->path_abs."upload.php',
            'onUploadComplete' : function(file, data) {
                var response = JSON.parse(data);
                pb_image_image_upload(".$data['id'].", response.file_name);
            }
        });";

        return $return;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        if($data['_meta']['image_file']) {
            $html =
        ($data['_meta']['image_link']!=NULL?"<a href='".$data['_meta']['image_link']."' target='".$data['_meta']['image_link_location']."'>":NULL)."
            <img src='".MAIN_rel."file/post/".$data['token']."/".$data['_meta']['image_file']."' alt='".(trim($data['_meta']['image_alt'])==NULL && trim($data['_meta']['image_title'])!=NULL?$data['_meta']['image_title']:$data['_meta']['image_alt'])."' title='".$data['_meta']['image_title']."' />
        ".($data['_meta']['image_link']!=NULL?"</a>":NULL);
        } else {
            $html = '';
        }

        return $html;
    }

}

?>
