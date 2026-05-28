<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: PRODUCTS
class product {

	public $SQL_table_product = 'product';
	public $SQL_table_product_meta = 'product_meta';
	public $SQL_table_product_attribute = 'product_attribute';
	public $SQL_table_product_stock = 'product_stock';
	public $SQL_table_product_location = 'product_stock_location';
	public $SQL_table_product_option = 'product_option';
	public $SQL_table_product_feature = 'product_feature';
	public $SQL_table_product_special = 'product_special';
	public $SQL_table_product_special_item = 'product_special_item';
    public $SQL_table_product_price_break = 'product_price_break';

	function __construct($config=[]) {
		global $db,$zulu,$class_user,$class_setting;
		$this->db = $db;
		$this->zulu = $zulu;
		$this->config = new stdClass();

		if(isset($_GET['ProductRoot']) && $_GET['ProductRoot']!=0) {
			$this->root_id = $_GET['ProductRoot'];
		} else {
			$this->root_id = 0;
		}

		//Image Root
		$this->image_fold = 'file/product/';
		$this->image_rel = MAIN_rel.$this->image_fold;
		$this->image_path = MAIN_path.$this->image_fold;
		$this->ticket_id = $class_setting->data['ticket_id'];
		$this->schedule_id = $class_setting->data['schedule_id'];

		$this->config->display_mode = ['select'=>"Selectors",'list'=>"List Options",'bulk'=>"Bulk Table"];
		$this->config->attr_input = ['select'=>"Dropdown Box",'radio'=>"Radio Buttons",'checkbox'=>"Checkboxes"];
		$this->config->attr_input_price = ['checkbox'];

        $this->config->view_modes = [
            'feature'   =>  [
                'title'             =>  'Featured',
                'meta_title'        =>  'Featured Products',
                'sql_where'         =>  "feature = 1",
                'title_default'     =>  'Featured Products',
                'title_category'    =>  'Featured in ',
                'title_search'      =>  ' in Featured',
                'icon'              =>  'fas fa-star',
            ],
            'special'   =>  [
                'title'             =>  'Specials',
                'meta_title'        =>  'Specials',
                'sql_where'         =>  "(price_special > 0 OR special = 1 OR EXISTS (SELECT * FROM product_special ps INNER JOIN product_special_item psi ON ps.id=psi.product_special_id WHERE ps.status=1 AND psi.product_id=product.id AND ps.date_from <= ".time()." AND (ps.date_to+86400) > ".time().") OR EXISTS (SELECT * FROM product p2 WHERE p2.parent_id=product.id AND p2.hide=0 AND p2.status=1 AND p2.type='product' AND p2.type_variant=2 AND EXISTS(SELECT * FROM product_special ps INNER JOIN product_special_item psi ON ps.id=psi.product_special_id WHERE psi.product_id=p2.id AND ps.status=1 AND ps.date_from <= ".time()." AND (ps.date_to+86400) > ".time().")))",
                'title_default'     =>  'Products on Special',
                'title_category'    =>  'Specials in ',
                'title_search'      =>  ' in Specials',
                'icon'              =>  'fas fa-certificate',
            ],
            'new'       =>  [
                'title'             =>  'New Products',
                'meta_title'        =>  'New Products',
                'sql_where'         =>  "new = 1",
                'title_default'     =>  'New Products',
                'title_category'    =>  'New Products in ',
                'title_search'      =>  ' in New Products',
                'icon'              =>  'far fa-calendar-alt',
            ],
            'clearance' =>  [
                'title'             =>  'Clearance',
                'meta_title'        =>  'Clearance Deals',
                'sql_where'         =>  "EXISTS (SELECT * FROM product_meta WHERE product.id=product_meta.identifier AND product_meta.field='clearance' AND product_meta.value = '1')",
                'title_default'     =>  'Clearance Deals',
                'title_category'    =>  'Clearance in ',
                'title_search'      =>  ' in Clearance',
                'icon'              =>  'fas fa-fire',
            ],
        ];
	}
	function admin_link($id=0,$config=[]) {
		global $zulu;

		if($id==0) {
			$data = $this->vars->data_row;
		} else {
			$data = $this->product_data(['id'=>$id]);
		}
		if($config['link']) {
			return $zulu->link_page('product',array('query'=>array('id'=>$data['id'],'Action'=>'edit')));
		} else {
			return "<a ".(isset($config['target'])?"target=\"".$config['target']."\"":NULL)." href=\"".$zulu->link_page('product',array('query'=>array('id'=>$data['id'],'Action'=>'edit')))."\">".stripslashes($data['title'])."</a>";
		}
	}
	function field_value($id,$field='id') {
		$data = $this->zulu->table_data('product',$id,array("field"=>array($field),"first"=>true));
		return $data[$field];
	}
	function product_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if($config['sku']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "sku = '".$config['sku']."'";
		}
		if($config['token']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "token = '".$config['token']."'";
		}
		if($config['slug']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "slug = '".$config['slug']."'";
		}
		if($config['title']!=NULL) {
			$sql_config['first'] = true;
			$sql_config['where'][] = "title = '".$config['title']."'";
		}
		if($config['template']!=NULL) {
			$sql_config['where'][] = "template = '".$config['template']."'";
		}
        if($config['brand_id'] > 0) {
			$sql_config['where'][] = "brand_id = ".$config['brand_id'];
		}
		if(isset($config['first'])) {
			$sql_config['first'] = $config['first'];
		}
		if(isset($config['root_id'])) {
			//-- filter for meta cats
			if(is_array($config['root_id'])) {
				$root_array = $config['root_id'];
			} else {
				$root_array[] = $config['root_id'];
			}
			$sql_config['where'][] = "(EXISTS(SELECT * FROM product_meta WHERE identifier=product.id AND field='category' AND value IN (".implode(",",$root_array)."))".(!is_array($config['root_id'])?($config['root_id']>0?" OR parent_id = '".$config['root_id']."'":" OR (parent_id = '".$config['root_id']."' AND (type = 'category' OR NOT EXISTS(SELECT * FROM product_meta WHERE identifier=product.id AND field='category' AND value > 0)))"):NULL).")";
			$sql_config['group'] = 'product.id';
			$sql_config['field'] = ['*','product.id AS id'];
		}
		if(isset($config['field'])) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['status'])) {
			$sql_config['where'][] = "product.status = '".$config['status']."'";
		} else {
			$sql_config['where'][] = "product.status = 1";
		}
		if(isset($config['type'])) {
			$sql_config['where'][] = "type = '".$config['type']."'";
		}
		if($config['supplier_id']>0) {
			$tbl = "product INNER JOIN product_meta AS sup ON product.id = sup.identifier";
			$sql_config['where'][] = "(sup.field = 'supplier_id' AND sup.value = '".$config['supplier_id']."')";
		}
		if($config['parent_id']!=NULL) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['type_variant']!=NULL) {
			if(is_array($config['type_variant'])) {
				$sql_config['where'][] = "type_variant IN(".implode(",",$config['type_variant']).")";
			} else {
				$sql_config['where'][] = "type_variant = '".$config['type_variant']."'";
			}
		}
		if(isset($config['name'])) {
			$sql_config['where'][] = "name = '".$config['name']."'";
		}
		if(isset($config['object'])) {
			$tbl = "product JOIN product_meta AS j1 ON product.id = j1.identifier";
			$sql_config['where'][] = "j1.field = 'object'";
			$sql_config['where'][] = "j1.value = '".$config['object']."'";
		}
		if(isset($config['object_id'])) {
			if(isset($tbl)) {
				$sql_config['first'] = true;;
			}
			$tbl .= (isset($tbl)?NULL:"product")." JOIN product_meta AS j2 ON product.id = j2.identifier";
			$sql_config['where'][] = "j2.field = 'object_id'";
			$sql_config['where'][] = "j2.value = '".$config['object_id']."'";
			$sql_config['field'] = ['*','product.id AS id'];
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'name ASC';
		}
		if(isset($config['row_start'])) {
			$sql_config['start'] = $config['row_start'];
		}
		if(isset($config['row_limit'])) {
			$sql_config['limit'] = $config['row_limit'];
		}
		if($config['search']!=NULL) {
			$search = addslashes(strtolower($config['search']));

            $sql_config['where'][] = "(name LIKE '%".$search."%' OR sku LIKE '%".$search."%' OR MATCH(name,sku) AGAINST ('".$search."' IN BOOLEAN MODE) OR MATCH(description) AGAINST ('".$search."' IN BOOLEAN MODE))";
            $sql_config['field']['search1'] = "((name LIKE '%".$search."%') + (sku LIKE '%".$search."%')) AS rel1";
            $sql_config['field']['search2'] = "MATCH(name,sku) AGAINST ('".$search."' IN BOOLEAN MODE) AS rel2";
            $sql_config['field']['search3'] = "MATCH(description) AGAINST ('".$search."' IN BOOLEAN MODE) AS rel3";
            $sql_config['sort'] = "rel1 DESC, rel2 DESC, rel3 DESC";
		}
        if($config['sys'] != null) {
			$sql_config['where'][] = "sys = '".$config['sys']."'";
		}
		if($config['live'] != null) {
			$sql_config['where'][] = "live = '".$config['live']."'";
		}
		if($config['test']) {
			$sql_config['test'] = true;
		}
		if($config['assoc']) {
			$sql_config['assoc'] = true;
		}
        if(is_array($config['where'])) {
			foreach($config['where'] as $wval) {
				$sql_config['where'][] = $wval;
			}
		}

		$sql_config['where'][] = "user_id = '".(isset($config['user_id'])?$config['user_id']:$class_user->authorised->id)."'";
		$tbl = ($tbl!=NULL?$tbl:'product');
		return zulu::table_data($tbl,$id,$sql_config);
	}
	function product_array() {
		$data = $this->product_data(['type'=>'product','type_variant'=>[0,2]]);
		foreach($data as $row) {
			$arr[$row['id']] = $this->name($row['id']);
		}
		asort($arr);
		return $arr;
	}
	function product_delete($id,$identifier='id') {
		global $class_user;
		$this_product = $this->product_data([$identifier=>$id]);
		if($this_product['type_variant']==2&&$this_product['parent_id']>0) {
			$check_type_variant = true;
		}

		$query = "DELETE FROM ".$this->SQL_table_product." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			if($identifier=='id') {
				$this->db->query("DELETE FROM ".$this->SQL_table_product_option." WHERE `product_id` = '".$id."'");
				$this->db->query("DELETE FROM ".$this->SQL_table_product_attribute." WHERE `product_id` = '".$id."'");
			}
			if($check_type_variant) {
				$parent_count = $this->product_data(['parent_id'=>$this_product['parent_id'],'status'=>1,'field'=>['id']]);
				if(count($parent_count)<=0) {
					$this->product_edit($this_product['parent_id'],['type_variant'=>0]);
				}
			}
			return true;
		} else {
			return false;
		}
	}
	function product_block($id=0,$config=[]) {
		global $class_website,$class_setting,$zulu;

		if($id==0) {
			$prod_data = $this->vars->data;
			$id = $prod_data['id'];
		} else {
			$prod_data = $this->product_data(array('id'=>$id));
		}
        $product = Products::find($prod_data['id']);

		$title = stripslashes($prod_data['name']);
		$slug = $prod_data['slug'];
		$images = $this->image_data($id);
		$image = ($images!=NULL?$images['main']:NULL);
		$url = $this->product_url($id,$slug,true);
		$shop_mode = $class_website->shop_mode();
		$icm = $class_setting->data['ws_shop_browse_image_crop'];
		$is_product = ($prod_data['type']=='product'?true:false);
        if(isset($config['link_filter']) && count($config['link_filter']) > 0) {
            $link_filter = $config['link_filter'];
        } else {
            $link_filter = [];
        }

		if($is_product) {
            $price_data = $this->price($id);
            $price_html = LOCALE_currency.$zulu->dollar($price_data['price'],true);
            if($price_data['special']) {
                $price_html = "<span class=\"price-highlight\"><b>".($price_data['price_count']>1?"From":null)."</b> ".$price_html."</span> <strike>".LOCALE_currency.$price_data['rrp']."</strike>";
            } elseif($price_data['price_count'] > 1) {
                $price_html = "From ".$price_html;
            }
            if(defined('PRODUCT_reviews_enabled') && PRODUCT_reviews_enabled) {
                $reviews_enabled = true;
                $ratings = $product->getReviewRating();
            } else {
                $reviews_enabled = false;
            }
			$flag = $product->locationFlag();
			$listing = $product->listing;
			$client = $product->client;
			$is_live = $product->isLive();
			$currency_code = $product->currencyCode();

			if($listing->time_close < strtotime('-1 day')) {
				$closing = "<span class='red'>".$zulu->dateTimezone($listing->time_close, 'h:ia')."</span>";
			} else {
				$closing = $zulu->dateTimezone($listing->time_close, 'd M');
			}
			$owned_listing = $on_watchlist = false;
			if(CLIENT_auth) {
				if($client->id == $_SESSION['user']['id']) {
					$owned_listing = true;
				}
				if($product->onWatchlist($_SESSION['user']['id'])) {
					$on_watchlist = true;
				}
			}
			$is_auction = $product->isAuction();
			$price_class = '';
			if($is_auction) {
				$price_label = 'Starting Price';
				$has_bids = $listing->hasBids();
				if($has_bids) {
					$price_label = 'Current Bid';
					if(CLIENT_auth && $listing->clientHasBid($_SESSION['user']['id'])) {
						if($listing->isClientBidLeader($_SESSION['user']['id'])) {
	                        $price_label = "You Lead!";
	                        $price_class = 'green';
	                    } else {
	                        $price_label = "You're Outbid!";
	                        $price_class = 'red';
	                    }
					}
				}
			}

			$html = "
			<div class=\"box product-type-".$prod_data['type'].($listing->add_feature?' featured':null)."\">
				".(!$owned_listing&&$is_live?"<div class=\"watchlist\">
					<a href=\"".$url."\" title=\"Watchlist\" data-toggle='".($on_watchlist?'remove':'add')."'>
						".$zulu->icon(($on_watchlist?'check-circle':'binoculars'), 'l')."
					</a>
				</div>":null)."
				<a href=\"".$url."\" title=\"View this listing...\">
					<div class=\"image\">
						<img src=\"".zulu::thumb(($image!=NULL?$image:FE_crm.FE_path.FE_tpl."images/placeholder-product.png"),"w=700&h=500&".($icm!=NULL?$icm:'zc')."=1&bg=ffffff")."\" border=\"0\" alt=\"image of ".$title."\" />
						".($flag?"<img src=\"".$flag."\" alt=\"".$product->locationText()."\" class='flag' />":null)."
					</div>
					<div class=\"title result\">
						".$title."
						<div class=\"white-ghost\"></div>
					</div>
					<div class=\"details\">
						<ul class='specs'>
							<li>".$product->spec_age."yo</li>
							<li>".$product->spec_sex."</li>
							<li class='alt'>".$product->locationFullText()."</li>
						</ul>
						<ul class='parents'>
							<li>".stripslashes($product->spec_sire)."</li>
							<li>".stripslashes($product->spec_dam)."</li>
						</ul>
					</div>
					<div class=\"pricing\">
						<dl class='dl-horizontal dt-left dd-right'>
	                        ".($is_auction?"
							<dt>".$price_label."</dt>
							<dd class='".$price_class."'>$".number_format($product->price)." ".$currency_code."</dd>
							":"
							<dt>Asking Price</dt>
	                        <dd>".($product->is_poa ? "Contact the Seller" : "$".number_format($product->price)." ".$currency_code)."</dd>
							")."
							<dt>Closes</dt>
	                        <dd>".$closing."</dd>
	                    </dl>
					</div>
				</a>
			</div>";
		} else {
            if(isset($config['prod_count']) && $config['prod_count'] != null) {
                $prod_count = $config['prod_count'];
            } else {
                $prod_count = null;
            }

			$html = "
			<div class=\"box product-type-".$prod_data['type']."\">
                <a href=\"".$zulu->front_link($url, ['self'=>true, 'filter'=>$link_filter])."\" title=\"View this category...\">
                    ".($image!=null?"
                    <div class=\"image\">
                        <img src=\"".zulu::thumb($image,"w=500&h=350&".($icm!=NULL?$icm:'zc')."=1&bg=ffffff")."\" border=\"0\" alt=\"image of ".$title."\" />
                    </div>
                    ":null)."
                    <div class=\"title result\">
                        ".$title.($prod_count!==null?" <small class='count'>".$prod_count."</small>":null)."
                    </div>
                </a>
			</div>";
		}

		return $html;
	}
	function product_url($id,$slug=NULL,$front=true,$abs=false) {
		$product_data = $this->product_data(array('id'=>$id,'field'=>['type','parent_id','slug','type_variant','id']));
		if($slug==NULL) {
			$slug = $product_data['slug'];
		}
		if($product_data['type']=='category') {
			if($front) {
				$url = $this->category_url_front($product_data['id'],$product_data['slug'],$abs);
			} else {
				$url = $this->category_url(['root_id'=>$product_data['id']]);
			}
		} else {
			if($product_data['type_variant']==2) {
				$parent_data = $this->product_data(array('id'=>$product_data['parent_id'],'field'=>['slug','type_variant','id']));
				$slug = $parent_data['slug'];
				$id = $parent_data['id'];
			}
			$url = ($front?(!$abs?FE_rel:FE_url)."product/".$id."/".$slug."/":NULL);
		}
		return $url;
	}
	function category_url($config=array()) {
		return $this->zulu->link_page('product',array('query'=>array('ProductRoot'=>$config['root_id'])));
	}
	function category_url_front($id,$slug=NULL,$abs=false) {
		if($slug==NULL) {
			$product_data = $this->product_data(['id'=>$id,'field'=>['slug']]);
			$slug = stripslashes($product_data['slug']);
		}
		return (!$abs?FE_rel:FE_url)."category/".$slug."/";
	}
	function image_url($id) {
		return FE_rel."file/product/".$id."/";
	}
	function name($id=0) {
		if($id<=0) {
			$prod_data = $this->vars->data;
		} else {
			$prod_data = $this->product_data(array('id'=>$id,'field'=>['type','name','id','parent_id']));
		}
		if($prod_data['type']!='product') {
			return stripslashes($prod_data['name']);
		} else {
			if(trim($prod_data['name'])==NULL) {
				$check_attr = $this->product_option_data(['product_id'=>$prod_data['id'],'mode'=>'display']);
				foreach($check_attr as $val) {
					$name[] = $val['value'];
				}
				$val = ($prod_data['parent_id']>0?$this->name($prod_data['parent_id'])." (":NULL).implode(", ",$name).($prod_data['parent_id']>0?")":NULL);
			} else {
				$val = $prod_data['name'];
			}
			return stripslashes($val);
		}
	}
	function stock($id=0,$config=[]) {
		global $zulu,$class_setting;

		$global_setting = $class_setting->data;
		if(is_array($id)) {
			$meta = $id['meta'];
			$product_id = $id['id'];
		} elseif($id<=0) {
			$meta = $this->vars->meta_data;
			$product_id = $this->vars->product_id;
		} else {
			$data = $this->product_data(array('id'=>$id,'field'=>['id','status']));
			$meta = $zulu->meta_array($this->product_meta($id));
			$product_id = $id;
		}

		//--location
		if(isset($config['location_id'])) {
			$stock_locations = unserialize($meta['stock_location']);
			$meta['stock'] = $stock_locations[$config['location_id']];
		}

		//--overrides
		$eta = $meta['opt_stock_eta'];
		if(isset($meta['opt_stock_show'])&&trim($meta['opt_stock_show'])!=NULL) {
			$global_setting['ws_shop_stock_show'] = $meta['ws_shop_stock_show'];
		}
		if(isset($meta['opt_stock_low_threshold'])&&trim($meta['opt_stock_low_threshold'])!=NULL) {
			$global_setting['ws_shop_stock_low_threshold'] = $meta['opt_stock_low_threshold'];
		}
		if(isset($meta['opt_stock_show_threshold'])&&trim($meta['opt_stock_show_threshold'])!=NULL) {
			$global_setting['ws_shop_stock_threshold'] = $meta['opt_stock_show_threshold'];
		}
		if(isset($meta['opt_stock_count'])&&trim($meta['opt_stock_count'])!=NULL) {
			$global_setting['ws_shop_stock_count'] = $meta['opt_stock_count'];
		}
		$label = ($meta['stock']>0?($meta['stock']<=$global_setting['ws_shop_stock_low_threshold']&&$global_setting['ws_shop_stock_low_threshold']>0?"Low Stock":"In Stock"):"Out of Stock".($eta>time()?", due ".date('d/m/Y',$eta):NULL));

		$arr = [
			'level'	=>	($meta['stock']==NULL?'0':(isset($meta['stock']['value'])?$meta['stock']['value']:$meta['stock'])),
			'show'	=>	(isset($global_setting['ws_shop_stock_show'])&&$global_setting['ws_shop_stock_show']=='0'?false:true),
			'threshold'	=>	($global_setting['ws_shop_stock_low_threshold']>0?$global_setting['ws_shop_stock_low_threshold']:0),
			'label'	=>	$label,
			'label_count'	=>	((isset($global_setting['ws_shop_stock_count'])&&$global_setting['ws_shop_stock_count']=='0')||$meta['stock']<=0||($global_setting['ws_shop_stock_threshold']>0&&$meta['stock']>=$global_setting['ws_shop_stock_threshold'])?false:true),
			'icon'	=>	($meta['stock']>0?"cubes":"times"),
			'class'	=>	($meta['stock']>0?"success":"danger"),
		];

		return $arr;
	}
	function price($id=0, $config=[]) {
        global $class_setting;

		if($id<=0) {
			$prod_data = $this->vars->data;
		} else {
			$prod_data = $this->product_data(array('id'=>$id,'field'=>['id','type_variant','price_special','price','price_base']));
		}
        $quantity = ($config['quantity']>0?$config['quantity']:1);

		//Scheduled specials
		$schedule_data = $this->scheduled_price($prod_data['id'], $prod_data['price']);
		$has_scheduled_special = $schedule_data['has_scheduled_special'];
		$scheduled_special_price = $schedule_data['scheduled_special_price'];

		if($has_scheduled_special && $scheduled_special_price > 0){
			if($prod_data['price_special']>0 && $scheduled_special_price < $prod_data['price_special']){
				$prod_data['price_special'] = $scheduled_special_price;
			}else if($prod_data['price_special'] == 0 || empty($prod_data['price_special'])){
				$prod_data['price_special'] = $scheduled_special_price;
			}
		}

        // Price Breaks
        if($class_setting->data['price_break_enable'] && !$config['price_break_skip']) {
            $break_data = $this->price_break($prod_data['id'], $quantity, ['price'=>$prod_data['price'],'price_special'=>$prod_data['price_special']]);
            $prod_data['price'] = $break_data['price'];
            $prod_data['price_special'] = $break_data['price_special'];
        }

		if($prod_data['type_variant']==1&&$prod_data['id']>0) {
			$this->child_count($prod_data['id']);
			if(count($this->vars->child_product)>0) {
				foreach($this->vars->child_product as $child_id) {
					$child_price = $this->price($child_id, ['quantity'=>$quantity]);
					$price_collection[] = $child_price['price'];
					$price_collection_rrp[] = ($child_price['rrp']>0?$child_price['rrp']:$child_price['price']);

					if($child_price['special']) {
						$has_special = true;
					}
				}
				asort($price_collection);
				asort($price_collection_rrp);
				$price_collection = array_values($price_collection);
				$price_collection_rrp = array_values($price_collection_rrp);

				$tip = count($price_collection)-1;
				$custom = array('price'=>$price_collection[0],'price_count'=>count($price_collection),'price_low'=>$price_collection[0],'price_high'=>($price_collection_rrp[$tip]>$price_collection[$tip]?$price_collection_rrp[$tip]:$price_collection[$tip]),'special'=>$has_special,'rrp'=>$price_collection_rrp[0],'rrp_low'=>$price_collection_rrp[0],'rrp_high'=>$price_collection_rrp[$tip]);
			}
		}
		if($prod_data['price_special']>0) {
			//Individual product special
			$ret = array('price'=>$prod_data['price_special'],'special'=>true,'rrp'=>$prod_data['price']);
		} else {
			$ret = array('price'=>$prod_data['price']);
		}
		$ret['price_base'] = $prod_data['price_base'];
		if(isset($custom)) {
			return $custom;
		} else {
			return $ret;
		}
	}
	function image_data($id,$config=[]) {
		global $class_user,$zulu;
		$info = $zulu->table_data('product',$id,array('field'=>array('parent_id','type_variant','image'),'where'=>array("user_id='".$class_user->authorised->id."'")));
		foreach(glob($this->image_path.$id."/*.{jpg,jpeg,png,gif}", GLOB_BRACE) as $row) {
			if(basename($row) != '_notes') {
				$file = str_replace($this->image_path.$id."/","",$row);
				if($config['variant_main'] == $file || ($info['image'] == $file && $data['main'] == NULL)) {
					$data['main'] = $this->image_fold.$id."/".$file;
				} else {
					$data['gallery'][] = $this->image_fold.$id."/".$file;
				}
				$data['_all'][] = $this->image_fold.$id."/".$file;
			}
		}
		if($data['main']==NULL) {
			$data['main'] = $data['gallery'][0];
			unset($data['gallery'][0]);

			if(trim($data['main']) == NULL && $info['type_variant'] == 2) {
				$data = $this->image_data($info['parent_id'],['variant_main'=>$info['image']]);
				$skip_rest = true;
			}
		}
		if(!$skip_rest) {
			$data['_path'] = $this->image_fold.$id."/";
			if(count($data['_all']) < 1) {
				$data = NULL;
			}
		}
		return $data;
	}
	function image_set($id,$image) {
		global $class_user;
		$query = "UPDATE product SET image = '{$image}' WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function image_dir($id,$type='abs') {
		if($type=='rel') {
			$dir = dirname(__FILE__)."/../../".$this->image_fold.$id."/";
		} elseif($type=='abs') {
			$dir = $this->image_rel.$id."/";
		} elseif($type=='path') {
			$dir = $this->image_path.$id."/";
		}
		return $dir;
	}
	function image_add($id,$field='image') {
		global $zulu;
		if($_FILES[$field]['tmp_name']!=NULL) {

			$dir = $this->image_dir($id,'rel');
			@mkdir($dir);

			$name_arr = explode('.',$_FILES[$field]['name']);
			$ext = array_pop($name_arr);
			$name = $zulu->slug(implode('',$name_arr)).".".$ext;
			$file_destination = $this->image_dir($id,'path').$name;
			$fdr = $this->image_dir($id,'abs').$name;

			if(move_uploaded_file($_FILES[$field]['tmp_name'],$file_destination)) {
				return array("success"=>true,"reason"=>"File uploaded.","url_abs"=>$file_destination,"url"=>$fdr);
			} else {
				return array("success"=>false,"reason"=>"Failed to move file.");
			}
		} else {
			return array("success"=>false,"reason"=>"Empty image upload field.");
		}
	}
	function image_delete($id,$image) {
		$file = $this->image_path.$id."/".$image;
		@unlink($file);
		return true;
	}
	function category_remove($product,$category) {
		global $zulu;

		$zulu->meta_remove("product",$product,"category",$category);

		return true;
	}
	function category_add($product,$category) {
		global $zulu;

		$zulu->meta_update("product",$product,"category",$category,NULL,0,true);

		return true;
	}
	function child_count($product_id) {
		//$data = $this->product_data(['status'=>1,'parent_id'=>$product_id,'field'=>['id']]);
		$data = $this->attribute_option($product_id);
		foreach($data as $row) {
			$id_arr[] = $row['id'];
		}
		$this->vars->child_product = $id_arr;
		return count($data);
	}
	function product_new($config=array()) {
		global $class_user;
		$data['user_id'] = $class_user->authorised->id;
		$data['token'] = zulu::serial();
		$data['serial'] = zulu::serial(32);
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['name'] = stripslashes($config['name']);
		$data['slug'] = zulu::makeHT($data['name']);
		$data['parent_id'] = $config['parent_id'];
		$data['type'] = 'product';
		$data['type_variant'] = $variant;

		if($data['parent_id']>0&&$this->type($data['parent_id'])=='product') {
			$variant = 2;
		}
		if($variant==2) {
			$this->product_edit($data['parent_id'],['type_variant'=>1]);
		}

		$query = "INSERT INTO product ".$this->db->build(2,array('name','slug','parent_id','serial','token','stat_add','stat_update','type','user_id'),$data);

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
	function product_edit($id,$config=array(),$meta=array()) {
		global $class_user,$zulu;

		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		if($id<1) {
			$data = $this->product_new($config);
			$id = $data['id'];
		}
		if(isset($config['stock'])) {
			$stock_adjust = $config['stock'];
			unset($config['stock']);
		}
		if(isset($config['attribute'])) {
			$attribute_data = $config['attribute'];
			unset($config['attribute']);
		}
		foreach($config as $key=>$val) {
			if(in_array($key,['stock','attribute'])) {
				continue;
			}
			$data[$key] = $val;
			$fields[] = $key;
		}
		if($data['name']!=NULL) {
			$data['slug'] = $zulu->makeHT($data['name']);
		}

		$query = "UPDATE product SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {

            $this_data = $this->product_data(['id'=>$id]);

            //-- update category slug
            if($this_data['type'] == 'category') {
				$this->set_category_slug($id);
			}

			//-- set master product price
			if($this_data['parent_id']>0 && $this_data['type']=='product' && $this_data['type_variant']=='2') {
				$sub_data = $this->product_data(['parent_id'=>$this_data['parent_id'],'sort'=>'price ASC','first'=>true,'status'=>'1']);
				$this->product_edit($this_data['parent_id'],['price'=>$sub_data['price'],'price_special'=>'0']);

				$sub_data = $this->product_data(['parent_id'=>$this_data['parent_id'],'sort'=>'price_special ASC','status'=>'1']);
				foreach($sub_data as $sub_row) {
					if($sub_row['price_special'] > 0) {
						$this->product_edit($this_data['parent_id'],['price_special'=>$sub_row['price_special']]);
						break;
					}
				}
			}

			if(count($meta)>0) {
				foreach($meta as $mkey=>$mval) {
					$zulu->meta_update('product',$id,$mkey,$mval);
				}
			}
			if(count($attribute_data)>0) {
				foreach($attribute_data as $ak=>$av) {
					$attr_config = ['slug'=>$ak,'value'=>$av];
					$this->product_option_set($id,$attr_config);
				}
			}
			if(isset($stock_adjust)) {
				$this->stock_adjust($id,['value'=>$stock_adjust]);
			}
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}

    function set_category_slug($id, $parent_slug='') {
		global $db, $zulu, $class_user;

		$product_data = $this->product_data(array('id'=>$id));
		if($product_data['type'] == 'category') {
			$this_slug = '';
			if($parent_slug != NULL) {
				$this_slug = $parent_slug."/";
			} elseif($product_data['parent_id'] > 0) {
				$parent_data = $this->product_data(['id'=>$product_data['parent_id']]);
				$parent_slug = $parent_data['slug'];
				$this_slug = $parent_slug."/";
			}
			$this_slug .= $zulu->slug($product_data['name']);
			$db->query("UPDATE product SET slug='".$this_slug."' WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'");

			// update child category slugs
			unset($this->vars->children_array);
			if($this->has_children($id)) {
				foreach($this->vars->children_array as $child_row) {
					$this->set_category_slug($child_row['id'], $this_slug);
				}
			}
		}
		return;
	}

	function product_option_set($id,$config) {
		global $zulu;

		$product = $this->product_data(['id'=>$id]);
		if(isset($config['slug'])) {
			$product_attribute = $this->attribute_data(['first'=>true,'slug'=>$config['slug'],'product_id'=>$product['parent_id']]);
		} elseif(isset($config['id'])) {
			$product_attribute = $this->attribute_data(['id'=>$config['id']]);
		} else {
			return false;
		}

		$post = [
			'parent_id'	=>	$product['parent_id'],
			'product_id'	=>	$id,
			'attribute_id'	=>	$product_attribute['id'],
			'slug'			=>	$product_attribute['slug'],
			'name'			=>	$product_attribute['name'],
			'value'			=>	$config['value'],
		];
        if(is_numeric($post['value']) || $post['value'] > 0) {
            $post['value'] = $zulu->esc($post['value']);
        }

		$exist_check = $zulu->table_data($this->SQL_table_product_option,0,['where'=>["product_id = '".$id."'","attribute_id = '".$product_attribute['id']."'"],'first'=>true]);
		$exist_id = ($exist_check['id']>0?$exist_check['id']:0);

		if($exist_id<=0) {
			$query = "INSERT INTO ".$this->SQL_table_product_option." ".$this->db->build(2,array_keys($post),$post);
		} else {
			$query = "UPDATE ".$this->SQL_table_product_option." SET ".$this->db->build(1,array_keys($post),$post)." WHERE id = '".$exist_id."'";
		}

		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}

	function product_option_data($config=[]) {
		global $zulu;

		if(isset($config['attribute'])) { //-- if attribute data specified, assumes looking up products from ATTRIBUTE data
			$i = 0;
			foreach($config['attribute'] as $ak=>$av) {
				$join_sql[] = $this->SQL_table_product_option." po".$i." ON product.id = po".$i.".product_id";
				$where_sql[] = "po".$i.".slug = '".$ak."'";
				if(is_array($av)) {
					foreach($av as $avv) {
						if(trim($avv)!='*') { //-- load attributes WITH a specific value
							$where_sql[] = "po".$i.".value = '".$zulu->esc($avv)."'";
						}
					}
				} else {
					if(trim($av)!='*') { //-- load attributes WITH a specific value
						$where_sql[] = "po".$i.".value = '".$zulu->esc($av)."'";
					}
				}
				$i++;
			}

			$sql_config['join'] = $join_sql;
			$sql_config['field'] = ['product.id'];
			$sql_config['where'] = $where_sql;
			$sql_config['where'][] = 'status = 1';
			$sql_config['where'][] = 'type_variant = 2';
			$sql_config['where'][] = "type = 'product'";
			$sql_config['sort'] = 'product.id ASC';
			if($config['parent_id']>0) {
				$sql_config['where'][] = "product.parent_id = '".$config['parent_id']."'";
			}

			$tdata = $zulu->table_data($this->SQL_table_product,0,$sql_config);

			foreach($tdata as $row) {
				$data[] = $row['id'];
			}
			//-- returns id array of products with matches
		} else { //-- otherwise we look for the product_option rows
			$sql_config = array();
			$id = ($config['id']>0?$config['id']:0);

			if(count($config['field'])>0) {
				$sql_config['field'][] = $config['field'];
			}
			if($config['parent_id']!=NULL) {
				$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
			}
			if($config['product_id']!=NULL) {
				$sql_config['where'][] = "product_id = '".$config['product_id']."'";
			}
			if($config['attribute_id']!=NULL) {
				$sql_config['where'][] = "attribute_id = '".$config['attribute_id']."'";
			}
			if($config['slug']!=NULL) {
				$sql_config['where'][] = "slug = '".$config['slug']."'";
			}
			if($config['value']!=NULL) {
				$sql_config['where'][] = "value = '".$zulu->esc($config['value'])."'";
			}
			if($config['object_id']!=NULL) {
				$sql_config['where'][] = "object_id = '".$config['object_id']."'";
			}
			if($config['object']!=NULL) {
				$sql_config['where'][] = "object = '".$config['object']."'";
			}
			if($config['limit']>0) {
				$sql_config['limit'] = $config['limit'];
			}
			if($config['sort']!=NULL) {
				$sql_config['sort'] = $config['sort'];
			}
			if($config['mode']!=NULL) {
				switch($config['mode']) {
					case 'display':
					$sql_config['field'][] = "name";
					$sql_config['field'][] = "value";
					break;
				}
			}

			return $zulu->table_data($this->SQL_table_product_option,$id,$sql_config);
		}

		return $data;
	}
	function type($id) {
		global $zulu;
		$data = $zulu->table_data($this->SQL_table_product,$id,['field'=>['type']]);
		return $data['type'];
	}
	function product_button($id) {
		$product_data = $this->product_data(array('id'=>$id));
		return $this->zulu->link_page('product',array('query'=>array('Token'=>$product_data['token'],'Action'=>'download')));
	}
	function product_meta($id,$field=NULL) {
		return zulu::meta_value("product",$id,$field);
	}
	function delete($id,$identifier='id') {
		return $this->product_delete($id,$identifier);
	}
	function product_icon($type,$extension='') {
		switch($type) {
			case 'product':
			$icon = "fa-puzzle-piece";
			break;
			case 'category':
			$icon = "fa-folder";
			break;
		}
		return "{$icon}";
	}
	function category_tree($id,$data=array()) {
		$product_data = $this->product_data(array('id'=>$id));
		$data[] = $product_data['id'];
		$this->category_tree->tree[] = $product_data['id'];
		if($product_data['parent_id']>0) {
			$this->category_tree($product_data['parent_id'],$data);
		} else {
			$this->category_tree->tree = array_reverse($this->category_tree->tree);
			return true;
		}
	}
	function category_children($pos,$config=[]) {
		global $zulu;
		$subs = (isset($config['subs'])?$config['subs']:true);
		if(is_array($config['array'])) {
			$child_array = $config['array'];
		} else {
			$child_array = [];
		}
		$this->category_child[$pos] = $pos;

		if($subs) {
			$sub_data = $this->product_data(['parent_id'=>$pos,'type'=>'category','field'=>['parent_id','id']]);
			foreach($sub_data as $row) {
				$this->category_child[$row['id']] = $row['id'];
				$has_kid = $this->product_data(['parent_id'=>$row['id'],'type'=>'category','first'=>true,'field'=>['name','id']]);

				if($has_kid['id']>0) {
					$this->category_children($row['id'],['array'=>$child_array]); // recursive call
				}
			}
		} else {
			$parent_data = $zulu->table_data($this->SQL_table_product,0,['field'=>['id'],'where'=>['type'=>'category','parent_id'=>$pos]]);
			foreach($parent_data as $item) {
				$output[] = $item['id'];
			}
			return $output;
		}
	}
	function category_breadcrumb($id) {
		$this->category_tree($id);
		foreach($this->category_tree->tree as $row) {
			$product_data = $this->product_data(array('id'=>$row));
			$this->zulu->nav->breadcrumb[stripslashes($product_data['name'])] = array("link"=>$this->zulu->link_page('product',array('query'=>array('ProductRoot'=>$row))));
		}
		unset($this->category_tree->tree);
		return true;
	}
	function has_children($pid=0) {
		global $zulu;
		$data = $zulu->table_data($this->SQL_table_product,0,['field'=>['id'],'where'=>["status=1","parent_id='".$pid."'"]]);
		if(count($data)>0) {
			$this->vars->children_array = $data;
			return true;
		} else {
			return false;
		}
	}
	function is_parent($pid,$cid) {
		$data = $this->product_data(array('id'=>$pid));
		if($data['parent_id']==$cid) {
			return true;
		} else {
			$parent_data = $this->product_data(array('id'=>$data['parent_id']));
			if($parent_data['parent_id']==0) {
				return false;
			} else {
				return $this->is_parent($parent_data['id'],$cid);
			}
		}
	}

	function stock_data($config=[]) {
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if(count($config['field'])>0) {
			$sql_config['field'][] = $config['field'];
		}
		if($config['location_id']!=NULL) {
			$sql_config['where'][] = "location_id = '".$config['location_id']."'";
		}
		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['object_id']!=NULL) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['object']!=NULL) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		if($config['is_return']!=NULL) {
			$sql_config['where'][] = "is_return = '".$config['is_return']."'";
		}
		if($config['limit']>0) {
			$sql_config['limit'] = $config['limit'];
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		}

		return zulu::table_data($this->SQL_table_product_stock,$id,$sql_config);
	}
	function stock_level($id,$config=[]) {
		if($this->has_children($id)) {
			$default_filter = [];
			if($config['location_id']>0) {
				$default_filter['location_id'] = $config['location_id'];
			}
			foreach($this->vars->children_array as $row) {
				$level += $this->stock_level($row['id'],$default_filter);
			}
		} else {
			$default_filter = array('product_id'=>$id);
			if($config['location_id']>0) {
				$default_filter['location_id'] = $config['location_id'];
			}
			$data = $this->stock_data($default_filter);
			$level = 0;
			foreach($data as $row) {
				$level += $row['value'];
			}
		}
		return $level;
	}
	function stock_label($level) {
		$level = ($level==NULL?0:$level);
		return ($level<=0?"<span class=\"opt opt-danger\" title=\"".$title."\"><b>Out of stock</b> <span class=\"fas fa-chevron-down\"></span> {$level}</span>":"<span class=\"opt opt-success\"><b>In stock</b> <span class=\"fas fa-chevron-up\"></span> {$level}</span>");
	}
	function stock_adjust($id, $config=[]) {
		global $zulu;

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		$fields[] = 'product_id';
		$fields[] = 'stat_add';
		$data['product_id'] = $id;
		$data['stat_add'] = time();

		$query = "INSERT INTO ".$this->SQL_table_product_stock." ".$this->db->build(2,$fields,$data);
		$this->db->query($query);

		$stock_adjust = $zulu->meta_value("product",$id,"stock_adjust");
		$stock_adjust = unserialize($stock_adjust['value']);
		if(count($stock_adjust)>0) {
			foreach($stock_adjust as $sa) {
				$saa = $config;
				$saa['parent_id'] = $this->db->insert_id;
				$saa['value'] = (($sa['quantity'])*$config['value']);
				$this->stock_adjust($sa['product_id'],$saa);
			}
		}

		$level = $this->stock_level($id);
		$zulu->meta_update("product",$id,"stock",$level);

		if($data['location_id']>0) {
			$loc_level = $this->stock_level($id,['location_id'=>$data['location_id']]);
			$stock_location_meta = $zulu->meta_value('product',$id,'stock_location');
			$stock_location = unserialize($stock_location_meta['value']);
			$stock_location[$data['location_id']] = $loc_level;
			$zulu->meta_update("product",$id,"stock_location",serialize($stock_location));
			unset($stock_location);
		}

		//Check parent
		$data = $this->product_data(['id'=>$id]);

		if($data['parent_id']>0&&$data['type_variant']==2) {
			$level = $this->stock_level($data['parent_id']);
			$zulu->meta_update("product",$data['parent_id'],"stock",$level);
		}

		return;
	}
	function attribute_is($id=0) {
		if($id>0) {
			$data = $this->product_data(array('id'=>$id));
		} else {
			$data = $this->vars->data;
		}

		$attr_check = $this->attribute_data(['product_id'=>$data['id'],'field'=>['id']]);
		if(count($attr_check)>0) {
			foreach($attr_check as $row) {
				$ret_arr[] = $row['id'];
			}
			$this->vars->attr_array = $ret_arr;
			return true;
		} else {
			return false;
		}
	}
	function custom_filter($array) {
		global $class_sale;

		$new = $array;

		//--new line?
		if($array['product_id']>0) { //by product id
			$product_meta = $this->product_meta($array['product_id']);
		} elseif($array['line_id']>0) { //by line id
			$line_data = $class_sale->sale_line_data(['id'=>$array['line_id']]);
			$product_meta = $this->product_meta($line_data['product_id']);
		} else { //by sku

			$product_data = $this->product_data(['sku'=>$array['sku'],'field'=>['id']]);
			$product_meta = $this->product_meta($array['product_id']);
		}

		$p_custom = $product_meta['custom_variable']['value'];

		if($p_custom!=NULL) {
			$cd = explode(",",$p_custom);
			foreach($cd as $cdr) {
				$spl = explode("=",$cdr);
				$p_custom_data[$spl[0]] = $spl[1];
			}
		}

		//-- pay profile
		if(isset($class_sale->pay_profile[$p_custom_data['pay']])) {
			$new['custom']['pay'] = $p_custom_data['pay'];
		}

		return $new;
	}
	private function attribute_new($data) {
		global $zulu;

		$data = ['token'=>$zulu->serial(8),'product_id'=>$data['product_id'],'stat_add'=>time()];
		$query = "INSERT INTO ".$this->SQL_table_product_attribute." ".$this->db->build(2,['token','product_id','stat_add'],$data);

		if($this->db->query($query)) {
			return ['id'=>$this->db->insert_id,'success'=>true];
		} else {
			return ['success'=>false];
		}
	}
	function attribute_edit($id,$config=array()) {
		global $zulu;

		$option_ids = [];
		if(count($config['option_ids']) > 0) {
			$option_ids = $config['option_ids'];
		}
		unset($config['option_ids']);

		$new = false;
		if($id<1) {
			$data = $this->attribute_new($config);
			$id = $data['id'];
			$new = true;
		}

		unset($data);
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		if(isset($data['name'])) {
			$data['slug'] = $zulu->slug($data['name']);
			$fields[] = 'slug';
		}

		if(!$new && isset($data['options']) && count($option_ids)>0) {
			$options = explode(',',$data['options']);
			foreach($options as $key=>$option) {
				if($option_ids[$key] > 0) {
					$option_row = $this->product_option_data(['id'=>$option_ids[$key]]);
					if($option != $option_row['value']) {
						$this->db->query("UPDATE ".$this->SQL_table_product_option." SET ".$this->db->build(1,['value'],['value'=>$option])." WHERE id IN (".$option_ids[$key].")");
					}
				}
			}
		}

		$query = "UPDATE ".$this->SQL_table_product_attribute." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($this->db->query($query)) {
			if(isset($data['name'])) $this->db->query("UPDATE ".$this->SQL_table_product_option." SET ".$this->db->build(1,['slug','name'],['slug'=>$data['slug'],'name'=>$data['name']])." WHERE attribute_id = '{$id}'");
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function attribute_delete($id,$identifier='id') {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_product_attribute." WHERE `{$identifier}` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function attribute_data($config=array()) {
		global $zulu;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['name']!=NULL) {
			$sql_config['where'][] = "name = '".$config['name']."'";
		}
		if($config['slug']!=NULL) {
			$sql_config['where'][] = "slug = '".$config['slug']."'";
		}
		if($config['input']!=NULL) {
			$sql_config['where'][] = "input = '".$config['input']."'";
		}
		if($config['input_not']!=NULL) {
			$sql_config['where'][] = "input != '".$config['input_not']."'";
		}
		if($config['field']!=NULL) {
			$sql_config['field'] = $config['field'];
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['sort'] = 'input_sort ASC, name ASC';
		return $zulu->table_data($this->SQL_table_product_attribute,$id,$sql_config);
	}
	function attribute_option_array($id=0) {
        global $zulu;
		if($id==0) {
			$attr_data = $this->vars->attribute_data;
		} else {
			$attr_data = $this->attribute_data(['id'=>$id]);
		}
		$options = explode(",",$attr_data['options']);
		foreach($options as $opt) {
			$opts[$zulu->entity($opt)] = $zulu->entity($opt);
		}
		return $opts;
	}
	function attribute_option($id,$config=[]) { //-- product options
		$newconf = $config;
		$newconf['parent_id'] = $id;
		$newconf['type_variant'] = 2;
		return $this->product_data($newconf);
	}
	function attribute_option_combinations($id,$config=[]) { //-- attribute combinations for product
		$attributes = $this->attribute_data(['product_id'=>$id,'input_not'=>'checkbox']);
		foreach($attributes as $row) {
			$options = explode(",",$row['options']);
			foreach($options as $opt) {
				if($config['label']) {
					$combination[$row['name']][] = $opt;
				} else {
					$combination[$row['slug']][] = $opt;
				}
			}
		}

		return $this->combination($combination);
	}
	private function combination($arrays) {
		$result = array(array());
		foreach ($arrays as $property => $property_values) {
			$tmp = array();
			foreach ($result as $result_item) {
				foreach ($property_values as $property_value) {
					$tmp[] = array_merge($result_item, array($property => $property_value));
				}
			}
			$result = $tmp;
		}
		return $result;
	}

	function attribute_edit_table($id=0,$config=[]) {
		global $form_edit,$zulu;

		if($id > 0) $attribute_data = $this->attribute_data(array('id'=>$id));
		$show_price = false;
		if(($config['input']!=NULL && in_array($config['input'],$this->config->attr_input_price)) || ($config['input']==NULL && in_array($attribute_data['input'],$this->config->attr_input_price))) {
			$show_price = true;
		}
        $atribute_data_all = $this->attribute_data(array('product_id'=>$attribute_data['product_id'],'input_not'=>'checkbox'));
		$options = explode(',',$attribute_data['options']);
		$options_price = explode(',',$attribute_data['options_price']);
		if($options[0] == '' && is_array($_POST['option']['name'])) {
			$options = $_POST['option']['name'];
			$options_price = $_POST['option']['price'];
		}
		$table_column[] = array("Name",array('class'=>array('')));
		if($show_price) $table_column[] = array("Price",array('class'=>array('')));
		if($id > 0 && !$show_price) $table_column[] = array("",array('class'=>array('')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = ["header" => true, "class" => "", "content" => $table_column];
		foreach($options as $key=>$option) {
			$option_data = $this->product_option_data(['value'=>$option,'slug'=>$attribute_data['slug'],'attribute_id'=>$attribute_data['id'],'parent_id'=>$attribute_data['product_id']]);
			$option_row = $option_data[0];

            $opt_link = "";
            $opt_ids = [];
            if($id > 0 && !$show_price && $option_row['id'] > 0) {
                if(count($atribute_data_all) > 1) {
                    $opt_link = "<a href='".$zulu->link_page('product',['query'=>['ProductRoot'=>$attribute_data['product_id'],'Attribute'=>$attribute_data['id'],'Option'=>$option]])."' target='_blank' class=\"btn btn-warning btn-xs\" title='This option is linked to a product variants.'><i class='fas fa-link'></i> Linked products</a>";
                } else {
                    $opt_link = "<a href='".$zulu->link_page('product',['query'=>['id'=>$option_row['product_id'],'Action'=>'edit']])."' target='_blank' class=\"btn btn-warning btn-xs\" title='This option is linked to a product variant.'><i class='fas fa-link'></i> Linked product</a>";
                }
                foreach($option_data as $opt_row) {
                    $opt_ids[] = $opt_row['id'];
                }
            }

			$content_rows[] = array($form_edit->input_html('input','option[name][]',$zulu->entity($option)));
			if($show_price) $content_rows[] = array("<div class='input-group'><span class='input-group-addon'>$</span>".$form_edit->input_html('input','option[price][]',$options_price[$key])."</div>");
			if($id > 0 && !$show_price) $content_rows[] = array($opt_link.$form_edit->input_html('hidden','option[option_id][]',implode(',',$opt_ids)),array('class'=>array('center')));
			$content_rows[] = array("<a href=\"#\" class=\"btn btn-default btn-xs clear-row\" title='Clear row'><i class=\"fas fa-eraser\"></i></a> <a href=\"#\" class=\"btn btn-danger btn-xs remove-row\" title='Remove row'><i class=\"fas fa-times\"></i></a>",array('class'=>array('right','w80')));
			$table_row[] = array("content" => $content_rows);
			unset($content_rows);
		}
		$html = $zulu->table_render($table_row,0,array('class'=>'','js_table'=>false,'data_table'=>false,'html_id'=>'attr-options','tbody'=>['id'=>'sortable-rows']));
		return $html;
	}

	function feature_data($config=array()) {
		global $zulu, $class_user;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if($config['product_id']!=NULL) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['has_image']) {
			$sql_config['where'][] = "image != ''";
		}
		if($config['no_image']) {
			$sql_config['where'][] = "image = ''";
		}
		if($config['field']!=NULL) {
			$sql_config['field'] = $config['field'];
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		$sql_config['sort'] = 'sort ASC, title ASC';
		return $zulu->table_data($this->SQL_table_product_feature,$id,$sql_config);
	}

	private function feature_new($data) {
		global $zulu, $class_user;

		$data = ['user_id'=>$class_user->authorised->id,'product_id'=>$data['product_id'],'stat_add'=>time()];
		$query = "INSERT INTO ".$this->SQL_table_product_feature." ".$this->db->build(2,['product_id','stat_add','user_id'],$data);

		if($this->db->query($query)) {
			return ['id'=>$this->db->insert_id,'success'=>true];
		} else {
			return ['success'=>false];
		}
	}
	function feature_edit($id,$config=array()) {
		global $zulu,$class_user;

		if($id<1) {
			$data = $this->feature_new($config);
			$id = $data['id'];
		}

		unset($data);
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$query = "UPDATE ".$this->SQL_table_product_feature." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}

	function feature_image_add($id,$field='image') {
		global $zulu;
		if($_FILES[$field]['tmp_name']!=NULL) {
			$product_id = $this->vars->product_id;
			if($product_id <= 0) {
				$feature_row = $this->feature_data(['id'=>$id]);
				$product_id = $feature_row['product_id'];
			}
			$dir = $this->image_dir($product_id,'rel')."feature/";
			@mkdir($dir);
			$dir .= $id."/";
			@mkdir($dir);

			$name_arr = explode('.',$_FILES[$field]['name']);
			$ext = array_pop($name_arr);
			$name = $zulu->slug(implode('',$name_arr)).".".$ext;
			$file_destination = $this->image_dir($product_id,'path')."feature/".$id."/".$name;
			$fdr = $this->image_dir($product_id,'abs')."feature/".$id."/".$name;

			if(move_uploaded_file($_FILES[$field]['tmp_name'],$file_destination)) {
				$this->feature_edit($id,['image'=>$name]);
				return array("success"=>true,"reason"=>"File uploaded.","url_abs"=>$file_destination,"url"=>$fdr,'name'=>$name);
			} else {
				return array("success"=>false,"reason"=>"Failed to move file.");
			}
		} else {
			return array("success"=>false,"reason"=>"Empty image upload field.");
		}
	}
	function feature_image_delete($id) {
		$feature_row = $this->feature_data(['id'=>$id]);
		$product_id = $feature_row['product_id'];
		$file = $this->image_path.$product_id."/feature/".$id."/".$feature_row['image'];
		@unlink($file);
		$this->feature_edit($id,['image'=>'']);
		return true;
	}
	function feature_delete($id,$identifier='id') {
		global $class_user;

		$feature_row = $this->feature_data(['id'=>$id]);
		$product_id = $feature_row['product_id'];

		$query = "DELETE FROM ".$this->SQL_table_product_feature." WHERE `{$identifier}` = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			@unlink($this->image_path.$product_id."/feature/".$id."/");
			return true;
		} else {
			return false;
		}
	}

	function product_duplicate($id, $config=[]) {
		global $zulu, $class_user;

		$product_data = $this->product_data(array('id'=>$id,'assoc'=>true));
		if($product_data['id'] > 0) {
			$parent = (in_array($product_data['type_variant'],[0,1])?1:0);
			unset($product_data['id'], $product_data['token'], $product_data['serial'], $product_data['stat_add'], $product_data['stat_update'], $product_data['timestamp']);
			if($config['parent_id'] > 0) {
                $org_parent_id = $product_data['parent_id'];
                $product_data['parent_id'] = $config['parent_id'];
            }
            if(trim($product_data['name']) != NULL) {
                $product_data['name'] .= " Copy";
            }
            if(trim($product_data['sku']) != NULL) {
                $product_data['sku'] .= "_1";
            }
			$meta_data = $zulu->meta_array($this->product_meta($id));
			$product_data['stock'] = $meta_data['stock'];
			$result = $this->product_edit(0,$product_data,$meta_data);
			if($result['id'] > 0) {
				$new_id = $result['id'];
				$new_file_path = $this->image_dir($new_id,'path');
				@mkdir($new_file_path);
				foreach(glob($this->image_dir($id,'path')."*") as $file) {
					@copy($file,$new_file_path.basename($file));
				}
                $price_break_data = $this->price_break_data(['product_id'=>$id]);
                if(count($price_break_data) > 0) {
                    foreach($price_break_data as $price_break_row) {
                        unset($price_break_row['id'], $price_break_row['token'], $price_break_row['stat_add'], $price_break_row['stat_update'], $price_break_row['timestamp']);
                        $price_break_row['product_id'] = $new_id;
                        $this->price_break_edit(0,$price_break_row);
                    }
                }
				if($parent) {
					$attr_data = $this->attribute_data(['product_id'=>$id]);
					foreach($attr_data as $attr_row) {
						$attr_id = $attr_row['id'];
						unset($attr_row['id'], $attr_row['token'], $attr_row['stat_add'], $attr_row['timestamp']);
						$attr_row['product_id'] = $new_id;
						$attr_result = $this->attribute_edit(0,$attr_row);
						$attr_relation[$attr_id] = $attr_result['id'];
					}
					$feature_data = $this->feature_data(['id'=>$id]);
					$new_file_path_feature = $this->image_dir($new_id,'path')."feature/";
					@mkdir($new_file_path_feature);
					foreach($feature_data as $feature_row) {
						$curr_dir = $this->image_dir($id,'path')."feature/".$feature_row['id']."/";
						unset($feature_row['id'], $feature_row['stat_add'], $feature_row['stat_update'], $feature_row['timestamp']);
						$feature_row['product_id'] = $new_id;
						$feature_result = $this->feature_edit(0,$feature_row);
						if($feature_row['image']!=NULL) {
							@mkdir($new_file_path_feature.$feature_result['id']."/");
							@copy($curr_dir.$feature_row['image'],$new_file_path_feature.$feature_row['image']);
						}
					}
					$variant_data = $this->product_data(array('parent_id'=>$id));
					foreach($variant_data as $variant_row) {
						$this->product_duplicate($variant_row['id'], ['parent_id'=>$new_id, 'attr_relation'=>$attr_relation]);
					}
				} else {
					$option_data = $this->product_option_data(['parent_id'=>$org_parent_id,'product_id'=>$id]);
					foreach($option_data as $option_row) {
						unset($option_row['id'], $option_row['timestamp']);
						$option_row['product_id'] = $new_id;
                        $option_row['parent_id'] = $product_data['parent_id'];
						if($config['attr_relation'][$option_row['attribute_id']] > 0) {
                            $option_row['attribute_id'] = $config['attr_relation'][$option_row['attribute_id']];
                        }
						$this->db->query("INSERT INTO ".$this->SQL_table_product_option." ".$this->db->build(2,array_keys($option_row),$option_row));
					}
				}

				return ['success'=>true,'id'=>$new_id];
			}
		}
		return ['success'=>false];
	}

	//------Product Specials----------
	function product_special_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if($config['active']){
			//Within date period
			$sql_config['where'][] = "date_from < '".time()."' AND date_to > '".time()."'";
		}else if($config['upcoming']){
			//Before date period
			$sql_config['where'][] = "date_from > '".time()."' AND date_to > '".time()."'";
		}else if($config['complete']){
			//After date period
			$sql_config['where'][] = "date_from < '".time()."' AND date_to < '".time()."'";
		}

		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";

		return zulu::table_data($this->SQL_table_product_special,$id,$sql_config);
	}
	private function product_special_new($data) {
		global $zulu, $class_user;

		$data = ['user_id'=>$class_user->authorised->id,'stat_add'=>time()];
		$query = "INSERT INTO ".$this->SQL_table_product_special." ".$this->db->build(2,['stat_add','user_id'],$data);

		if($this->db->query($query)) {
			return ['id'=>$this->db->insert_id,'success'=>true];
		} else {
			return ['success'=>false];
		}
	}
	function product_special_edit($id,$config=array(),$products =[],$edit_products = false) {
		global $zulu,$class_user;
		if($id<1) {
			$data = $this->product_special_new($config);
			$id = $data['id'];
		}
		unset($data);
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		$data['stat_update'] = time();
		$fields[] = 'stat_update';

		$query = "UPDATE ".$this->SQL_table_product_special." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";

		if($this->db->query($query)) {
			if($edit_products){
				//Remove Existing products
				$this->remove_products_from_special($id);
				foreach($products as $product_id){
					$this->product_special_item_new($id, $product_id);
				}
			}
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function product_special_delete($id) {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_product_special." WHERE  id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			$this->remove_products_from_special($id);
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function product_special_item_data($config=array()) {
		global $class_user;
		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if(count($config['field'])>0) {
			$sql_config['field'] = $config['field'];
		}
		if($config['product_id'] > 0) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['product_special_id'] > 0) {
			$sql_config['where'][] = "product_special_id = '".$config['product_special_id']."'";
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";

		return zulu::table_data($this->SQL_table_product_special_item,$id,$sql_config);
	}
	private function product_special_item_new($product_special_id, $product_id) {
		global $zulu, $class_user;

		$data = ['user_id'=>$class_user->authorised->id,'stat_add'=>time(), 'product_special_id'=>$product_special_id, 'product_id'=>$product_id];
		$query = "INSERT INTO ".$this->SQL_table_product_special_item." ".$this->db->build(2,['stat_add','user_id','product_special_id','product_id'],$data);
		if($this->db->query($query)) {
			return ['id'=>$this->db->insert_id,'success'=>true];
		} else {
			return ['success'=>false];
		}
	}
	function remove_products_from_special($product_special_id) {
		global $class_user;
		$query = "DELETE FROM ".$this->SQL_table_product_special_item." WHERE  product_special_id = '{$product_special_id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function scheduled_price($product_id, $product_price){
		$has_scheduled_special = false;
		$scheduled_special_price;
		$product_special_item_data = $this->product_special_item_data(['product_id'=>$product_id]);
		if(count($product_special_item_data) > 0){
			foreach($product_special_item_data as $special_product){
				$this_special_price;
				$product_special_data = $this->product_special_data(['id'=>$special_product['product_special_id']]);
				//Check if the special is active
				if($product_special_data['status'] == 1 && time() > $product_special_data['date_from'] && time() < $product_special_data['date_to']){
					$has_scheduled_special = true;
					switch ($product_special_data['discount_type']) {
						case 'fixed':
							$this_special_price = $product_price - $product_special_data['amount'];
							break;
						case 'percent':
							$this_special_price = $product_price - ($product_special_data['amount']/100 * $product_price);
							break;
						case 'actual':
							$this_special_price = $product_special_data['amount'];
							break;
					}
				}
				if($scheduled_special_price == NULL || $this_special_price < $scheduled_special_price){
					$scheduled_special_price = $this_special_price;
				}
			}
		}
		return ['has_scheduled_special'=>$has_scheduled_special, 'scheduled_special_price'=>$scheduled_special_price];
	}
	//------Product Specials END----------

	/*------ Price Breaks ------*/
	private function price_break_new($config=[]) {
		global $zulu;

		$data = [
            'token'         =>  $zulu->serial(),
            'product_id'    =>  $config['product_id'],
            'stat_add'      =>  time(),
        ];
		$query = "INSERT INTO ".$this->SQL_table_product_price_break." ".$this->db->build(2,['token','product_id','stat_add'],$data);

		if($this->db->query($query)) {
			return ["success"=>true,"id"=>$this->db->insert_id];
		} else {
			return ["success"=>false,"id"=>0];
		}
	}
	function price_break_edit($id=0,$config=[]) {
		global $zulu;
        $data = $fields = [];

		if($id < 1) {
			$result = $this->price_break_new($config);
			$id = $result['id'];
		}

        $data['stat_update'] = time();
        $fields[] = 'stat_update';

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_product_price_break." SET ".$this->db->build(1,$fields,$data)." WHERE id = '".$id."'";
		if($this->db->query($query)) {
			return ["success"=>true,"id"=>$id];
		} else {
			return ["success"=>false];
		}
	}
	function price_break_delete($id,$identifier='id') {
		$query = "DELETE FROM ".$this->SQL_table_product_price_break." WHERE `".$identifier."` = '".$id."'";
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function price_break_data($config=[]) {
		global $zulu;

		$sql_config = ['where'=>[]];
		$id = ($config['id']>0?$config['id']:0);

        if($config['field'] != null) {
			$sql_config['field'] = $config['field'];
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
        if($config['sort'] != null) {
			$sql_config['sort'] = $config['sort'];
		} else {
            $sql_config['sort'] = 'quantity_min ASC';
        }
        if(is_array($config['where'])) {
			foreach($config['where'] as $wval) {
				$sql_config['where'][] = $wval;
			}
		}
        if($config['group'] != null) {
			$sql_config['group'] = $config['group'];
		}

        if($config['token'] != null) {
			$sql_config['where'][] = "token = '".$config['token']."'";
            $sql_config['first'] = true;
		}
		if($config['product_id'] != null) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		}
		if($config['quantity'] > 0) {
			$sql_config['where'][] = "quantity_min <= '".$config['quantity']."'";
			$sql_config['where'][] = "(quantity_max = '0' OR quantity_max >= '".$config['quantity']."')";
		}

		return $zulu->table_data($this->SQL_table_product_price_break,$id,$sql_config);
	}
	function price_break_check($product_id) {

		$data = $this->price_break_data(['product_id'=>$product_id]);
        foreach($data as $row) {
            foreach($data as $row2) {
                if($row['id'] != $row2['id']) {
                    if($row['quantity_min'] >= $row2['quantity_min'] && $row['quantity_min'] <= $row2['quantity_max']) {
                        return false;
                    } elseif(($row['quantity_max'] >= $row2['quantity_min'] && $row['quantity_max'] <= $row2['quantity_max']) || ($row['quantity_max'] == $row2['quantity_max'])) {
                        return false;
                    }
                }
            }
        }

		return true;
	}
    function price_break($product_id, $quantity=1, $config=[]) {
        global $class_setting;

        $return = [];
        if($class_setting->data['price_break_enable']) {
            $prod_row = $this->product_data(['id'=>$product_id]);
            $disable = $this->product_meta($prod_row['id'],'price_break_disable')['value'];
            if(!$disable) {
                $break_row = $this->price_break_data(['product_id'=>$prod_row['id'], 'quantity'=>$quantity, 'first'=>true]);
                if($break_row['id'] > 0) {
                    $return['price'] = $break_row['price'];
                    $return['price_special'] = $break_row['price_special'];
                    $return['price_break'] = true;
                }
            }
            if($prod_row['parent_id'] > 0 && !$return['price_break']) {
                $disable = $this->product_meta($prod_row['id'],'price_break_disable_parent')['value'];
                if(!$disable) {
                    $return = $this->price_break($prod_row['parent_id'],$quantity);
                }
            }
        }

        if(isset($config['price'])) {
            if($return['price_break']) {
                if($config['price'] < $return['price']) {
                    $return['price'] = $config['price'];
                }
                if($return['price_special'] >= $config['price']) {
                    $return['price_special'] = 0;
                }
                if($config['price_special'] > 0 && $return['price'] <= $config['price_special']) {
                    $return['price_special'] = 0;
                }
                if($config['price_special'] > 0 && ($return['price_special'] <= 0 || $config['price_special'] < $return['price_special'])) {
                    $return['price_special'] = $config['price_special'];
                }
            } else {
                $return['price'] = $config['price'];
                $return['price_special'] = $config['price_special'];
            }
        }

        return $return;
	}
	function price_break_table($product_id, $config=[]) {
		global $zulu, $class_setting;

		$product_row = $this->product_data(['id'=>$product_id]);
        $disable = $this->product_meta($product_row['id'], 'price_break_disable')['value'];
        $disable_parent = $this->product_meta($product_row['id'], 'price_break_disable_parent')['value'];
        $price_break_data = $this->price_break_data(['product_id'=>$product_row['id']]);
		$table_html = $table_rows = '';

        if($class_setting->data['price_break_enable']) {
            $quantity_highlight = (isset($config['quantity_highlight'])?$config['quantity_highlight']:false);
            if(!$disable && count($price_break_data) > 0) {
                if($price_break_data[0]['quantity_min'] > 1) {
                    $price_data = $this->price($product_row['id']);
                    $table_rows .= "
                    <tr class=''>
                        <td class='center'>0</td>
                        <td class='center'>".$price_break_data[0]['quantity_min']."</td>
                        <td class='center'>".LOCALE_currency_symbol.$zulu->dollar($price_data['price'],true)."</td>
                    </tr>";
                }
                foreach($price_break_data as $price_break_row) {
                    $highlight = false;
                    $price = $price_break_row['price'];
                    if($price_break_row['price_special'] > 0) {
                        $price = $price_break_row['price_special'];
                    }

                    if($quantity_highlight !== false && $price_break_row['quantity_min'] <= $quantity_highlight && ($price_break_row['quantity_max'] == 0 || $price_break_row['quantity_max'] >= $quantity_highlight)) {
                        $highlight = true;
                    }
                    if($price_break_row['quantity_max'] <= 0) {
                        $price_break_row['quantity_max'] = "+";
                    }
                    $table_rows .= "
                    <tr class='".(!$highlight?'':'success')."'>
                        <td class='center'>".$price_break_row['quantity_min']."</td>
                        <td class='center'>".$price_break_row['quantity_max']."</td>
                        <td class='center'>".LOCALE_currency_symbol.$zulu->dollar($price,true)."</td>
                    </tr>";
                }
                $table_html = "
                <table class='table table-striped table-bordered table-hover grid-table'>
                    <thead>
                        <tr>
                            <td class='center'>Minimum Quantity</td>
                            <td class='center'>Maximum Quantity</td>
                            <td class='center'>Price</td>
                        </tr>
                    </thead>
                    <tbody id='item-table'>".$table_rows."</tbody>
                </table>";

            } elseif(!$disable_parent && $product_row['parent_id'] > 0) {
                $table_html = $this->price_break_table($product_row['parent_id']);

            }
        }

		return $table_html;
	}
	/*------ Price Breaks END ------*/

	/*------ Stock Location ------*/
	private function location_new($config=[]) {
		global $zulu,$class_user;

		$data = [
            'token'         =>  $zulu->serial(),
            'user_id'    =>  $class_user->authorised->id,
        ];
		$query = "INSERT INTO ".$this->SQL_table_product_location." ".$this->db->build(2,['token','user_id'],$data);

		if($this->db->query($query)) {
			return ["success"=>true,"id"=>$this->db->insert_id];
		} else {
			return ["success"=>false,"id"=>0];
		}
	}
	function location_edit($id=0,$config=[]) {
		global $zulu,$class_setting;
        $data = $fields = [];

		if($id < 1) {
			$result = $this->location_new($config);
			$id = $result['id'];
		}

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table_product_location." SET ".$this->db->build(1,$fields,$data)." WHERE id = '".$id."'";
		if($this->db->query($query)) {

			if(count($this->location_data())==0) {
				$class_setting->setting_edit('stock_location',1);
				$class_setting->construct(['cache_clear'=>true]);
				$this->location_set_main($id);
			}

			return ["success"=>true,"id"=>$id];
		} else {
			return ["success"=>false];
		}
	}
	function location_delete($id,$identifier='id') {
		$query = "DELETE FROM ".$this->SQL_table_product_location." WHERE `".$identifier."` = '".$id."'";
		if($this->db->query($query)) {

			if(count($this->location_data())<=0) {
				$class_setting->setting_edit('stock_location',1);
				$class_setting->construct(['cache_clear'=>true]);
			}

			return true;
		} else {
			return false;
		}
	}
	function location_data($config=[]) {
		global $zulu,$class_user;

		$sql_config = ['where'=>[]];
		$id = ($config['id']>0?$config['id']:0);

        if($config['field'] != null) {
			$sql_config['field'] = $config['field'];
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
        if($config['sort'] != null) {
			$sql_config['sort'] = $config['sort'];
		} else {
            $sql_config['sort'] = 'name ASC';
        }
        if(is_array($config['where'])) {
			foreach($config['where'] as $wval) {
				$sql_config['where'][] = $wval;
			}
		}
        if($config['group'] != null) {
			$sql_config['group'] = $config['group'];
		}

        if($config['token'] != null) {
			$sql_config['where'][] = "token = '".$config['token']."'";
            $sql_config['first'] = true;
		}
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['user_id']."'";
		} elseif(isset($config['user_id'])) {
			$sql_config['where'][] = "product_id = '".$config['product_id']."'";
		} else {
			$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		}

		return $zulu->table_data($this->SQL_table_product_location,$id,$sql_config);
	}
	function location_set_main($id) {
		global $class_user,$class_setting;

		$query = "UPDATE ".$this->SQL_table_product_location." SET main = 0 WHERE user_id = '".$class_user->authorised->id."'";
		if($this->db->query($query)) {
			$query = "UPDATE ".$this->SQL_table_product_location." SET main = 1 WHERE id = '".$id."' AND user_id = '".$class_user->authorised->id."'";
			if($this->db->query($query)) {
				$class_setting->setting_edit('stock_location_default',$id);
				$class_setting->construct(['cache_clear'=>true]);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/*------ Stock Location END ------*/
}
