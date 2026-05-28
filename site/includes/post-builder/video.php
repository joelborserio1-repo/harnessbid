<?php

class pb_video {

	function __construct($config=[]) {
        global $class_post, $zulu;

        $this->zulu = $zulu;
        $this->cpost = $class_post;

		$this->class_name = 'pb_video';
		$this->filename = 'video.php';
		$this->title = 'Video';
		$this->icon = 'play';
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
			<div class=\"col-sm-12\">
				<div class=\"form-group\">
					<label>YouTube&reg; or Vimeo&reg; URL</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][video_url]',$data['_meta']['video_url'],['custom'=>['data-meta'=>'1']])."
				</div>
			</div>
			<div class=\"col-sm-4\">
				<div class=\"form-group\">
					<label>Auto-play on page load?</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][autoplay]',$data['_meta']['autoplay'],['custom'=>['data-meta'=>'1'],'option'=>[0=>'Off',1=>'On']])."
				</div>
			</div>
			<div class=\"col-sm-4\">
				<div class=\"form-group\">
					<label>Width</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][video_width]',$data['_meta']['video_width'],['custom'=>['data-meta'=>'1'],'placeholder'=>'Default is 100%'])."
				</div>
			</div>
			<div class=\"col-sm-4\">
				<div class=\"form-group\">
					<label>Height</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][video_height]',$data['_meta']['video_height'],['custom'=>['data-meta'=>'1'],'placeholder'=>'Default is 350px'])."
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

        $video_url = $zulu->video_url($data['_meta']['video_url'],['autoplay'=>($data['_meta']['autoplay']>0?true:false)]);
        $w = ($data['_meta']['video_width']!=NULL?$data['_meta']['video_width']:'100%');
        $h = ($data['_meta']['video_height']!=NULL?$data['_meta']['video_height']:'350px');
        if(strstr($video_url,'youtube')) {
            $html = "<iframe width=\"".$w."\" height=\"".$h."\" src=\"".$video_url."\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>";
        } else {
            $html = "<iframe src=\"".$video_url."\" width=\"".$w."\" height=\"".$h."\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
        }

        return $html;
    }

}

?>
