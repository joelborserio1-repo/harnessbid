<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: WEBSITE MENU
class website_menu {

	function __construct($config=[]) {
			global $db, $class_website;
			$this->db = $db;
	        $this->data = new stdClass();
	        $this->object_types = $this->category = new stdClass();

		$this->object_types->options = [
			'page'=>
				[
					'title'=>'Page',
					'data_table'=>'post',
					'data_where'=>["type = 'page'","status = 'published'"],
					'adm_edit'=>["file"=>'post'],
				],
			'news'=>
				[
					'title'=>'News',
					'data_table'=>'post',
					'data_where'=>["type = 'news'","status = 'published'"],
					'adm_edit'=>["file"=>'post'],
				],
			'gallery'=>
				[
					'title'=>'Gallery',
					'data_table'=>'post',
					'data_where'=>["type = 'gallery'","status = 'published'"],
					'adm_edit'=>["file"=>'post'],
				],
			'gallery_category'=>
				[
					'title'=>'Gallery Category',
					'data_table'=>'post',
					'data_where'=>["type = 'gallery_category'","status = 'published'"],
					'adm_edit'=>["file"=>'post'],
				],
			'default'=>
				[
					'title'=>'Default Page',
				],
			'category'=>
				[
					'title'=>'Category',
					'data_table'=>'product',
					'data_where'=>["type = 'category'","status = 1"],
					'data_label'=>'name',
					'adm_edit'=>["file"=>'product'],
				],
			'product'=>
				[
					'title'=>'Product',
					'data_table'=>'product',
					'data_where'=>["type = 'product'","status = 1","type_variant IN (0,1)"],
					'data_label'=>'name',
					'adm_edit'=>["file"=>'product'],
				],
		];
		$this->category->level = 99;
		$this->category->vl = 1;
		$this->category->tab = "--";
		$this->object_type_static = [
			'default'	=>	[ //default objects
				0	=>	['url'=>'browse/','label'=>'Shop - Home','web_label'=>"Shop Online"],
                9	=>	['url'=>'brands/','label'=>'Shop - Brands','web_label'=>"Our Brands"],
				1	=>	['url'=>'checkout/basket/','label'=>'Shopping Basket','web_label'=>"My Basket"],
				2	=>	['url'=>'checkout/index/','label'=>'Checkout','web_label'=>"Checkout"],
				3	=>	['url'=>'members/','label'=>'Members Account','web_label'=>"My Account"],
			],
		];
        if($class_website->config->program != null && $class_website->config->program != 'ZULUSHP' && defined('MASTER_mode') && MASTER_mode != 'main') {
            unset($this->object_types->options['category'],$this->object_types->options['product'],$this->object_type_static['default'][0],$this->object_type_static['default'][1],$this->object_type_static['default'][2],$this->object_type_static['default'][3],$this->object_type_static['default'][9]);
        }
	}

	function menu_data($config=[]) {
		global $class_post;
		$arr = ['type'=>'menu'];
		if($config['id']>0) {
			$arr['id'] = $config['id'];
		}
		$data = $class_post->post_data($arr);
		return $data;
	}

	function menu_item_data($config=[]) {
		global $class_post;
		$arr = ['type'=>'menu_item'];
		if($config['id']>0) {
			$arr['id'] = $config['id'];
		}
		$data = $class_post->post_data($arr);

		return $data;
	}

	function object_select($selected="") {
		foreach($this->object_types->options as $key=>$val) {
			$options .= "<option value=\"".$key."\" ".($key==$selected?"selected":NULL).">".$val."</option>";
		}
		$select = "<select name=\"object\" id=\"object\"><option></option>".$options."</select>";

		return $select;
	}
	function table_name($object) {
		switch($object) {
			case 'page':
				$return = array('table'=>'sh_pages','id'=>'pid');
				break;
			case 'category':
				$return = array('table'=>'sh_category','id'=>'cid');
				break;
			case 'product':
				$return = array('table'=>'sh_product','id'=>'pid');
				break;
		}
		return $return;
	}
	function category_option($selected=0,$pos="") {
		$pos = ($pos?$pos:NULL);
		$query = "SELECT * FROM sh_category WHERE status='1' AND parent=".($pos==NULL?"0":$pos)." ORDER BY title ASC";

		$res = $this->db->query($query);

		while($row = $res->fetch_array()) {
			$res_child = $this->db->query("SELECT * FROM sh_category WHERE status='1' AND parent='".$row[0]."'");
			$has_kids = $res_child->fetch_array() != NULL;

			$output .= "<option value=\"$row[0]\" ".($row[0]==$selected?"selected":NULL).">";

			if($pos!='') {
				for ($i=0; $i<$this->category->vl; $i++) $output .= $this->category->tab;
			}

			$output .= "$row[1]</option>\n";

			//If the category has sub-categories
			if ($has_kids) {
				$this->category->_vl++;
				$output .= $this->category_option($selected,$row[0]); // recursive call
				$this->category->_vl--;
			}
		}
		return $output;
	}
	function tab_title($id) {
		$data = table_data('sh_menu_option',$id);
		if($data['title']!=NULL) {
			$title = stripslashes($data['title']);
		} else {
			$table_info = $this->table_name($data['object']);
			$object_data = table_data($table_info['table'],$data['object_id'],array("primary_field"=>$table_info['id']));
			$title = stripslashes($object_data['title']);
		}
		return $title;
	}
	function page_link($id) {
		$data = table_data('sh_pages',$id,array("primary_field"=>"pid"));
		if($data['sub_menu']==0) {
			$link .= $data['ht_title']."/";
		} else {
			$link .= $this->page_link($data['sub_menu']);
		}
		return $link;
	}
	function category_link($id) {
		$data = table_data('sh_category',$id,array("primary_field"=>"cid"));
		$link = $data['ht_title']."/";
		return $link;
	}
	function root_category_ht($id) {
		$data = table_data('sh_category',$id,array("primary_field"=>"cid"));
		$cats = explode('/',$data['ht_title']);
		return $cats[0];
	}
	function product_link($id) {
		$data = table_data('sh_category',$id,array("primary_field"=>"cid"));
		$link = $data['ht_title']."/";
		return $link;
	}
	function tab_link($id) {
		global $main_URL_REL;
		$data = table_data('sh_menu_option',$id);
		if($data['link']!=NULL) {
			$link = $data['link'];
		} else {
			switch($data['object']) {
				case 'page':
					$link = $this->page_link($data['object_id']);
					break;
				case 'category':
					$link = "category/".$this->category_link($data['object_id']);
					break;
				case 'product':
					$link = "product/".$this->product_link($data['object_id']);
					break;
			}
			$link = $main_URL_REL.$link;
		}
		return $link;
	}
	function tab_has_children($id) {
		$tab_data = table_data('sh_menu_option',0,array('where'=>array("parent_id='".$id."'")));
		if(count($tab_data) > 0) return true;
		else return false;
	}
	function menu_items($config=[]) {
		global $class_post;
		if(isset($config['status'])) {
			$status = $config['status'];
		} else {
			$status = 'published';
		}
		return $class_post->post_data(['type'=>'menu_item','status'=>$status,'parent_id'=>$config['id']]);
	}
	function menu_id_from_tag($tag) {
		global $class_setting;
		return $class_setting->value_with_prefix('ws_menu_',$tag);
	}
	function build($ident, $config=[]) {
		global $zulu;

		if(is_numeric($ident)) {
			$id = $ident;
			$menu_data = $this->menu_data(['id'=>$id]);
		} else {
			$menu_data = $this->menu_data(['id'=>$this->menu_id_from_tag($ident)]);
			$id = $menu_data['id'];
		}

		if($id<=0) {
			return false;
		}

        $mobile = (isset($config['mobile'])&&$config['mobile']?true:false);
		$tab_data = $this->menu_items(['id'=>$id]);
        $menu_tabs = "";
		foreach($tab_data as $tab) {

			if(!$this->menu_tab_visible($tab)) {
				continue;
			}

			//-- parent
            $drop = false;
			$menu_sub_tabs_i = 0;
            $menu_sub_tabs = "";
			$tab_sub_data = $this->menu_items(['id'=>$tab['id']]);
			foreach($tab_sub_data as $tab_sub) {

				if(!$this->menu_tab_visible($tab_sub)) {
					continue;
				}
                $drop = true;

                $menu_link = $this->menu_link($tab_sub['id']);
				$menu_sub_tabs_i++;
				$menu_sub_tabs .= "
                <li class=\"tab-".$tab_sub['id']." ".($tab_sub['_meta']['icon']!=NULL?"has-icon":NULL)."\">
                    <a ".($config['schema']?"itemprop=\"url\"":NULL)." ".($menu_link!=NULL?"href=\"".$menu_link."\"":NULL)." ".($tab_sub['_meta']['redirect_location']!=NULL?"target=\"".$tab_sub['_meta']['redirect_location']."\"":NULL).">
                        ".($tab_sub['_meta']['icon']!=NULL?"<span class=\"".strtolower($tab_sub['_meta']['icon'])."\"></span> ":NULL)."
                        ".$this->menu_title($tab_sub['id'])."
                    </a>
                </li>";
			}
			if($drop) {
				$menu_sub_tabs = "<ul class='menu-dropdown'>".$menu_sub_tabs."</ul>";
			}

			//-- menu tabs
            $menu_link = $this->menu_link($tab['id']);
			$menu_tabs .= "
            <li class=\"rlink tab-".$tab['id']." ".($tab['_meta']['icon']!=NULL?"has-icon":NULL)." ".($drop?"has-children":NULL)." child-count-".$menu_sub_tabs_i."\">
                <a class=\"menulink\" ".($config['schema']?"itemprop=\"url\"":NULL)." ".($menu_link!=NULL?"href=\"".$menu_link."\"":NULL)." ".($tab['_meta']['redirect_location']!=NULL?"target=\"".$tab['_meta']['redirect_location']."\"":NULL).">
                    ".($tab['_meta']['icon']!=NULL?"<span class=\"".strtolower($tab['_meta']['icon'])."\"></span> ":NULL)."
                    ".$this->menu_title($tab['id'])."
                    ".($drop?" <span class='fas fa-chevron-down dropdown-arrow'></span>":null)."
                </a>
                ".$menu_sub_tabs."
            </li>";
		}

		//Wrapper
		if(!$config['wrapper'] && isset($config['wrapper'])) {
			$wrapper = $menu_tabs;
		} else {
			if(count($config['wrapper']['class']) > 0) {
				$class = "class=\"".implode(" ",$config['wrapper']['class'])."\"";
			}
			$attrid = "id=\"".$config['attrid']."\"";
			$wrapper = "<ul ".($config['schema']?"itemscope=\"itemscope\" itemtype=\"https://schema.org/SiteNavigationElement\"":NULL)." {$class} {$attrid}>{$menu_tabs}</ul>";
		}

		//Admin button
		if($_SESSION['zl_user']['auth_type']=='user'&&$_SESSION['zl_user']['id']==$menu_data['user_id']) {
			$wrapper .= "<div class=\"cb plain cb-menu\"><span class=\"cb-link\"><a href=\"".$zulu->link_page('post',['query'=>['id'=>$menu_data['id'],'Action'=>'edit']],SECTION_path_admin)."\" target=\"".(defined('CRM_fe')?'_blank':'_parent')."\" class=\"button bt-outline\"><i class=\"far fa-pencil\"></i> Edit</a></span></div>";
		}

		return $wrapper;
	}
	function menu_title($id=0) {
		global $zulu,$class_user;

		if($id>0) {
			$data = $this->menu_item_data(['id'=>$id]);
		} else {
			$data = $this->data->row;
		}
			if($data['_meta']['object']=='custom') {
				return stripslashes(isset($data['title']) ? $data['title'] : '');
			}
			$object_info = (isset($this->object_types->options[$data['_meta']['object']]) ? $this->object_types->options[$data['_meta']['object']] : []);
			$object_static = (isset($this->object_type_static[$data['_meta']['object']]) ? $this->object_type_static[$data['_meta']['object']] : []);

			if((!isset($object_info['data_where']) || $object_info['data_where']==NULL) && (!isset($object_info['data_table']) || $object_info['data_table']==NULL) && count($object_static)>0) {
			$title = $object_static[$data['_meta']['object_id']]['web_label'];

			if(trim($data['title'])!=NULL) {
				$title = stripslashes($data['title']);
			}
			} else {
				$conf = ['first'=>true,'where'=>(isset($object_info['data_where']) ? $object_info['data_where'] : [])];
			$conf['where'][] = "id = '".$data['_meta']['object_id']."'";
			$conf['where'][] = "user_id = '".$class_user->authorised->id."'";

				if(isset($object_info['data_table']) && $object_info['data_table'] != NULL) {
					$row = $zulu->table_data($object_info['data_table'],0,$conf);
					if($data['title']!=NULL) {
						$title = stripslashes($data['title']);
					} else {
						$title = stripslashes((isset($object_info['data_label'])&&$object_info['data_label']!=NULL?$row[$object_info['data_label']]:$row['title']));
					}
				} else {
					$title = stripslashes(isset($data['title']) ? $data['title'] : '');
				}
			}

		return $title;
	}
	function menu_link($id=0) {
		global $class_post,$class_product,$class_setting;

		if($id>0) {
			$data = $this->menu_item_data(['id'=>$id]);
		} else {
			$data = $this->data->row;
		}

		if($data['_meta']['object']=='custom') {
			$link = $data['_meta']['redirect_link'];
		} elseif(isset($this->object_type_static[$data['_meta']['object']])) {
			$object_static = $this->object_type_static[$data['_meta']['object']];
			$link = FE_url.$object_static[$data['_meta']['object_id']]['url'];
		} else  {
			switch($data['_meta']['object']) {
				case 'page':
					$link = $class_post->post_url($data['_meta']['object_id']);
					if($data['_meta']['object_id']==$class_setting->data['ws_site_homepage']) {
						$link = FE_url;	//override & set homepage
					}
				break;
				case 'category':
					$link = $class_product->category_url_front($data['_meta']['object_id']);
				break;
				case 'product':
					$link = $class_product->product_url($data['_meta']['object_id']);
				break;
				case 'gallery':
					$link = $class_post->post_url($data['_meta']['object_id']);
				break;
				case 'gallery_category':
					$link = $class_post->post_url($data['_meta']['object_id']);
				break;
			}
		}

		return $link;
	}
	function menu_tab_visible($data) {
		global $class_post;

		$this_tab = $data;
		$ret = true;

		if($this_tab['_meta']['object']=='page') { //--check posts status
			$stat = $class_post->post_status_raw($this_tab['_meta']['object_id']);

			if($stat!='published') {
				return false;
			}
		}

		return $ret;
	}
	function admin_link_label($id=0) {
		global $zulu;

		if($id>0) {
			$data = $this->menu_item_data(['id'=>$id]);
		} else {
			$data = $this->data->row;
		}
		$object_info = $this->object_types->options[$data['_meta']['object']];

		if($data['_meta']['object']=='custom') {
			$pre = "Custom";
			$link = "<code>".$data['_meta']['redirect_link']."</code>";
		} elseif($data['_meta']['object']=='default') {
			$pre = "Direct";
			$link = "<a href=\"".$this->menu_link()."\" target=\"_blank\">".$this->menu_title()."</a>";
		} else {
			$pre = $this->object_types->options[$data['_meta']['object']]['title'];
			$link = "<a href=\"".$zulu->link_page($object_info['adm_edit']['file'],['query'=>["Action"=>'edit','id'=>$data['_meta']['object_id']]])."\">".$this->menu_title()."</a>";
		}

		return $pre." link to ".$link;
	}
}
