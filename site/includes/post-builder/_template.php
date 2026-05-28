<?php

class pb__template {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb__template';
		$this->filename = '_template.php';
		$this->title = 'Template';
		$this->icon = '';
		$this->css_control = false;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">

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
        $html = "";



        return $html;
    }

}

?>
