<?php

class pb_filetable {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_filetable';
		$this->filename = 'filetable.php';
		$this->title = 'File Table';
		$this->icon = 'file';
		$this->css_control = false;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $class_file, $class_user;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$file_data = $class_file->file_data(['type'=>'folder','user_id'=>$class_user->authorised->id]);
		$file_option = [];
		foreach($file_data as $fr) {
			$file_option[$fr['id']] = stripslashes($fr['name']);
		}

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-sm-12\">
                <div class=\"form-group\">
					<label>Manage your files...</label>
					<p><a href=\"".$this->zulu->link_page('file')."\" target=\"_blank\" class=\"btn btn-default\"><i class=\"fa fa-file\"></i> Manage Files</a></p>
				</div>
            </div>
			<div class=\"col-sm-12\">
				<div class=\"form-group\">
					<label>Select the Cloud Storage folder you want to show files from...</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][file_id]',$data['_meta']['file_id'],['custom'=>['data-meta'=>'1'],'option'=>$file_option])."
				</div>
			</div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "";

        return $return;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu, $class_file;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }
        $html = "";

        $file_id = $data['_meta']['file_id'];
        if($file_id>0) {
            $html = $class_file->embed_table(['public'=>true,'root_id'=>$file_id]);
        }

        return $html;
    }

}

?>
