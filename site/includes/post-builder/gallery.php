<?php

class pb_gallery {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_gallery';
		$this->filename = 'gallery.php';
		$this->title = 'Gallery';
		$this->icon = 'images';
		$this->css_control = true;

		$this->config = new stdClass();
		$this->config->row_options = [
			0	=>	"Default",
			1	=>	"1",
			2	=>	"2",
			3	=>	"3",
			4	=>	"4",
			5	=>	"5",
			6	=>	"6",
		];
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$gallery_option = [0=>"None"];
		$gallery_data = $class_post->post_data(['status'=>'published','type'=>'gallery','sort'=>'sort ASC, title ASC']);
		foreach($gallery_data as $val) {
			$gallery_option[$val['id']] = stripslashes($val['title']);
		}


        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-sm-12\">
				<div class=\"form-group\">
					<label>Select gallery...</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][gallery_id]',$data['_meta']['gallery_id'],['custom'=>['data-meta'=>'1'],'option'=>$gallery_option])."
				</div>
			</div>
			<div class=\"col-sm-4\">
				<div class=\"form-group\">
					<label>Images per row</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][gallery_row]',$data['_meta']['gallery_row'],['custom'=>['data-meta'=>'1'],'option'=>$this->config->row_options])."
				</div>
			</div>
			<div class=\"col-sm-4\">
				<div class=\"form-group\">
					<label>Crop Images</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][gallery_crop]',$data['_meta']['gallery_crop'],['custom'=>['data-meta'=>'1'],'option'=>[0=>'No',1=>'Yes']])."
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
        $html = "";

        $gallery_id = $data['_meta']['gallery_id'];
        if($gallery_id>0) {
            $gallery = $class_post->post_data(['id'=>$gallery_id]);
            $gcon = [
                'row'	=>	$data['_meta']['gallery_row'],
                'crop'	=>	$data['_meta']['gallery_crop'],
            ];
            $html = $class_post->post_content($gallery,['display'=>'gallery','gallery'=>$gcon]);
        }

        return $html;
    }

}

?>
