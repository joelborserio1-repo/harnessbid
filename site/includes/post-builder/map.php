<?php

class pb_map {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_map';
		$this->filename = 'map.php';
		$this->title = 'Map';
		$this->icon = 'map';
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
					<label>Map Location</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][map_addr]',$data['_meta']['map_addr'],['custom'=>['data-meta'=>'1']])."
				</div>
            </div>
			<div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Zoom Level</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][map_zoom]',$data['_meta']['map_zoom'],['custom'=>['data-meta'=>'1','min'=>'0'],'placeholder'=>'Default is 13'])."
				</div>
            </div>
			<div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Width</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][map_width]',$data['_meta']['map_width'],['custom'=>['data-meta'=>'1'],'placeholder'=>'Default is 100%'])."
				</div>
            </div>
			<div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Height</label>
					".$form_edit->input_html('input','pb_post['.$data['id'].'][meta][map_height]',$data['_meta']['map_height'],['custom'=>['data-meta'=>'1'],'placeholder'=>'Default is 400px'])."
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

        $serial = $data['id'];
        $w = ($data['_meta']['map_width']!=NULL?$data['_meta']['map_width']:'100%');
        $h = ($data['_meta']['map_height']!=NULL?$data['_meta']['map_height']:'400px');
        $z = ($data['_meta']['map_zoom']!=NULL?$data['_meta']['map_zoom']:13);
        $params = [
            "key=".GOOGLE_MAPS_API_KEY,
            "q=".$data['_meta']['map_addr'],
            "zoom=".$z,
        ];

        $html = "
        <div class=\"map-wrapper\" id=\"map_".$serial."\">
            <iframe src='https://www.google.com/maps/embed/v1/place?".implode('&',$params)."' width='".$w."' height='".$h."'></iframe>
        </div>";

        return $html;
    }

}

?>
