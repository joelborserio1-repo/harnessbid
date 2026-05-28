<?php

class pb_product {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_product';
		$this->filename = 'product.php';
		$this->title = 'Product';
		$this->icon = 'cube';
		$this->css_control = false;
        $this->shop_only = true;
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
					<label>Select product to feature...</label>
					".$form_edit->input_html("input","pb_post[".$data['id']."][meta][product_name]",$data['_meta']['product_name'],array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-product_quick'),'placeholder'=>'Add product...','custom'=>array('data-meta'=>'1','data-sf'=>'sf_product_main','data-populate'=>'sf-product_title','autocomplete'=>'off')))."
					<div class=\"guessbox guessbox-product_quick\">
						<ul></ul>
					</div>
					".$form_edit->input_html("hidden","pb_post[".$data['id']."][meta][product_id]",$data['_meta']['product_id'],array('id'=>'sf-product_quick','class'=>array('sf-value'),'custom'=>['data-meta'=>'1']))."
				</div>
            </div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "";

        return $return;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu, $class_product;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

        $html = "<div class='product-single-wrapper'>".$class_product->product_block($data['_meta']['product_id'])."</div>";

        return $html;
    }

}

?>
