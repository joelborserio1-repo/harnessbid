<?php

class pb_product_showcase {

	function __construct($config=[]) {
        global $zulu;

        $this->zulu = $zulu;

		$this->class_name = 'pb_product_showcase';
		$this->filename = 'product_showcase.php';
		$this->title = 'Product Showcase';
		$this->icon = 'cubes';
		$this->css_control = false;
        $this->shop_only = true;
	}

    function admin_edit_html($id=0, $data=[], $config=[]) {
        global $form_edit, $class_post, $zulu, $output;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }

		$has_manual = false;
        $manual_item_html = '';
		if(isset($data['_meta']['product_manual']) && $data['_meta']['product_manual'][0] != null) {
			$has_manual = true;
            $pm_meta = $zulu->meta_value('post', $data['id'], 'product_manual');
            if(isset($pm_meta['id'])) {
                $pm_meta = ['product_manual'=>[$pm_meta]];
            }
            foreach($pm_meta['product_manual'] as $product_manual) {
                $manual_item_html .= $this->admin_manual_item_html($product_manual['value'], $data['id']);
            }
		}
        $new = false;
		if(isset($config['new']) && $config['new']) {
			$new = true;
		}
		$output = [];
		$form_edit->categoryOptionForm();

        $return = [];
        $return['main_html'] = "
        <div class=\"row\">
            <div class=\"col-sm-12\">
                <div class=\"alert alert-info\"><i class=\"far fa-info-circle\"></i> Show products based on a filter or manually select which ones to show. If you have any manually selected then that's what will show, otherwise the filter will be used.</div>
				<div class=\"panel panel-default\">
					<div class=\"panel-heading\">
						<a class=\"".($has_manual&&!$new?'collapsed':null)."\" data-toggle=\"collapse\" href=\"#prod-con-filter\" aria-expanded=\"".($has_manual&&!$new?'false':'true')."\"><i class=\"fas fa-filter\"></i> Select product filter...</a>
					</div>
					<div id=\"prod-con-filter\" class=\"panel-collapse collapse".(!$has_manual||$new?' in':null)."\">
						<div class=\"panel-body\">
							<div class=\"row\">
                                <div class=\"col-md-12\">
                                    <div class=\"form-group\">
                                        <label>Filter by category...</label>
                                        ".$form_edit->input_html("select","pb_post[".$data['id']."][meta][filter_category_id]",$data['_meta']['filter_category_id'],['option'=>$output])."
                                    </div>
                                </div>
								<div class=\"col-md-6\">
									<div class=\"form-group\">
										<label>Filter by keyword...</label>
										".$form_edit->input_html("input","pb_post[".$data['id']."][meta][filter_keyword]",$data['_meta']['filter_keyword'],['placeholder'=>'Enter a search term to filter by...'])."
									</div>
								</div>
								<div class=\"col-md-6\">
									<div class=\"form-group\">
										<label>Maximum Results to Show</label>
										".$form_edit->input_html("number","pb_post[".$data['id']."][meta][filter_max]",$data['_meta']['filter_max'],['placeholder'=>'Leave blank for unlimited...','custom'=>['min'=>'0']])."
									</div>
								</div>
							</div>
                            <div class=\"row\">
								<div class=\"col-md-4\">
									<label>Homepage Featured Products Only</label>
									".$form_edit->input_html("select","pb_post[".$data['id']."][meta][filter_homepage_feature]",$data['_meta']['filter_homepage_feature'],['option'=>['0'=>'No','1'=>'Yes']])."
								</div>
                                <!--<div class=\"col-md-4\">
                                    <label>Special Products Only</label>
                                    ".$form_edit->input_html("select","pb_post[".$data['id']."][meta][filter_special]",$data['_meta']['filter_special'],['option'=>['0'=>'No','1'=>'Yes']])."
                                </div>-->
                                <div class=\"col-md-4\">
                                    <label>New Products Only</label>
                                    ".$form_edit->input_html("select","pb_post[".$data['id']."][meta][filter_new]",$data['_meta']['filter_new'],['option'=>['0'=>'No','1'=>'Yes']])."
                                </div>
                                <!--<div class=\"col-md-4\">
                                    <label>In Stock Only</label>
                                    ".$form_edit->input_html("select","pb_post[".$data['id']."][meta][filter_instock]",$data['_meta']['filter_instock'],['option'=>['0'=>'No','1'=>'Yes']])."
                                </div>-->
                            </div>
						</div>
					</div>
				</div><!-- END Table for Manual -->
				<div class=\"panel panel-default\">
					<div class=\"panel-heading\">
						<a class=\"".(!$has_manual&&!$new?'collapsed':null)."\" data-toggle=\"collapse\" href=\"#prod-con-manual\" aria-expanded=\"".(!$has_manual&&!$new?'false':'true')."\"><i class=\"fas fa-cubes\"></i> Manually select products</a>
					</div>
					<div id=\"prod-con-manual\" class=\"panel-collapse collapse".($has_manual||$new?' in':null)."\">
						<div class=\"panel-body\">
							<div class=\"form-group\">
								<label>Select product to feature...</label>
								".$form_edit->input_html("input","pb_post[".$data['id']."][meta][pshow_ajax_name]",'',array('autocomplete'=>false,'class'=>array('sf-input','typeahead','sf-input-product_quick'),'placeholder'=>'Add product...','custom'=>array('data-meta'=>'1','data-sf'=>'sf_product_main','data-populate'=>'sf-product_title','autocomplete'=>'off')))."
								<div class=\"guessbox guessbox-product_quick\">
									<ul></ul>
								</div>
								".$form_edit->input_html("hidden","pb_post[".$data['id']."][meta][pshow_product_id]",'',array('class'=>array('sf-value','pshow-sf-product_quick'),'custom'=>['data-meta'=>'1']))."
							</div>

							<div class=\"pshow-html-product-wrapper\">
								<!--<span class=\"opt opt-grey\">Loading...</span>-->
                                ".$form_edit->input_html("hidden","pb_post[".$data['id']."][meta][product_manual][]",'',['class'=>['pb-pshow-input-manual-product-blank'],'custom'=>['disabled'=>($has_manual?false:true)]])."
                                ".$manual_item_html."
							</div>
						</div>
					</div>
				</div><!-- END Table for Manual -->
            </div>
        </div>
        ";

        $return['extra_html'] = "";

        $return['js_code'] = "";

        return $return;
    }

    function admin_manual_item_html($product_id=0, $block_id=0) {
        global $form_edit, $class_product;

        $html = '';
        if($product_id > 0) {
            $product = $class_product->product_data(['id'=>$product_id,'field'=>['name','id','price']]);
            if($product['id'] > 0) {
                $html = "
                <span class=\"opt pshow-manual-item\" data-product=\"".$product['id']."\">
                    <span class='pshow-manual-item-title'>".stripslashes($product['name'])."</span>
                    <a href=\"#\" class=\"opt opt-danger pshow-bt-prod-del\"><i class=\"fas fa-times\"></i></a>
                    ".$form_edit->input_html("hidden","pb_post[".$block_id."][meta][product_manual][]",$product['id'])."
                </span>";
            }
        }

        return $html;
    }

    function fe_html($id=0, $data=[]) {
        global $class_post, $zulu, $class_product, $class_user;

        if($data['id'] <= 0 && $id > 0) {
            $data = $class_post->post_data(['id'=>$id]);
        }
        $html = "";

        $block_id = $data['id'];
        $product_manual_array = $zulu->table_data('post_meta',0,['where'=>["field = 'product_manual'","identifier = '".$block_id."'"]]);
		$pblock = [];

		if(count($product_manual_array)>0) { //-- MANUAL product
            foreach($product_manual_array as $ma) {
                if(isset($meta_cache[$ma['id']]) || $ma['value'] <= 0)
                    continue;
                $meta_cache[$ma['id']] = $ma['id'];
                $pblock[] = "<li>".$class_product->product_block($ma['value'])."</li>";
            }
        }

		if(count($pblock) <= 0) {
			//-- FILTERED product
			$instock = false;
            $limit = 100;
            $join = "product_meta pm1 ON product.id = pm1.identifier";
			$sort = 'sort ASC, name ASC';
            if($data['_meta']['filter_category_id']>0) {
                $class_product->category_children($data['_meta']['filter_category_id']);
                $child_category_array = $class_product->category_child;
                $child_category_array[$category_data['id']] = $category_data['id'];
                if(count($child_category_array)>0) {
                    $sql_where[] = "pm1.field = 'category'";
                    $sql_where[] = "pm1.value IN(".implode(",",array_filter($child_category_array)).")";
                }
            }
            if($data['_meta']['filter_new']>0) {
                //$sql_where[] = "new = 1";
				$sort = "stat_add ASC";
            }
            if($data['_meta']['filter_special']>0) {
                $sql_where[] = "price_special > 0";
            }
            if($data['_meta']['filter_instock']>0) {
                $instock = true;
            }
            if($data['_meta']['filter_max']>0) {
                $limit = $data['_meta']['filter_max'];
            }
            if($data['_meta']['filter_keyword']!=NULL) {
                $searchpre = $data['_meta']['filter_keyword'];
                $search = $data['_meta']['filter_keyword'];
                $sql_where[] = "((name LIKE '%".strtolower($search)."%' OR sku LIKE '%".strtolower($search)."%') OR MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) OR MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE))";
                $rel_field[] = "((name LIKE '%".strtolower($search)."%') + (sku LIKE '%".strtolower($search)."%')) AS rel1";
                $rel_field[] = "MATCH(name,sku) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel2";
                $rel_field[] = "MATCH(description) AGAINST ('".strtolower($search)."' IN BOOLEAN MODE) AS rel3";
            }
			if($data['_meta']['filter_homepage_feature']>0) {
                $sql_where[] = "EXISTS(select * from product_listing pl where product.listing_id=pl.id and add_home=1)";
            }

            //Final settings
            $sql_where[] = "status = 1";
			$sql_where[] = "live = 1";
            $sql_where[] = "hide = 0";
            $sql_where[] = "user_id = '".$class_user->authorised->id."'";
            $sql_where[] = "type = 'product'";
            $sql_where[] = "sys = '0'";
            $sql_where[] = "(type_variant = '1' OR type_variant = '0')";

            //Query FULL
            $row_PRODF = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$join,'sort'=>$sort,'field'=>['*,product.id AS id'],'where'=>$sql_where,'group'=>'product.id','test'=>false,'limit'=>$limit]);

            foreach($row_PRODF as $row_PROD) {
                $class_product->vars->data = $row_PROD;
                if($instock) {
                    $stock_level = $zulu->meta_value('product',$row_PROD['id'],'stock');
                    if($stock_level['value']<=0) {
                        continue;
                    }
                }
                $pblock[] = "<li>".$class_product->product_block()."</li>";
            }
		}

        $html = "<ul class=\"product-box row4 ls-master\">".implode(PHP_EOL,$pblock)."</ul>";

        return $html;
    }

}

?>
