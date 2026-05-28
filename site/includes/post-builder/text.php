<?php

class pb_text {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_text';
		$this->filename = 'text.php';
		$this->title = 'Text';
		$this->icon = 'font';
		$this->css_control = true;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"form-group\">
                    <label>Content</label>
                    ".$form_edit->input_html('htmlarea','pb_post['.$data['id'].'][content]',$data['content'],['ajax'=>(isset($config['ajax'])&&$config['ajax']?true:false)])."
                </div>
            </div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "";

        return $return;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $html = $data['content'];

        return $html;
    }

}

?>
