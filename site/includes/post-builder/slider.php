<?php

class pb_slider {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_slider';
		$this->filename = 'slider.php';
		$this->title = 'Slider';
		$this->icon = 'laptop';
		$this->css_control = true;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$slider_post = $class_post->post_data(['status'=>'published','type'=>'slider','sort'=>'sort ASC, title ASC']);
		$slider_option = [];
		foreach($slider_post as $sp) {
			$slider_option[$sp['id']] = stripslashes($sp['title']);
		}

        $post_type_options = [
            'slider'    =>  'Slider'
        ];
        foreach($class_post->config->template as $key=>$val) {
            if(isset($val['config']['sliderable']) && $val['config']['sliderable']) {
                $post_type_options[$key] = $val['name'];
            }
        }
        if($data['_meta']['post_type'] == null) {
            $data['_meta']['post_type'] = 'slider';
        }

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-sm-6\">
                <div class=\"form-group\">
					<label>Slider type</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][post_type]',$data['_meta']['post_type'],['custom'=>['data-meta'=>'1'],'option'=>$post_type_options,'class'=>['slider-type']])."
				</div>
            </div>
            <div class=\"col-sm-6".($data['_meta']['post_type']!='slider'?' hide':null)."\" data-config='slider'>
                <div class=\"form-group\">
					<label>Slider to use</label>
					".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][slider_id]',$data['_meta']['slider_id'],['custom'=>['data-meta'=>'1'],'option'=>$slider_option])."
				</div>
            </div>
            <div class=\"col-sm-6".($data['_meta']['post_type']=='slider'?' hide':null)."\" data-config='post'>
                <div class=\"form-group\">
					<label>Maximum posts to load</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][post_limit]',$data['_meta']['post_limit'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'8']])."
				</div>
            </div>
            <div class=\"col-sm-12\">
                <h4>Slider Configuration</h4>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides shown</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][items]',$data['_meta']['items'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'1']])."
				</div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides that move</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][slideBy]',$data['_meta']['slideBy'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'1']])."
				</div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
                    <label>Loop</label>
                    ".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][loop]',$data['_meta']['loop'],['custom'=>['data-meta'=>'1'],'option'=>['yes'=>'Yes','no'=>'No']])."
                </div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
                    <label>Autoplay</label>
                    ".$form_edit->input_html('select','pb_post['.$data['id'].'][meta][autoplay]',$data['_meta']['autoplay'],['custom'=>['data-meta'=>'1'],'option'=>['yes'=>'Yes','no'=>'No']])."
                </div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
                    <label>Autoplay speed</label>
                    ".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][autoplayTimeout]',$data['_meta']['autoplayTimeout'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'5']])."
                </div>
            </div>
            <div class=\"col-sm-12\">
                <h4>Tablet Override</h4>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides shown</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][tablet_items]',$data['_meta']['tablet_items'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'default']])."
				</div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides that move</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][tablet_slideBy]',$data['_meta']['tablet_slideBy'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'default']])."
				</div>
            </div>
            <div class=\"col-sm-12\">
                <h4>Mobile Override</h4>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides shown</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][mobile_items]',$data['_meta']['mobile_items'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'default']])."
				</div>
            </div>
            <div class=\"col-sm-4\">
                <div class=\"form-group\">
					<label>Number of slides that move</label>
					".$form_edit->input_html('number','pb_post['.$data['id'].'][meta][mobile_slideBy]',$data['_meta']['mobile_slideBy'],['custom'=>['data-meta'=>'1','min'=>'1','placeholder'=>'default']])."
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
        $slider_build = false;
        $slider_id = 0;
        $slider_config = [
            'items'             =>  isset($data['_meta']['items']) && $data['_meta']['items'] > 0 ? $data['_meta']['items'] : 1,
            'slideBy'           =>  isset($data['_meta']['slideBy']) && $data['_meta']['slideBy'] > 0 ? $data['_meta']['slideBy'] : 1,
            'loop'              =>  isset($data['_meta']['loop']) && $data['_meta']['loop'] == 'no' ? false : true,
            'autoplay'          =>  isset($data['_meta']['autoplay']) && $data['_meta']['autoplay'] == 'no' ? false : true,
            'autoplayTimeout'   =>  (isset($data['_meta']['autoplayTimeout']) && $data['_meta']['autoplayTimeout'] > 0 ? $data['_meta']['autoplayTimeout'] : 5) * 1000,
        ];
        $slider_config['tablet'] = [
            'items'             =>  isset($data['_meta']['tablet_items']) && $data['_meta']['tablet_items'] > 0 ? $data['_meta']['tablet_items'] : $slider_config['items'],
            'slideBy'           =>  isset($data['_meta']['tablet_slideBy']) && $data['_meta']['tablet_slideBy'] > 0 ? $data['_meta']['tablet_slideBy'] : $slider_config['slideBy'],
        ];
        $slider_config['mobile'] = [
            'items'             =>  isset($data['_meta']['mobile_items']) && $data['_meta']['mobile_items'] > 0 ? $data['_meta']['mobile_items'] : $slider_config['tablet']['items'],
            'slideBy'           =>  isset($data['_meta']['mobile_slideBy']) && $data['_meta']['mobile_slideBy'] > 0 ? $data['_meta']['mobile_slideBy'] : $slider_config['tablet']['slideBy'],
        ];

        if($data['_meta']['post_type'] == 'slider') {
            if($data['_meta']['slider_id'] > 0) {
                $slider_build = true;
                $slider_id = $data['_meta']['slider_id'];
            }

        } elseif($data['_meta']['post_type'] != null) {

            $limit = isset($data['_meta']['post_limit']) && $data['_meta']['post_limit'] > 0 ? $data['_meta']['post_limit'] : 8;
            $post_config = ['type'=>$data['_meta']['post_type'], 'status'=>'published', 'limit'=>$limit];
            if(isset($class_post->config->template[$data['_meta']['post_type']]['config']['index_filter']) && is_array($class_post->config->template[$data['_meta']['post_type']]['config']['index_filter'])) {
                $post_config += $class_post->config->template[$data['_meta']['post_type']]['config']['index_filter'];
            }

            $post_data = $class_post->post_data($post_config);
            if(count($post_data) > 0) {
                $slider_build = true;
                $slides = [];

                foreach($post_data as $post_row) {
                    $image_data = $class_post->post_image($post_row['id']);
                    $image = $image_data['main'] != null ? $image_data['file'] : $class_post->config->placeholder;

                    if($data['_meta']['post_type'] == 'testimonial') {
                        $slides[] = "<div class=\"slide post-slide post-slide-".$data['_meta']['post_type']."\">
                            <div class=\"slide-content-wrap\">
                                <h4 class='testimonial-title'>".stripslashes($post_row['title'])."</h4>
                                <div class='testimonial-content-wrapper'>
                                    <div class='testimonial-content'>
                                        ".stripslashes($post_row['content'])."
                                    </div>
                                </div>
                                <h5 class='testimonial-author'>".stripslashes($post_row['_meta']['from'])."</h5>
                                ".($image_data['main']?"<div class='testimonial-image'><img src='".$image."' alt='".stripslashes($post_row['title'])."' class='' /></div>":null)."
                            </div>
                        </div>";

                    } elseif($data['_meta']['post_type'] == 'news') {
                        $slides[] = "<div class=\"slide post-slide post-slide-".$data['_meta']['post_type']."\">
                            <div class=\"post-item\">
                                <a href=\"".$post_row['_data']['url']."\">
                                    <div class=\"slide-content-wrap\">
                                        <div class=\"post-image date-label\">
                                            <p class='date'>".$zulu->time_history($post_row['_meta']['date'])."</p>
                                            <img src=\"".$zulu->thumb(FE_crm.$image,"w=600&h=400&zc=1")."\" alt=\"image of ".stripslashes($post_row['title'])."\" class=\"feature-image responsive\" />
                                        </div>
                                        <div class=\"post-body\">
                                            <h3>".stripslashes($post_row['title'])."</h3>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>";

                    } else {
                        $slides[] = "<div class=\"slide post-slide post-slide-".$data['_meta']['post_type']."\">
                            <div class=\"post-item\">
                                <a href=\"".$post_row['_data']['url']."\">
                                    <div class=\"slide-content-wrap\">
                                        <div class=\"post-image\">
                                            <img src=\"".$zulu->thumb(FE_crm.$image,"w=600&h=400&zc=1")."\" alt=\"image of ".stripslashes($post_row['title'])."\" class=\"feature-image responsive\" />
                                        </div>
                                        <div class=\"post-body\">
                                            <h3>".stripslashes($post_row['title'])."</h3>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>";
                    }
                }

                $slider_config['slides'] = $slides;
            }

        }

        if($slider_build) {
            $slider_result = $class_website->slider_build($slider_id, $slider_config);
            if($slider_result['success']) {
                $html = $slider_result['html'];
            }
        }

        return $html;
    }

}

?>
