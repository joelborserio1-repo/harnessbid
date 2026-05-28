<?php

class pb_form {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_form';
		$this->filename = 'form.php';
		$this->title = 'Form';
		$this->icon = 'file-alt';
		$this->css_control = false;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $class_form_post;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$form_array = [];
		foreach($class_form_post->template as $key=>$val) {
			$form_array[$key] = $val['form']['title'];
		}
		$form_data = $class_form_post->form_data();
		foreach($form_data as $val) {
			$form_array[$val['id']] = stripslashes($val['title']);
		}

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-sm-12\">
                <div class=\"form-group\">
					<label>Select form...</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][form_embed]',$data['_meta']['form_embed'],['custom'=>['data-meta'=>'1'],'option'=>$form_array])."
				</div>
            </div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "";

        return $return;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu, $class_website;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }
        $html = "";

        $form_id = $data['_meta']['form_embed'];
        if(trim($form_id)!=NULL) {
            if(is_numeric($form_id)) {
                $html = $class_website->form_build('',$form_id,['ovr_user_id'=>true]);
            } else {
                $html = $class_website->form_build($form_id);
            }
        }

        return $html;
    }

}

?>
