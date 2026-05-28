<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//-- Class: POST
class post {

	public $SQL_table = 'post';
	public $SQL_meta_table = 'post_meta';
	public $SQL_version_table = 'post_version';

	function __construct($config=[]) {
		global $db,$class_file,$class_setting;
		$this->db = $db;
	        $this->config = new stdClass();
	        $this->vars = new stdClass();
	        $this->vars->data = new stdClass();
	        $this->pb_data = new stdClass();

		$this->config->version_filter_max = 5;
		$this->config->version_filter = ['id','token','user_id','type','stat_add','stat_update','timestamp']; //include latest fields

		$this->config->status = ['draft'=>'Draft','published'=>'Published','hidden'=>'Trash'];
		$this->config->file_rel = $class_file->file_root_rel."../";
		$this->config->placeholder = $this->config->file_rel."post/_placeholder.jpg";
        $this->config->duplicate_exclude_fields = ['id','token','user_id','team_id','slug','stat_add','stat_update','timestamp'];
		$this->config->template = [
			"news"	=>	[
				"name"			=>	"News",
				"name_plural"	=>	"News",
				"short"			=>	"Add news articles to your website giving people access to the latest updates.",
				"slug"			=>	"news",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	'news',
					"frontend_label"	=>	"News",
					"frontend_index"	=>	'news-index',
					"frontend_single"	=>	true,
					"frontend_fullwidth"=>	true,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>true],
					"version"			=>	true,
					"redirect"			=>	true,
					"meta"				=>	true,
					"sitemap"			=>	true,
					"slug_format"		=>	"{DATE}/{SLUG}",
					"index_filter"		=>	['join'=>' post_meta pm1 ON post.id = pm1.identifier','sort'=>'pm1.value DESC','where'=>["pm1.field = 'date'","pm1.value <= ".time()]],
					"searchable"		=>	true,
                    'sliderable'        =>  true,
                    'content_position'  =>  '',
				]
			],
			"faq"				=>	[
				"name"			=>	"FAQ",
				"name_plural"	=>	"FAQs",
				"short"			=>	"Add frequently asked questions to your website to help answer peoples common questions.",
				"slug"			=>	"faq",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	"FAQ's",
					"frontend_index"	=>	'faq-index',
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>false,"gallery"=>false],
					"version"			=>	true,
					"redirect"			=>	false,
					"meta"				=>	false,
					"sitemap"			=>	false,
                    'sliderable'        =>  false,
				]
			],
			"testimonial"		=>	[
				"name"			=>	"Testimonial",
				"name_plural"	=>	"Testimonials",
				"short"			=>	"Add testimonials to your website to promote your business from your clients comments!",
				"slug"			=>	"testimonials",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	"Testimonials",
					"frontend_index"	=>	'testimonial-index',
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>false],
					"version"			=>	true,
					"redirect"			=>	true,
					"meta"				=>	false,
					"sitemap"			=>	false,
                    'sliderable'        =>  true,
				]
			],
			"image"				=>	[
				"name"			=>	"Image",
				"name_plural"	=>	"Images",
				"short"			=>	"Images for post items.",
				"slug"			=>	"image",
				"config"		=>	[
					"frontend"			=>	false,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>false],
					"version"			=>	false,
					"redirect"			=>	true,
					"meta"				=>	false,
					"sitemap"			=>	false,
                    'sliderable'        =>  false,
				]
			],
			"gallery"			=>	[
				"name"			=>	"Gallery",
				"name_plural"	=>	"Galleries",
				"short"			=>	"Add image galleries to your website giving people access to the latest images.",
				"slug"			=>	"gallery",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	'gallery',
					"frontend_label"	=>	"Gallery",
					"frontend_index"	=>	'gallery-index',
					"frontend_single"	=>	true,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>true],
					"version"			=>	true,
					"redirect"			=>	false,
					"meta"				=>	true,
					"sitemap"			=>	true,
					'parent_meta'		=>	'gallery_category',
                    'sliderable'        =>  true,
                    'content_position'  =>  '',
				]
			],
			"gallery_category"	=>	[
				"name"			=>	"Gallery Category",
				"name_plural"	=>	"Gallery Categories",
				"short"			=>	"Add Categories to organize your Galleries into.",
				"slug"			=>	"gallery_category",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	'gallery-category',
					"frontend_label"	=>	"Gallery Category",
					"frontend_label_plural"	=>	"Gallery Categories",
					"frontend_index"	=>	'gallery-category-index',
					"frontend_single"	=>	true,
					"parent"			=>	false,
					"image"				=>	["main"=>false,"gallery"=>false],
					"version"			=>	true,
					"redirect"			=>	false,
					"meta"				=>	false,
					"sitemap"			=>	false,
                    'sliderable'        =>  true,
                    'content_position'  =>  '',
				]
			],
			"slider"			=>	[
				"name"			=>	"Slider",
				"name_plural"	=>	"Sliders",
				"short"			=>	"Add sliders to your website with images that rotate on the page.",
				"slug"			=>	"slider",
				"config"		=>	[
					"frontend"			=>	false,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>false,"gallery"=>true],
					"version"			=>	false,
					"redirect"			=>	true,
					"meta"				=>	false,
					"sitemap"			=>	false,
					"image_max_width"	=>	1920,
                    'sliderable'        =>  true,
				]
			],
			"menu"				=>	[
				"name"			=>	"Menu",
				"name_plural"	=>	"Menus",
				"short"			=>	"Add menus to your website that can be used by visitors for navigation.",
				"slug"			=>	"menu",
				"config"		=>	[
					"frontend"			=>	false,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>false,"gallery"=>false],
					"version"			=>	false,
					"redirect"			=>	false,
					"meta"				=>	false,
					"sitemap"			=>	false,
					"action_save"		=>	'index',
                    'sliderable'        =>  false,
				]
			],
			"menu_item"			=>	[
				"name"			=>	"Menu Item",
				"name_plural"	=>	"Menu Items",
				"short"			=>	"Add items to your menu for navigation.",
				"slug"			=>	"menu_item",
				"config"		=>	[
					"frontend"			=>	false,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>false],
					"version"			=>	false,
					"redirect"			=>	false,
					"meta"				=>	false,
					"sitemap"			=>	false,
					"action_save"		=>	'parent',
                    'sliderable'        =>  false,
				]
			],
			"content_block"		=>	[
				"name"			=>	"Content Block",
				"name_plural"	=>	"Content Blocks",
				"short"			=>	"Add content blocks to include in your website pages.",
				"slug"			=>	"content_block",
				"config"		=>	[
					"frontend"			=>	false,
					"frontend_tpl"		=>	false,
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	false,
					"parent"			=>	false,
					"image"				=>	["main"=>true,"gallery"=>false],
					"version"			=>	true,
					"redirect"			=>	false,
					"meta"				=>	false,
					"sitemap"			=>	false,
					"post_index"		=>	false,
					"frame"				=>	true,
					"slug_lock"			=>	true,
                    'sliderable'        =>  false,
					"admin"				=>	[
						'edit_parent_redir'	=>	false,
					]
				]
			],
			/* WARNING: PAGE TYPE MUST BE LAST IN ARRAY */
			"page"				=>	[
				"name"			=>	"Page",
				"name_plural"	=>	"Pages",
				"short"			=>	"Add pages to your website.",
				"slug"			=>	"",
				"config"		=>	[
					"frontend"			=>	true,
					"frontend_tpl"		=>	'default',
					"frontend_label"	=>	false,
					"frontend_index"	=>	false,
					"frontend_single"	=>	true,
					"parent"			=>	true,
					"image"				=>	["main"=>true,"gallery"=>false],
					"version"			=>	true,
					"redirect"			=>	true,
					"meta"				=>	true,
					"sitemap"			=>	true,
					"post_index"		=>	true,
					"frame"				=>	true,
					"post_builder"		=>	true,
                    "duplicate"         =>	true,
                    'sliderable'        =>  false,
				]
			],
		];

		$this->config->post_builder_layouts = [
            0   =>  ['label'=>'Main Options','layout'=>['12','6,6','4,4,4','3,3,3,3','8,4','4,8','3,9','9,3','6,3,3','3,3,6','3,6,3']],
            1   =>  ['label'=>'1 Column','layout'=>['12']],
            2   =>  ['label'=>'2 Columns','layout'=>['6,6','8,4','4,8','3,9','9,3','10,2','2,10','9-6,2-4','2-4,9-6','7-2,4-8','4-8,7-2']],
            3   =>  ['label'=>'3 Columns','layout'=>['4,4,4','6,3,3','3,3,6','3,6,3','8,2,2','2,8,2','2,2,8','6,4,2','6,2,4','4,6,2','4,2,6','2,6,4','2,4,6','4-8,4-8,2-4','4-8,2-4,4-8','2-4,4-8,4-8','7-2,2-4,2-4','2-4,7-2,2-4','2-4,2-4,7-2']],
            4   =>  ['label'=>'4 Columns','layout'=>['3,3,3,3','6,2,2,2','2,6,2,2','2,2,6,2','2,2,2,6','4,4,2,2','4,2,4,2','4,2,2,4','2,4,4,2','2,4,2,4','2,2,4,4','4-8,2-4,2-4,2-4','2-4,4-8,2-4,2-4','2-4,2-4,4-8,2-4','2-4,2-4,2-4,4-8']],
            5   =>  ['label'=>'5 Columns','layout'=>['2-4,2-4,2-4,2-4,2-4','4,2,2,2,2','2,4,2,2,2','2,2,4,2,2','2,2,2,4,2','2,2,2,2,4']],
            6   =>  ['label'=>'6 Columns','layout'=>['2,2,2,2,2,2']],
		];
        $this->config->post_builder_layout_nice = [
			'12'=>'Full','6'=>'<sup>1</sup>/<sub>2</sub>','4'=>'<sup>1</sup>&frasl;<sub>3</sub>','3'=>'<sup>1</sup>&frasl;<sub>4</sub>','8'=>'<sup>2</sup>&frasl;<sub>3</sub>','9'=>'<sup>3</sup>&frasl;<sub>4</sub>','2'=>'<sup>1</sup>&frasl;<sub>6</sub>','10'=>'<sup>5</sup>&frasl;<sub>6</sub>','2-4'=>'<sup>1</sup>&frasl;<sub>5</sub>','4-8'=>'<sup>2</sup>&frasl;<sub>5</sub>','7-2'=>'<sup>3</sup>&frasl;<sub>5</sub>','9-6'=>'<sup>4</sup>&frasl;<sub>5</sub>'
		];
		$this->config->post_builder_defaults = [
			'block_type'		=>	'text',
			'block_field_input'	=>	'input',
			'block_field_width'	=>	'6',
		];
		$this->config->post_builder_content_types = [
			'text',
			'button',
			'form',
			'image',
			'map',
			'gallery',
			'product',
			'product_showcase',
			'slider',
			'video',
            'accordion',
            'filetable',
		];

		$this->config->shortcode = array(
			"calc-age" => function($data){
				$content = "";
				//Calculate the age
				if(isset($data["day"], $data["month"], $data["year"])){
					$age = date("Y") - $data["year"];
					if(date("m") < $data["month"]){
						$age--;
					}
					if(date("m") == $data["month"] && date("d") < $data["day"]){
						$age--;
					}
					$content = $age;
				}
				return $content;
			},
			"post" => function($data){
				$content = "";
				if($data['id']>0){
					$id = $data['id'];
					$content = $id;
				}
				$post_data = $this->post_data(['id'=>$id]);
				$content = $this->post_content($post_data);
				return $content;
			}
		);
	}

	//$types_to_search is optional
	function post_search($search_string, $types_to_search, $limit_start ,$limit_end){
		//Check searchable post types
		$searchable_types = [];
		if(count($types_to_search)> 0){
			foreach($types_to_search as $type){
				if($this->config->template[$type]['config']['searchable']){
					$searchable_types[] = $key;
				}
			}
		}else{
			foreach($this->config->template as $key=>$template){
				if($template['config']['searchable']){
					$searchable_types[] = "'".$key."'";
				}
			}
		}
		//Full query
		$full_data = $this->post_data(['types'=>$searchable_types, 'search'=>(trim($search_string))]);
		//Limited query for pagination
		$data = $this->post_data(['types'=>$searchable_types, 'search'=>(trim($search_string)), 'limit'=>$limit_end, 'start'=>$limit_start]);
		return ['posts'=>$data, 'count'=>count($data), 'count_all'=>count($full_data), 'data_all'=>$full_data];
	}
	function post_get_current($id=0) {
		if($id==0) {
			$data = $this->vars->data;
		} else {
			$data = $this->post_data(['id'=>$id]);
		}
		return $data;
	}
	function post_status_raw($id) {
		global $zulu;
		$data = $zulu->table_data($this->SQL_table,$id,['field'=>['status']]);
		return $data['status'];
	}
	function post_url($id=0,$config=[]) {
		$post_data = $this->post_get_current($id);
		if(is_object($post_data)) {
			$type = $post_data->post_type;
			$post_data = (array)$post_data;
		} else {
			$type = $post_data['type'];
		}
		$folder = $this->config->template[$type]['slug'];
		//echo FE_url.($folder!=NULL?$folder.'/':NULL).$post_data['slug']."/".($config['version']!=NULL?"?version=".$config['version']:NULL)."<br>";
		return FE_url.($folder!=NULL?$folder.'/':NULL).$post_data['slug']."/".($config['version']!=NULL?"?version=".$config['version']:NULL);
	}
	function post_status($status) {
		if($status=='published') {
			$array['css'] = 'success';
			$array['label'] = $this->config->status['published'];
			$array['icon'] = 'check';
		} elseif($status=='draft') {
			$array['css'] = 'warning';
			$array['label'] = $this->config->status['draft'];
			$array['icon'] = 'pause';
		} else {
			$array['css'] = 'grey';
			$array['label'] = $this->config->status['hidden'];
			$array['icon'] = 'trash-alt';
		}
		return $array;
	}
	function post_data($config=array()) {
		global $class_user,$zulu;
		$config += [
			'id' => 0,
			'field' => array(),
			'slug' => NULL,
			'types' => NULL,
			'type' => NULL,
			'title' => NULL,
			'author_id' => 0,
			'branch_id' => 0,
			'parent_id' => NULL,
			'team_id' => 0,
			'user_id' => 0,
			'search' => NULL,
			'where' => array(),
			'first' => false,
			'limit' => NULL,
			'start' => NULL,
			'sort' => NULL,
			'join' => NULL,
			'counter' => false,
			'status' => NULL,
			'version' => NULL,
			'raw' => false,
			'meta' => true,
		];
		$sql_config = array();
		$version = false;
		$id = ($config['id']>0?$config['id']:0);

		if($config['field']!=NULL && (!is_array($config['field']) || count($config['field'])>0)) {
			$sql_config['field'] = $config['field'];
		}
		if($config['slug']!=NULL) {
			$sql_config['where'][] = "slug = '".$config['slug']."'";
			$single_full = true;
		}
		//$config['types'] is an array of types
		if($config['types']!=NULL) {
			$sql_config['where'][] = "type in (".implode(',',$config['types']).")";
		}
		if($config['type']!=NULL) {
			$sql_config['where'][] = "type = '".$config['type']."'";
		}
		if($config['title']!=NULL) {
			$config['where'][] = "title = '".$config['title']."'";
			$config['first'] = true;
		}
		if($config['type']!=NULL&&$config['slug']!=NULL) {
			$config['first'] = true;
		}
		if($config['author_id']>0) {
			$sql_config['where'][] = "author_id = '".$config['author_id']."'";
		}
		if($config['branch_id']>0) {
			$sql_config['where'][] = "branch_id = '".$config['branch_id']."'";
		}
		if($config['parent_id'] != NULL) {
			$sql_config['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['team_id']>0) {
			$sql_config['where'][] = "team_id = '".$config['team_id']."'";
		}
		if($config['user_id']>0) {
			$sql_config['where'][] = "user_id = '".$config['team_id']."'";
		}
		if($config['search']!=NULL) {
			$sql_config['where'][] = "(title LIKE '%".$config['search']."%' OR content LIKE '%".$config['search']."%')";
		}
		if(is_array($config['where'])) {
			foreach($config['where'] as $wval) {
				$sql_config['where'][] = $wval;
			}
		}
		if($config['first']) {
			$sql_config['first'] = true;
		}
		if($config['limit']>0) {
			$sql_config['limit'] = $config['limit'];
		}
		if($config['start']!=NULL) {
			$sql_config['start'] = $config['start'];
		}
		if($config['limit']!=NULL) {
			$sql_config['limit'] = $config['limit'];
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		}
		if($config['join']!=NULL) {
			$sql_config['join'] = $config['join'];
			$sql_config['field'] = ['*','post.id AS id'];
		}
		if($config['counter']) {
			$sql_config['field'] = ['post.id AS id'];
		}
		if($config['status']!=NULL) {
			if(is_array($config['status'])) {
				$sql_config['where'][] = "status IN('".implode("','",$config['status'])."')";
			} else {
				$sql_config['where'][] = "status = '".$config['status']."'";
			}
		}
		if($config['version']!=NULL) { //-- overrides any other paramater
			$version = true;
			$version_data = $this->version_data(0,['token'=>$config['version']]);
		}
		if($id>0) {
			$single_full = true;
		}
		if(!isset($sql_config['sort'])) {
			$sql_config['sort'] = 'sort ASC';
		}
		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		if(!$version) {
			$post = $zulu->table_data($this->SQL_table,$id,$sql_config);

			if($config['raw']) {
				//-- skip filtering
			} else {
				if($post['id']>0) {
					$this->vars->data = $post;
					$meta = $this->post_meta();

					$arr_data['frame'] = $meta['frame'];
					$arr_data['url'] = $this->post_url();
					$arr_data['template'] = $this->post_template($post['type']);
					if($post['author_id']>0) {
						$arr_data['author_name'] = $class_user->name(['id'=>$post['author_id']]);
					}

					//Main Compile
					$post['_root'] = $post;
					$post['_data'] = $arr_data;
					$post['_meta'] = $meta;

					$this->vars->data = $post;
				} else {
					foreach($post as $post_key=>$post_data) {
						$this->vars->data = $post_data;
						if(!isset($config['meta'])||(isset($config['meta'])&&$config['meta']==true)) {
							$meta = $this->post_meta();
						}

						$arr_data['frame'] = $meta['frame'];
						$arr_data['url'] = $this->post_url();
						$arr_data['template'] = $this->post_template($post_data['type']);
						$arr_data['author_name'] = $class_user->name(['id'=>$post_data['author_id']]);

						$post[$post_key]['_root'] = $post_data;
						$post[$post_key]['_data'] = $arr_data;
						$post[$post_key]['_meta'] = $meta;
						$table_total++;
					}
					$this->vars->table_total = $table_total;
				}
			}
		} else {
			$post = $this->version_decode($version_data);
		}
		return $post;
	}
	function post_image($id=0,$config=[]) {
		global $zulu,$class_file;
		$post_data = $this->post_get_current($id);

		$main_path = 'post/'.$post_data['token'].'/';
		$main_gallery = 'post/'.$post_data['token'].'/gallery/';

		$main_image = $main_path.$post_data['_meta']['image_main'];

		$return = [];
		if(file_exists($class_file->file_root."../".$main_image)&&$post_data['_meta']['image_main']!=NULL) {
			$return['main'] = ($config['base']!=NULL?$config['base']:NULL).$main_image;
			$return['file'] = $this->config->file_rel.$main_image;
		}

		if($this->config->template[$post_data['type']]['config']['image']['gallery']) {
			$post_gallery = $this->post_data(['type'=>'image','parent_id'=>$post_data['id']]);
			foreach($post_gallery as $gallery_item) {
				$image = $this->post_image($gallery_item['id']);
				if($image['main']!=NULL) {
					$retain_gallery[] = ['title'=>$gallery_item['title'],'image'=>($config['base']!=NULL?$config['base']:NULL).$image['main'],'post_id'=>$gallery_item['id']];
				}
			}
			$return['gallery']  = $retain_gallery;
		}

		return $return;
	}
	function post_content($data,$config=[]) {
		global $zulu;

		if(is_numeric($data)) {
			$data = $this->post_data(['id'=>$data]);
		}
		if($config['display']) {
			$data['type'] = $config['display'];
		}
		$id = $data['id'];
		if($data['type']=='gallery') {
			$post_image = $this->post_image($id);
			if(count($post_image['gallery'])>0) {

				//-- Gallery cols
				$col_count = ($config['gallery']['row']>0?$config['gallery']['row']:4);
				if($col_count>4) {
					$thumb_query = "w=400&h=300";
				} else {
					$thumb_query = "w=600&h=400";
				}

				//-- Gallery crop
				$crop = ($config['gallery']['crop']?true:false);
				if($crop) {
					$thumb_query .= "&zc=c&bg=ffffff";
				} else {
					$thumb_query .= "&far=1&bg=ffffff";
				}

				$content .= "<div class=\"image-container col".$col_count." coltable\">";
				foreach($post_image['gallery'] as $image) {
					$link = $this->config->file_rel.$image['image'];
					$image_meta = $this->post_meta($image['post_id']);
					if($image_meta['redirect_link'] != NULL) {
						$link = $image_meta['redirect_link'];
					}
					$content .= "<div class=\"col\"><div class=\"image-wrap\"><a title=\"".$image['title']."\" rel=\"image\" class=\"fancybox-image\" href=\"".$link."\" ".($image_meta['redirect_location']?"target=\"".$image_meta['redirect_location']."\"":NULL)."><img src=\"".$zulu->thumb("file/".$image['image'],$thumb_query)."\" alt=\"".$image['title']."\" /></a></div></div>";
				}
				$content .= "</div>";
			}
		} else {
			$content = stripslashes($data['content']);
		}

		return $content;
	}
	function post_filters($id=0) {
		global $zulu;
		$post_data = $this->post_get_current($id);
	}
	function post_template($type) {
		return ($this->config->template[$type]['config']['frontend_tpl']!=NULL?$this->config->template[$type]['config']['frontend_tpl']:'default');
	}
	function post_status_change($id,$status='draft') {
		global $class_user;

		$query = "UPDATE ".$this->SQL_table." SET status = '{$status}' WHERE id = '".$id."' AND user_id='".$class_user->authorised->id."'";
		if(!$this->db->query($query)) {
			return false;
		} else {
			return true;
		}
	}
	function post_image_delete($id=0) {
		global $class_file;
		$post_data = $this->post_image($id);
		@unlink($class_file->file_root."../".$post_data['main']);
	}
	function post_delete($id,$delete=false) {
		global $class_user,$class_cache;
		if($delete) {
			$query = "DELETE FROM ".$this->SQL_table." WHERE id = '".$id."' AND user_id='".$class_user->authorised->id."'";
		} else {
			$query = "UPDATE ".$this->SQL_table." SET status = 'hidden' WHERE status = 'published' AND id = '".$id."' AND user_id='".$class_user->authorised->id."'";
			$query2 = "DELETE FROM ".$this->SQL_table." WHERE status = 'draft' AND id = '".$id."' AND user_id='".$class_user->authorised->id."'";
		}
		$this->post_image_delete($id);

		if($this->db->query($query)) {
			if($query2!=NULL) {
				$this->db->query($query2);
			}
			return true;
		} else {
			return false;
		}
	}
	function set_status($id,$publish=true) {
		return $this->post_status_change($id,($publish?'published':'hidden'));
	}
	function post_new($config=array()) {
		global $class_user,$zulu,$class_file;
		$data['user_id'] = $class_user->authorised->id;
		$data['team_id'] = $class_user->authorised->child_id;
		$data['stat_add'] = time();
		$data['stat_update'] = time();
		$data['title'] = 'Untitled Post';
		$data['status'] = 'draft';
		$data['type'] = $config['type'];
		$data['token'] = $zulu->serial(8);

		foreach($data as $key=>$va) {
			$field[] = $key;
		}

		$query = "INSERT INTO ".$this->SQL_table." ".$this->db->build(2,$field,$data);
		if($this->db->query($query)) {
			$this->vars->post_new_token = $data['token'];

			//-- image dir
			$main_path = 'post/'.$data['token'].'/';
			@mkdir($class_file->file_root."/../".$main_path);

			return array("success"=>true,"id"=>$this->db->insert_id);
		} else {
			return array("success"=>false,"id"=>0);
		}
	}
    function post_edit($id,$config=[],$meta=[]) {
		global $class_user,$class_cache,$zulu;

        $version_config = [];

		if($id<1) {
			$data = $this->post_new();
			$id = $data['id'];
			$new = true;
		} else {
            $new = false;
			$config['stat_update'] = time();
			$data_current = $this->post_data(['id'=>$id]);
		}

		if($config['slug'] == NULL && !isset($config['slug_ovr'])) {
			$config['slug'] = $zulu->slug($config['title']);
			$post_template = $this->config->template[$config['type']]['config'];
			if($post_template['slug_format']!=NULL) {
				$format = $post_template['slug_format'];
				$format = str_replace("{SLUG}",$config['slug'],$format);
				if(strstr($format,'{PARENT}')) {
					if(!isset($data_current)) {
						$data_current = $this->post_data(['id'=>$id]);
					}
					$parent_data = $this->post_data(['id'=>$data_current['parent_id']]);
					$format = str_replace("{PARENT}",$parent_data['slug'],$format);
				}
				if(strstr($format,'{DATE}')) {
					if($meta['date']>0) {
						$dt = date('d-m-Y',$meta['date']);
					} elseif($config['stat_add']>0) {
						$dt = date('d-m-Y',$config['stat_add']);
					}
					$format = str_replace("{DATE}",$dt,$format);
				}
				$config['slug'] = $format;
			}
		}
		unset($config['slug_ovr']);

        if(isset($config['version_parent_id'])) {
            $version_config['version_parent_id'] = $config['version_parent_id'];
            unset($config['version_parent_id']);
        }
        if(isset($config['version_status'])) {
            $version_config['version_status'] = $config['version_status'];
            unset($config['version_status']);
        }

        if(isset($config['content'])) {
            $config['content'] = str_replace(['\r\n','\r','\n'],'',$config['content']);
        }

		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}

		$query = "UPDATE ".$this->SQL_table." SET ".$this->db->build(1,$fields,$data)." WHERE id = '{$id}' AND user_id='".$class_user->authorised->id."'";
		if($this->db->query($query)) {

			//-- meta
			foreach($meta as $mkey=>$mval) {
                if(is_array($mval)) {
                    $curr_meta = $zulu->meta_value($this->SQL_table, $id, $mkey);
                    if(isset($curr_meta['id'])) {
                        $curr_meta = [$mkey=>[$curr_meta]];
                    }
                    foreach($curr_meta[$mkey] as $cmkey=>$cmval) {
                        if(!in_array($cmval['value'], $mval)) {
                            $zulu->meta_remove($this->SQL_table, $id, $mkey, $zulu->entity($cmval['value']));
                        }
                    }
                    foreach($mval as $mvalue) {
                        $zulu->meta_update($this->SQL_table, $id, $mkey, $zulu->entity($mvalue), null, 0, true);
                    }
                } else {
                    $zulu->meta_update($this->SQL_table, $id, $mkey, $zulu->entity($mval));
                }
			}

			//-- meta version
			if(!$new) {
				$this->version_new($data_current,$version_config);
			}

			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function post_meta($id=0) {
		global $zulu;

		$post_data = $this->post_get_current($id);
		$id = $post_data['id'];

		$post_meta = $zulu->meta_array($zulu->meta_value("post",$id), true);
		return $post_meta;
	}
	function post_frontend($id=0,$config=[]) {
		global $zulu;
		$data = new stdClass();

		$post_data = $this->post_get_current($id);
		if($post_data['_meta']['post_builder']) {
			$post_builder_row = $this->post_data(['type'=>'post_builder','parent_id'=>($post_data['id']>0?$post_data['id']:'0'),'status'=>'published','first'=>true]);
			$post_data['content'] = $this->post_builder_fe_html($post_builder_row['id']);
		} else {
			$post_data['content'] = $this->shortcode_parse(stripslashes($post_data['content']));
		}

		foreach($post_data as $key=>$val) {
			if(is_array($val)) {
				$data->{$key} = (object)$val;
			} else {
				$data->{$key} = $val;
			}
		}

		return $data;
	}
	function post_exists($config) {
		global $zulu,$class_user;

		$where = [];
		if($config['type']!=NULL) {
			$where[] = "type = '".$config['type']."'";
		}
		if($config['status']!=NULL) {
			$where[] = "status = '".$config['status']."'";
		}
		if($config['slug']!=NULL) {
			$where[] = "slug = '".$config['slug']."'";
		}
		if($config['user_id']>0) {
			$where[] = "user_id = '".$config['user_id']."'";
		} else {
			$where[] = "user_id = '".$class_user->authorised->id."'";
		}
		$sql_config['where'] = $where;
		$sql_config['field'] = 'id';

		$data_check = $zulu->table_data($this->SQL_table,0,$sql_config);
		if(count($data_check)>0) {
			return true;
		} else {
			return false;
		}
	}
	function shortcode_parse($content){

		foreach($this->config->shortcode as $key => $function){
			$dat = array();
			preg_match_all("/\[".$key." (.+?)\]/", $content, $dat);
			if(count($dat) > 0 && $dat[0] != array() && isset($dat[1])){
				$i = 0;
				$actual_string = $dat[0];
				foreach($dat[1] as $temp){
					$temp = explode(" ", $temp);
					$params = array();
					foreach ($temp as $d){
						list($opt, $val) = explode("=", $d);
						$params[$opt] = trim($val, '"');
					}
					$content = str_replace($actual_string[$i], $function($params), $content);
					$i++;
				}
			}
		}
		return $content;
	}
	function version_new($data,$config=[]) {
		global $zulu;
        if(isset($config['version_status'])) {
            $data['_root']['status'] = $config['version_status'];
        }
		$vd = ['post_id'=>$data['id'],'token'=>$zulu->serial(8),'data_post'=>serialize($data['_root']),'stat_add'=>time(),'data_post_meta'=>serialize($data['_meta'])];
		if($config['draft']||$config['preview']) {
			$vd['draft'] = 1;
			$this->db->query("DELETE ".$this->SQL_version_table." WHERE post_id = '".$data['id']."' AND draft = 1");
		}
        if(isset($config['version_parent_id'])) {
            $vd['parent_id'] = $config['version_parent_id'];
        }
		foreach($vd as $field=>$val) {
			$vf[] = $field;
		}
		$query = "INSERT INTO ".$this->SQL_version_table." ".$this->db->build(0,$vf,$vd);

		if($this->db->query($query)) {
			$this->vars->token = $vd['token'];
			$this->vars->version_id = $this->db->insert_id;
			$vcount = count($this->version_list($vd['post_id']));
			if($this->config->version_filter_max > 0 && $vcount > $this->config->version_filter_max) {
				$limit = $vcount - $this->config->version_filter_max;
				$this->db->query("DELETE FROM ".$this->SQL_version_table." WHERE post_id = '".$vd['post_id']."' ORDER BY id ASC LIMIT ".$limit);
			}
			return true;
		} else {
			return false;
		}
	}
	function version_data($id=0,$config=[]) {
		global $zulu;

		if($config['token']!=NULL) {
			$sql_where['where'][] = "token = '".$config['token']."'";
			$sql_where['first'] = true;
		}
		if($config['post_id']>0) {
			$sql_where['where'][] = "post_id = '".$config['post_id']."'";
		}
        if($config['parent_id']>0) {
			$sql_where['where'][] = "parent_id = '".$config['parent_id']."'";
		}
		if($config['draft']) {
			$sql_where['where'][] = "draft = 1";
		}
		if($config['latest']) {
			$sql_where['sort'] = "id DESC";
			$sql_where['first'] = true;
		}
		$version = $zulu->table_data($this->SQL_version_table,$id,$sql_where);

		return $version;
	}
	function post_type_include($type) {
		$file = "includes/config/post/".$type.".php";
		$path = dirname(__FILE__)."/../../".$file;

		if(file_exists($path)) {
			return $path;
		} else {
			return NULL;
		}
	}
	function version_decode($data) {

		$post = unserialize($data['data_post']);
		$meta = unserialize($data['data_post_meta']);

		$this->vars->data = $post;
		$arr_data['url'] = $this->post_url();
		$arr_data['template'] = $this->post_template($post['type']);
		$post['_root'] = $post;
		$post['_data'] = $arr_data;
		$post['_meta'] = $meta;
		$this->vars->data = $post;

		return $post;
	}
	function version_restore($token) {
		$version = $this->version_data(0,['token'=>$token]);
        $v_id = $version['id'];
		$post_id = $version['post_id'];

		$v_data = unserialize($version['data_post']);
		$v_meta = unserialize($version['data_post_meta']);

		foreach($v_data as $key=>$val) {
			if(is_numeric($key)) {
				unset($v_data[$key]);
			}
		}

		foreach($this->config->version_filter as $filter) {
			unset($v_data[$filter]);
		}

		if($this->post_edit($post_id,$v_data,$v_meta)) {

            if($v_id > 0) {
                $child_v_data = $this->version_data(0,['parent_id'=>$v_id]);
                if(count($child_v_data) > 0) {
                    foreach($child_v_data as $child_v_row) {
                        $this->version_restore($child_v_row['token']);
                    }
                }
            }

			return true;
		} else {
			return false;
		}
	}
	function version_delete($config=[]) {
		if($config['token']!=NULL) {
			$where[] = "token = '{$config['token']}'";
		} else {
			$where[] = "id = '{$config['id']}'";
		}
		if($config['post']>0) {
			$where[] = "post_id = '{$config['post']}'";
		}
		if(count($where)<=0) {
			return false;
		} else {
			$query = "DELETE FROM ".$this->SQL_version_table." WHERE ".implode(" AND ",$where);
			return ($this->db->query($query)?true:false);
		}
	}
	function version_list($id) {
		global $zulu;

		$version = $zulu->table_data($this->SQL_version_table,0,['field'=>['id','stat_add','draft','token'],'where'=>['post_id = '.$id],'sort'=>'id DESC']);
		return $version;
	}
	function frame_array() {
		global $class_file,$class_user;
		$path = DOC_root.'/'.$class_user->authorised->file_web_path."frames/";
		@mkdir($path);
		$frame = glob($path."*");
		foreach($frame as $f) {
			if(is_file($f)) {
				$file = basename($f);
				$ret[$file] = $file;
			}
		}

		return $ret;
	}
	function slug_check($slug,$config=[]) {
		global $zulu;

		$slug = rtrim($slug,'/');
		if($config['type'] == NULL) {
			$config['type'] = 'page';
		}
		/*if($slug == 'index' || $slug == '') {
			return '';
		}*/
		$check = $this->post_data(['slug'=>$slug.($config['count']>0?"-".$config['count']:NULL),'type'=>$config['type']]);
		if($check['id']>0 && $check['id'] != $config['id']) {
			$config['count'] += 1;
			return $this->slug_check($slug,$config);
		}

		$slugret = $slug.($config['count']>0?"-".$config['count']:NULL);

		if($config['prefix']!=NULL) {
			$slugret_split = split("/",$slugret);
			if($config['prefix']=='{DATE}') { //-- default DATE uses meta date field
				$config['prefix'] = $_POST['meta']['date'];
			}
			$config['prefix'] = str_replace(['/','_'],'-',$config['prefix']);
			if($slugret_split[0]!=$config['prefix']) {
				$prefix = $config['prefix']."/";
			}
		}
		return $prefix.$slugret;
	}
	function cb($tag,$config=[]) {
		global $post_data,$class_user,$zulu;

		if($config['post_id']>0) {
			$post_id = $config['post_id'];
		} elseif($post_data['id']>0) {
			$post_id = $post_data['id'];
		} else {
			$fail = true;
		}
		$block_data = $this->post_data(['parent_id'=>$post_id,'type'=>'content_block','slug'=>"cb-".$post_id."-".$tag]);
		$toggle_edit = false;

		if(count($block_data)<=0) {
			$tag_title = ucwords(str_replace("_"," ",$tag));
			$arr = ['parent_id'=>$post_id,'status'=>'published','type'=>'content_block','slug'=>"cb-".$post_id."-".$tag,'title'=>$tag_title];
			$this->post_edit(0,$arr);
			$block_data = $this->post_data(['parent_id'=>$post_id,'type'=>'content_block','slug'=>"cb-".$post_id."-".$tag]);
		}

		$content = $this->post_content($block_data);
		if((trim($content)==NULL&&$config['default']!=NULL)||($config['default']!=NULL&&(WEBSITE_cb_force_default||$_GET['dev_force']>0))||($config['default']!=NULL&&$config['default_force'])) {
			$this->post_edit($block_data['id'],['content'=>$config['default'],'slug_ovr'=>true]);
			$content = $config['default'];
		}

		//-- User logged in and GET dev_edit or SETTING cb_force is true
		if($_SESSION['zl_user']['auth_type']=='user'&&$_SESSION['zl_user']['id']==$block_data['user_id']&&(!WEBSITE_cb_force_default||$_GET['dev_edit']==1)) {
			$toggle_edit = true;
		}

		//-- Use logged in and
		if($toggle_edit===true&&WEBSITE_cb_disable) {
			$toggle_edit = false;
		}

		//-- HTML output
		if($toggle_edit) {
			$html = "
			<div class=\"cb\">
			<div class=\"cb-title\">".$block_data['title']." <span class=\"cb-link\"><a href=\"".$zulu->link_page('post',['query'=>['id'=>$block_data['id'],'Action'=>'edit']],SECTION_path_admin)."\" target=\"".(defined('CRM_fe')?'_blank':'_parent')."\" class=\"button bt-outline\"><i class=\"far fa-pencil\"></i> Edit</a></span></div>
			<div class=\"cb-content\">".$content."</div>
			</div>";
		} else {
			$html = $content;
		}

		if(!$fail) {
			return $html;
		} else {
			return false;
		}
	}

    function post_duplicate($id, $config=[]) {
        global $class_file;

        $post_row = $this->post_data(['id'=>$id]);
        if($post_row['id'] > 0) {
            $dup_data = [];
            foreach($post_row['_root'] as $key=>$val) {
                if(!is_numeric($key) && !in_array($key,$this->config->duplicate_exclude_fields)) {
                    $dup_data[$key] = $val;
                }
            }
            if(isset($config['post_data']) && isset($config['post_data'][$post_row['id']])) {
                foreach($config['post_data'][$post_row['id']] as $dkey=>$dval) {
                    if($dkey != 'meta') {
                        $dup_data[$dkey] = $dval;
                    } else {
                        foreach($dval as $dmkey=>$dmval) {
                            $post_row['_meta'][$dmkey] = $dmval;
                        }
                    }
                }
            }
            if(!isset($config['no_copy']) || !$config['no_copy']) {
                $dup_data['title'] .= " Copy";
            }
            if(isset($config['parent_id'])) {
                $dup_data['parent_id'] = $config['parent_id'];
                if(isset($config['child_id']) && !in_array($post_row['id'],$config['child_id'])) {
                    return;
                }
            }
            if(isset($config['sort'])) {
                $dup_data['sort'] = $config['sort'];
                unset($config['sort']);
            }
            if(isset($config['status'])) {
                $dup_data['status'] = $config['status'];
            }
            $result = $this->post_edit(0, $dup_data, $post_row['_meta']);
            if($result['success']) {
                $new_row = $this->post_data(['id'=>$result['id']]);
                $new_path = $class_file->file_root.'../post/'.$new_row['token'].'/';
                $file_data = @glob($class_file->file_root.'../post/'.$post_row['token'].'/*');
                foreach($file_data as $file) {
                    @copy($file,$new_path.basename($file));
                }
                if(!isset($config['skip_children']) || !$config['skip_children']) {
                    $child_data = $this->post_data(['parent_id'=>$post_row['id']]);
                    foreach($child_data as $child_row) {
                        $this->post_duplicate($child_row['id'], array_merge($config, ['parent_id'=>$result['id'], 'no_copy'=>true]));
                    }
                }
                $return = ['success'=>true,'id'=>$result['id']];
            } else {
                $return = ['success'=>false,'msg'=>'Unable to duplicate this post.'];
            }
        } else {
            $return = ['success'=>false,'msg'=>'Unable to duplicate this post.'];
        }

        return $return;
    }

	/*-- POST BUILDER FUNCTIONS --*/
	function post_builder_admin_html($id) {
		global $zulu,$form_edit,$class_product,$class_file,$class_website;

		$post_builder_row = $this->post_data(['id'=>$id]);

        $sections = $this->post_data(['parent_id'=>$id,'type'=>'post_builder_section','status'=>'published','sort'=>'sort ASC, id ASC']);
		if(count($sections) <= 0) {
			$section_add = $this->post_edit(0,['parent_id'=>$id,'type'=>'post_builder_section','status'=>'published','title'=>'Section']);
            $row_add = $this->post_edit(0,['parent_id'=>$section_add['id'],'type'=>'post_builder_row','status'=>'published','title'=>'Row']);
			$column_add = $this->post_edit(0,['parent_id'=>$row_add['id'],'type'=>'post_builder_column','status'=>'published','title'=>'Column']);
            $sections = $this->post_data(['parent_id'=>$id,'type'=>'post_builder_section','status'=>'published','sort'=>'sort ASC, id ASC']);
		}
		$section_html = "";
		foreach($sections as $section) {
			$section_html .= $this->post_builder_admin_section_html($section['id'], ['data'=>$section,'wrapper'=>true]);
		}

		$modal_layout_html = $modal_layout_tabs = "";
        foreach($this->config->post_builder_layouts as $col_count=>$col_options) {
            $modal_layout_options = "";
            foreach($col_options['layout'] as $layout) {
                $cols = explode(',',$layout);
                $col_html = "";
                foreach($cols as $col) {
                    $col_html .= "<div class='col-md-".$col."'><div class='layout-part'>".$this->config->post_builder_layout_nice[$col]."</div></div>";
                }
                $modal_layout_options .= "<div class='col-sm-6 col-md-4'><div class='row layout-section' data-layout='".$layout."'>".$col_html."</div></div>";
            }
            if($modal_layout_options != "") {
                $modal_layout_tabs .= "<li class='".($col_count==0?'active':null)."'><a class='pb-modal-section-tab' data-tab='".$col_count."' href='#'>".$col_options['label']."</a></li>";
                $modal_layout_html .= "<div class='pb-modal-section-tab-block".($col_count!=0?' hide':null)."' data-tab='".$col_count."'>".$modal_layout_options."</div>";
            }
		}

		$modal_type_html = "";
		foreach($this->config->post_builder_content_types as $val) {

            @include_once MAIN_path."includes/post-builder/".$val.".php";
            $block_class = "pb_".$val;
            if(!class_exists($block_class)) {
                continue;
            }
            $block_obj = new $block_class();
            if(isset($block_obj->shop_only) && $block_obj->shop_only && $class_website->config->program != 'ZULUSHP' && MASTER_mode != 'main') {
                continue;
            }

            $modal_type_html .= "<div class='col-md-4'>
                <div class='content-type-section' data-type='".$val."' data-label='".$block_obj->title."'>
                    ".($block_obj->icon!=NULL?$zulu->icon($block_obj->icon, 's', ['fa-lg'])." ":NULL)."
                    <span class='content-type-text'>".$block_obj->title."</span>
                </div>
            </div>";
		}

		$html = "
		<div class='post-builder'>
			<div class='pb-section-container'>".$section_html."</div>
            <div class=\"row\">
                <div class=\"col-md-12 text-center pb-section-add-wrap\">
                    <a href='#' class='pb-section-add btn btn-primary btn-md'><i class='fas fa-plus-square'></i> Add New Section</a>
                </div>
            </div>
            <div id='pb-trash' class='hide'></div>
			<div id='modal-section-edit' class='modal fade modal-section-edit' role='dialog' data-section=''>
				<div class='modal-dialog modal-lg'>
					<div class='modal-content'>
						<div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Section Configuration</h4></div>
						<div class='modal-body'><div class='pb-section-edit-container'>".(isset($this->pb_data->section_edit_block)?implode('',$this->pb_data->section_edit_block):null)."</div></div>
						<div class='modal-footer'>
                            <button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>
							<button type='button' class='btn btn-success btn-save-modal section-content-save' data-dismiss='modal'><i class='fas fa-save'></i> Save</button>
						</div>
					</div>
				</div>
			</div>
            <div id='modal-structure' class='modal fade modal-structure' role='dialog' data-section='' data-row=''>
				<div class='modal-dialog modal-lg'>
					<div class='modal-content'>
						<div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Row Structure</h4></div>
						<div class='modal-body'><div class='row'><div class='col-lg-12'><ul class='nav nav-tabs pb-modal-section-tabs'>".$modal_layout_tabs."</ul></div>".$modal_layout_html."</div></div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>
						</div>
					</div>
				</div>
			</div>
            <div id='modal-row-edit' class='modal fade modal-row-edit' role='dialog' data-row=''>
				<div class='modal-dialog modal-lg'>
					<div class='modal-content'>
						<div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Row Configuration</h4></div>
						<div class='modal-body'><div class='pb-row-edit-container'>".(isset($this->pb_data->row_edit_block)?implode('',$this->pb_data->row_edit_block):null)."</div></div>
						<div class='modal-footer'>
                            <button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>
							<button type='button' class='btn btn-success btn-save-modal row-content-save' data-dismiss='modal'><i class='fas fa-save'></i> Save</button>
						</div>
					</div>
				</div>
			</div>
            <div id='modal-content-type' class='modal fade modal-content-type' role='dialog' data-block=''>
				<div class='modal-dialog modal-lg'>
					<div class='modal-content'>
						<div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Content Type</h4></div>
						<div class='modal-body'><div class='row'>".$modal_type_html."</div></div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id='modal-content-edit' class='modal fade modal-content-edit' role='dialog' data-block=''>
				<div class='modal-dialog modal-lg'>
					<div class='modal-content'>
						<div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>Content Edit</h4></div>
						<div class='modal-body'><div class='pb-block-edit-container'>".(isset($this->pb_data->block_edit_block)?implode('',$this->pb_data->block_edit_block):null)."</div></div>
						<div class='modal-footer'>
                            <button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>
							<button type='button' class='btn btn-success btn-save-modal pb-block-content-save' data-dismiss='modal'><i class='fas fa-save'></i> Save</button>
						</div>
					</div>
				</div>
			</div>
		</div>";
        $zulu->template->jquery_redactor = true;

        $zulu->template->js_code[] = "
        var tpl_rel = '".TPL_rel."';
        var redactor_upload_path = '".$class_file->user_upload_path()."';
        ";

		$zulu->template->js_file['smart_find'] = TPL_rel."assets/smart.find.js";
		foreach($this->config->post_builder_content_types as $val) {
            if(file_exists(TPL_root."assets/post-builder/".$val.".js")) {
                $zulu->template->js_file['pb-'.$val] = TPL_rel."assets/post-builder/".$val.".js";
            }
        }

		return $html;
	}
    function post_builder_admin_section_html($section_id, $config=[]) {
        global $zulu, $class_file, $form_edit;

        $status = (isset($config['status'])?$config['status']:'published');
		if(isset($config['data'])) {
			$section = $config['data'];
		} else {
			$section = $this->post_data(['id'=>$section_id,'type'=>'post_builder_section','status'=>$status]);
		}
		$rows = $this->post_data(['parent_id'=>$section['id'],'type'=>'post_builder_row','status'=>$status,'sort'=>'sort ASC, id ASC']);
        if(count($rows) <= 0) {
            $row_add = $this->post_edit(0,['parent_id'=>$section['id'],'type'=>'post_builder_row','status'=>$section['status'],'title'=>'Row']);
            $rows = [$this->post_data(['id'=>$row_add['id']])];
		}
		$row_html = "";
		foreach($rows as $row) {
			$row_html .= $this->post_builder_admin_row_html($row['id'], ['data'=>$row,'wrapper'=>true,'status'=>$status,'ajax'=>(isset($config['ajax'])&&$config['ajax']?true:false)]);
		}
        if(isset($config['wrapper']) && $config['wrapper']) {
            $wrapper = true;
        } else {
            $wrapper = false;
        }

		$section_html = ($wrapper?"<div class='row pb-section ".($section['_meta']['hide']?"inactive":NULL)."' data-id='".$section['id']."'>":NULL)."
		<div class='col-md-12'>
            <div class='pb-container-header' title='Click and drag to re-order'>
                <div class='pb-container-header-left'>

                    <div class='pb-container-heading'>
					<i class='fas fa-ellipsis-v'></i><i class='fas fa-ellipsis-v'></i><i class='fas fa-ellipsis-v'></i>
					<a href='#' class='pb-container-toggle' title='Hide/Show'><span class='pb-container-section-title'>".$section['title']."</span></a>
					</div>

                    <div class='pb-container-options'>
					<a href='#' class='pb-section-configuration'><i class='fas fa-cog fa-lg' title='Update the settings for this section'></i></a><a href='#' class='pb-section-duplicate' title='Duplicate this section'><i class='fas fa-clone fa-lg'></i></a>
                    </div>

                </div>
                <div class='pb-container-header-right'>
                    <a href='#' class='pb-section-remove' title='Remove this section'><i class='fas fa-times fa-lg'></i></a>
                </div>
            </div>
			<div class='pb-container pb-row-container row'>
                ".$row_html."
                <button class='pb-row-add btn btn-default' type='button'><i class='fas fa-plus-square'></i> Add New Row</button>
            </div>

		</div>".($wrapper?"</div>":NULL);

        $has_image = ($section['_meta']['image_file']!=NULL?true:false);
        $timestamp = $zulu->serial(16);
        $queue_id = $zulu->serial(6);
        $class_file->form_data['image_file-'.$section['id']] = ['queue_id'=>$queue_id];

        if(!isset($this->pb_data->section_edit_block)) {
            $this->pb_data->section_edit_block = [];
        }
        $this->pb_data->section_edit_block[$section['id']] = "
        <div class='pb-section-edit-block' data-id='".$section['id']."' data-save='0'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class='form-group'>
                        <label>Section Name</label>
                        ".$form_edit->input_html('input','pb_post['.$section['id'].'][title]',$section['title'],['placeholder'=>'Enter a name for this section...','class'=>['pb-section-title']])."
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Theme Style</label>
                        ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][section_theme]',$section['_meta']['section_theme'],['option'=>[''=>'Default','variant-1'=>'Variant 1','variant-2'=>'Variant 2','variant-3'=>'Variant 3','variant-4'=>'Variant 4','variant-5'=>'Variant 5']])."
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Vertical Alignment</label>
                        ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][section_valign]',$section['_meta']['section_valign'],['option'=>[''=>'Top (Default)','center'=>'Center','end'=>'Bottom']])."
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Padding Level</label>
                        ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][section_padding]',(isset($section['_meta']['section_padding'])?$section['_meta']['section_padding']:1),['option'=>[0=>'None',1=>'Level 1 (Default)',2=>'Level 2',3=>'Level 3',4=>'Level 4',5=>'Level 5']])."
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Full Width</label>
                        ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][frame_full_width]',$section['_meta']['frame_full_width'],['option'=>[0=>'No',1=>'Yes']])."
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Custom CSS Class</label>
                        ".$form_edit->input_html('input','pb_post['.$section['id'].'][meta][css_class]',$section['_meta']['css_class'],['placeholder'=>'Separate with spaces...'])."
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label>Custom CSS ID</label>
                        ".$form_edit->input_html('input','pb_post['.$section['id'].'][meta][css_id]',$section['_meta']['css_id'],['placeholder'=>'Separate with spaces...'])."
                    </div>
                </div>
                <div class='col-md-12'>
                    <div class='form-group'>
                        <label>Custom Inline Style</label>
                        ".$form_edit->input_html('input','pb_post['.$section['id'].'][meta][css_style]',$section['_meta']['css_style'],['placeholder'=>'Custom CSS styling'])."
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='form-group'>
                        <label>Hide</label>
                        ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][hide]',$section['_meta']['hide'],['option'=>['0'=>'No','1'=>'Yes'],'class'=>['pb-section-hide']])."
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12'>
                    <h4>Custom Background Image</h4>
                </div>
                <div class='col-md-12'>
                    <div class='row'>
                        <div class='col-md-6'>
                            <div class='form-group'>
                                <label>Image</label>
                                <span class='opt opt-success".(!$has_image?' hide':null)."' id='image-upl-success'><i class='fas fa-check'></i> Image uploaded</span>
                                <span class='image-upl-wrapper'>".$class_file->uploadifive_input('image_file-'.$section['id'])."</span>
                            </div>
                        </div>
						<div class='col-md-6 image-container'>
                            <div class=\"image".(!$has_image?' hide':null)."\" data-path='".MAIN_rel."file/post/".$section['token']."/'>
                                <div class=\"ctrl\"><a href=\"#\" class=\"btn btn-danger btn-xs pb-section-image-delete\"><i class=\"fas fa-times\"></i></a></div>
                                <img src='".($has_image?MAIN_rel."file/post/".$section['token']."/".$section['_meta']['image_file']."?".time():null)."' alt='Section background image' />
                            </div>
                        </div>
                    </div>
                    ".$form_edit->input_html('hidden','pb_post['.$section['id'].'][meta][image_file]',$section['_meta']['image_file'],['id'=>'input-image-file-'.$section['id']])."
                </div>
                <div id='img-upl-field-".$section['id']."' class='section-image-settings".(!$has_image?' hide':null)."'>
                    <div class='col-md-4'>
                        <div class='form-group'>
                            <label>Background Repeat</label>
                            ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][bg_repeat]',$section['_meta']['bg_repeat'],['option'=>['repeat'=>'Repeat','no-repeat'=>'No Repeat','repeat-x'=>'Repeat X (Horizontal)','repeat-y'=>'Repeat Y (Vertical)']])."
                        </div>
                    </div>
                    <div class='col-md-4'>
                        <div class='form-group'>
                            <label>Background Size</label>
                            ".$form_edit->input_html('select','pb_post['.$section['id'].'][meta][bg_size]',$section['_meta']['bg_size'],['option'=>['auto'=>'Auto','100%'=>'Full (100%)','cover'=>'Cover','contain'=>'Contain']])."
                        </div>
                    </div>
                    <div class='col-md-4'>
                        <div class='form-group'>
                            <label>Background Position</label>
                            ".$form_edit->input_html('input','pb_post['.$section['id'].'][meta][bg_position]',$section['_meta']['bg_position'],['placeholder'=>'top, center, left, right'])."
                        </div>
                    </div>
                </div>
            </div>
            ".$form_edit->input_html('hidden','pb_post['.$section['id'].'][parent_id]',$section['parent_id'],['class'=>['pb-section-input-parent-id']])."
            ".$form_edit->input_html('hidden','pb_post['.$section['id'].'][sort]',$section['sort'],['class'=>['pb-section-sort']])."
            ".$form_edit->input_html('hidden','pb_post['.$section['id'].'][remove]','0')."
        ";

        $init_code = "
        $('#upl_image_file-".$section['id']."').uploadifive({
            'auto'			: true,
            'multi' 		: false,
            'queueSizeLimit': 1,
            'formData'      : {
                'action' 	: 'post_builder_image',
                'post_id' 	: '".$section['id']."',
                'file_name' : '',
                'path' 		: 'post/".$section['token']."/',
                'chk_time' 	: '".$timestamp."',
                'chk_serial': '".md5('ZuLu2000' . $timestamp)."',
            },
            'queueID'          : 'queue_".$queue_id."',
            'uploadScript'     : '".$class_file->vars->uploadifive->path_abs."upload.php',
            'onUploadComplete' : function(file, data) {
                var response = JSON.parse(data);
                section_image_upload(".$section['id'].", response.file_name);
            }
        });";
        if(!isset($config['ajax']) || !$config['ajax']) {
            $zulu->template->jquery[] = $init_code;
        } else {
            $this->pb_data->section_edit_block[$section['id']] .= "<script type=\"text/javascript\">".$init_code."</script>";
        }
        $this->pb_data->section_edit_block[$section['id']] .= "</div>";

		return $section_html;
	}
    function post_builder_admin_row_html($row_id, $config=[]) {
        global $zulu, $form_edit;

        $status = (isset($config['status'])?$config['status']:'published');
		if(isset($config['data'])) {
			$row = $config['data'];
		} else {
			$row = $this->post_data(['id'=>$row_id,'type'=>'post_builder_row','status'=>$status]);
		}
		$layout_arr = explode(',',$row['_meta']['row_layout']);
		$columns = $this->post_data(['parent_id'=>$row['id'],'type'=>'post_builder_column','status'=>$status,'sort'=>'sort ASC, id ASC']);
        if(count($columns) <= 0) {
            $column_add = $this->post_edit(0,['parent_id'=>$row['id'],'type'=>'post_builder_column','status'=>$row['status'],'title'=>'Column']);
            $columns = [$this->post_data(['id'=>$column_add['id']])];
		}
		$column_html = "";
		foreach($columns as $column) {
			$column_html .= $this->post_builder_admin_column_html($column['id'], ['data'=>$column,'wrapper'=>true,'status'=>$status,'ajax'=>(isset($config['ajax'])&&$config['ajax']?true:false)]);
		}

        $row_content = "<div class='pb-container pb-column-container row'>".$column_html."</div>";

        if(isset($config['content']) && $config['content']) {
            $row_html = $row_content;

        } else {
            if(in_array($row['_meta']['row_layout'],$this->config->post_builder_layouts[0]['layout'])) {
                $layout_tab = 0;
            } else {
                $layout_tab = count($layout_arr);
            }
            if(isset($config['wrapper']) && $config['wrapper']) {
                $wrapper = true;
            } else {
                $wrapper = false;
            }
            $row_html = ($wrapper?"<div class='pb-row ".($row['_meta']['hide']?"inactive":NULL)."' data-id='".$row['id']."'>":NULL)."
            <div class=''>
                <div class='pb-container-header' title='Click and drag to re-order'>
                    <div class='pb-container-header-left'>
                        <div class='pb-container-heading'><i class='fas fa-ellipsis-v'></i><i class='fas fa-ellipsis-v'></i><i class='fas fa-ellipsis-v'></i> <a href='#' class='pb-container-toggle' title='Hide/Show'><span class='pb-container-row-title'>".$row['title']."</span></a></div>

                        <div class='pb-container-options'>
                            <a href='#' class='pb-row-configuration'><i class='fas fa-cog fa-lg' title='Update the settings for this row'></i></a><a href='#' class='pb-row-structure' title='Change the column structure of the row' data-layout='".$row['_meta']['row_layout']."' data-tab='".$layout_tab."'><i class='fas fa-th fa-lg'></i></a><a href='#' class='pb-row-duplicate' title='Duplicate this row'><i class='fas fa-clone fa-lg'></i></a>
                        </div>
                    </div>
                    <div class='pb-container-header-right'>
                        <a href='#' class='pb-row-remove' title='Remove this row'><i class='fas fa-times fa-lg'></i></a>
                    </div>
                </div>
                ".$row_content."
            </div>".($wrapper?"</div>":NULL);

            if(!isset($this->pb_data->row_edit_block)) {
                $this->pb_data->row_edit_block = [];
            }
            $this->pb_data->row_edit_block[$row['id']] = "
            <div class='pb-row-edit-block' data-id='".$row['id']."' data-save='0'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='form-group'>
                            <label>Row Name</label>
                            ".$form_edit->input_html('input','pb_post['.$row['id'].'][title]',$row['title'],['placeholder'=>'Enter a name for this row...','class'=>['pb-row-title']])."
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label>Custom CSS Class</label>
                            ".$form_edit->input_html('input','pb_post['.$row['id'].'][meta][css_class]',$row['_meta']['css_class'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label>Custom CSS ID</label>
                            ".$form_edit->input_html('input','pb_post['.$row['id'].'][meta][css_id]',$row['_meta']['css_id'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class='col-md-12'>
                        <div class='form-group'>
                            <label>Custom Inline Style</label>
                            ".$form_edit->input_html('input','pb_post['.$row['id'].'][meta][css_style]',$row['_meta']['css_style'],['placeholder'=>'Custom CSS styling'])."
                        </div>
                    </div>
                    <div class='col-md-4'>
                        <div class='form-group'>
                            <label>Hide</label>
                            ".$form_edit->input_html('select','pb_post['.$row['id'].'][meta][hide]',$row['_meta']['hide'],['option'=>['0'=>'No','1'=>'Yes'],'class'=>['pb-row-hide']])."
                        </div>
                    </div>
                </div>
                ".$form_edit->input_html('hidden','pb_post['.$row['id'].'][parent_id]',$row['parent_id'],['class'=>['pb-row-input-parent-id']])."
                ".$form_edit->input_html('hidden','pb_post['.$row['id'].'][meta][row_layout]',$row['_meta']['row_layout'],['class'=>['pb-row-layout']])."
                ".$form_edit->input_html('hidden','pb_post['.$row['id'].'][sort]',$row['sort'],['class'=>['pb-row-sort']])."
                ".$form_edit->input_html('hidden','pb_post['.$row['id'].'][remove]','0',['class'=>['pb-row-input-remove']])."
            </div>
            ";

        }

		return $row_html;
	}
	function post_builder_admin_column_html($column_id, $config=[]) {
        global $form_edit;

        $status = (isset($config['status'])?$config['status']:'published');
		if(isset($config['data'])) {
			$column = $config['data'];
		} else {
			$column = $this->post_data(['id'=>$column_id,'type'=>'post_builder_column','status'=>$status]);
		}
        $column_width = (isset($config['column_width'])&&$config['column_width']!=null?$config['column_width']:($column['_meta']['column_width']!=null?$column['_meta']['column_width']:'12'));
        if(isset($config['block_html'])) {          // we don't check for null because there could be no blocks in the column
            $block_html = $config['block_html'];
        } else {
            $blocks = $this->post_data(['parent_id'=>$column['id'],'type'=>'post_builder_block','status'=>$status,'sort'=>'sort ASC, id ASC']);
            $block_html = "";
            foreach($blocks as $block) {
                $block_html .= $this->post_builder_admin_block_html($block['id'], ['data'=>$block,'wrapper'=>true,'status'=>$status,'ajax'=>(isset($config['ajax'])&&$config['ajax']?true:false)]);
            }
        }
        if(isset($config['wrapper']) && $config['wrapper']) {
            $wrapper = true;
        } else {
            $wrapper = false;
        }
		$column_html = ($wrapper?"<div class='pb-column col-md-".$column_width."' data-id='".$column['id']."'><div class='pb-column-edit-block'>".$form_edit->input_html('hidden','pb_post['.$column['id'].'][remove]','0',['class'=>['pb-column-input-remove']]).$form_edit->input_html('hidden','pb_post['.$column['id'].'][meta][column_width]',$column_width,['class'=>['pb-column-width']])."</div>":NULL).$block_html."<button class='btn btn-default btn-xs pb-block-insert' title='Add more content to this column' type='button'><i class='fas fa-plus'></i> Add Content</button>".($wrapper?"</div>":NULL);

		return $column_html;
	}
	function post_builder_admin_block_html($block_id, $config=[]) {
        global $zulu,$form_edit,$class_product,$class_file,$class_website_menu,$form_fields;

        $status = (isset($config['status'])?$config['status']:'published');
		$wrapper = (isset($config['wrapper'])&&$config['wrapper']?true:false);
		if(isset($config['data'])) {
			$block = $config['data'];
		} else {
			$block = $this->post_data(['id'=>$block_id,'type'=>'post_builder_block','status'=>$status]);
		}

		@include_once MAIN_path."includes/post-builder/".$block['_meta']['block_type'].".php";
        $block_class = "pb_".$block['_meta']['block_type'];
        if(!class_exists($block_class)) {
            return false;
        }
        $block_obj = new $block_class();

		if($block['_meta']['block_type'] != NULL) {
			$block_content = "
            <div class='pb-block-content' data-type='".$block['_meta']['block_type']."'>
                <div class='content-options content-options-left'>
                    <a href='#' class='block-content-edit' title='Edit the blocks contents'><i class='fas fa-edit'></i></a><br>
                    <a href='#' class='block-content-duplicate' title='Duplicate the block'><i class='fas fa-clone'></i></a>
                </div>
                <div class='content-description'>
                    <span class=\"label-post-title\">".$block['title']."</span><br>
                    <span class=\"label-post-type opt opt-success\">".$zulu->icon($block_obj->icon, 's')." ".$block_obj->title."</span>
                </div>
                <div class='content-options content-options-right'>
                    <a href='#' class='block-content-remove' title='Remove the block'><i class='fas fa-times'></i></a>
                </div>
            </div>";
		} else {
			$block_content = "<button class='btn btn-default btn-xs pb-block-insert' title='Add more content to this column'><i class='fas fa-plus'></i> Add Content</button>";
		}
		$block_html = ($wrapper?"<div class='pb-block ".($block['_meta']['hide']?"inactive":NULL)."' data-id='".$block['id']."' data-type='".$block['_meta']['block_type']."'>":NULL).$block_content.($wrapper?"</div>":NULL);

        if(!isset($this->pb_data->block_edit_block)) {
            $this->pb_data->block_edit_block = [];
        }
        $form_edit_fields = [];

		if(method_exists($block_obj,'admin_edit_html')) {
            $form_edit_fields = $block_obj->admin_edit_html($block['id'], $block, $config);
        }

        if(isset($form_edit_fields['js_code']) && trim($form_edit_fields['js_code']) != null) {
            if(!isset($config['ajax']) || !$config['ajax']) {
                $zulu->template->jquery[] = $form_edit_fields['js_code'];
            } else {
                $form_edit_fields['main_html'] .= "<script type=\"text/javascript\">".$form_edit_fields['js_code']."</script>";
            }
        }

        $this->pb_data->block_edit_block[$block['id']] = "
        <div class='pb-block-edit-block' data-id='".$block['id']."' data-save='0' data-type='".$block['_meta']['block_type']."'>
            <div class='pb-block-edit-main'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='form-group'>
                            <label>Content Block Name ".$form_edit->icon_help('Enter the name of your content block, this helps you identify it when using the content builder.')."</label>
                            ".$form_edit->input_html('input','pb_post['.$block['id'].'][title]',$block['title'],['placeholder'=>'Enter a name for this content block...','class'=>['pb-block-title']])."
                        </div>
                    </div>
                </div>
                ".(isset($form_edit_fields['main_html'])&&$form_edit_fields['main_html']!=null?$form_edit_fields['main_html']:null)."
                <div class='row'>
                    ".($block_obj->css_control?"
                    <div class=\"col-sm-6\">
                        <div class=\"form-group\">
                            <label>Custom CSS Class</label>
                            ".$form_edit->input_html('input','pb_post['.$block['id'].'][meta][css_class]',$block['_meta']['css_class'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class=\"col-sm-6\">
                        <div class=\"form-group\">
                            <label>Custom CSS ID</label>
                            ".$form_edit->input_html('input','pb_post['.$block['id'].'][meta][css_id]',$block['_meta']['css_id'],['placeholder'=>'Separate with spaces...'])."
                        </div>
                    </div>
                    <div class=\"col-md-12\">
                        <div class=\"form-group\">
                            <label>Custom Style</label>
                            ".$form_edit->input_html('input','pb_post['.$block['id'].'][meta][css_style]',$block['_meta']['css_style'])."
                        </div>
                    </div>
                    ":null)."
                    <div class='col-md-4'>
                        <div class='form-group'>
                            <label>Hide</label>
                            ".$form_edit->input_html('select','pb_post['.$block['id'].'][meta][hide]',$block['_meta']['hide'],['option'=>['0'=>'No','1'=>'Yes'],'class'=>['pb-block-hide']])."
                        </div>
                    </div>
                </div>
                ".$form_edit->input_html('hidden','pb_post['.$block['id'].'][parent_id]',$block['parent_id'],['class'=>['pb-block-input-parent-id']])."
                ".$form_edit->input_html('hidden','pb_post['.$block['id'].'][sort]',$block['sort'],['class'=>['pb-block-sort']])."
                ".$form_edit->input_html('hidden','pb_post['.$block['id'].'][remove]','0',['class'=>['pb-block-input-remove']])."
            </div>
            ".(isset($form_edit_fields['extra_html'])&&trim($form_edit_fields['extra_html'])!=null?$form_edit_fields['extra_html']:null)."
        </div>
        ";

		return $block_html;
	}

	function post_builder_fe_html($id) {
		global $zulu;

		$post_builder_row = $this->post_data(['id'=>$id]);
		$sections = $this->post_data(['parent_id'=>$post_builder_row['id'],'type'=>'post_builder_section','status'=>'published','sort'=>'sort ASC, id ASC']);
		$section_html = "";
		foreach($sections as $section) {
            if($section['_meta']['hide']) continue;

            if(isset($section['_meta']['frame_full_width']) && $section['_meta']['frame_full_width']) {
                $section_full_width = true;
            } else {
                $section_full_width = false;
            }

            $rows = $this->post_data(['parent_id'=>$section['id'],'type'=>'post_builder_row','status'=>'published','sort'=>'sort ASC, id ASC']);
			$row_html = "";
			foreach($rows as $row) {
                if($row['_meta']['hide']) continue;
                $columns = $this->post_data(['parent_id'=>$row['id'],'type'=>'post_builder_column','status'=>'published','sort'=>'sort ASC, id ASC']);
                $column_html = "";
                foreach($columns as $column) {
                    $blocks = $this->post_data(['parent_id'=>$column['id'],'type'=>'post_builder_block','status'=>'published','sort'=>'sort ASC, id ASC']);
                    $block_html = "";
                    foreach($blocks as $block) {
                        if($block['_meta']['hide']) continue;
                        $block_content = "<div class='pb-block-content'>".$this->post_builder_fe_block_html($block['id'], ['data'=>$block])."</div>";

                        //-- block class configs
                        $block_class = [
                            'pb-block',
                            'pb-block-type-'.$block['_meta']['block_type'],
                            'pb-block-id-'.$block['id'],
                        ];
                        if(isset($block['_meta']['css_class']) && $block['_meta']['css_class'] != null) {
                            $block_class[] = $block['_meta']['css_class'];
                        }

                        //-- add block
                        $block_html .= "<div class='".implode(' ', $block_class)."'".($block['_meta']['css_style']!=NULL?" style='".$block['_meta']['css_style']."'":NULL).($block['_meta']['css_id']!=NULL?" id='".$block['_meta']['css_id']."'":NULL).">".$block_content."</div>";
                    }

                    //-- column class configs
                    $column_class = [
                        'pb-column',
                        'col-sm-'.($column['_meta']['column_width']!=NULL?$column['_meta']['column_width']:'12'),
                    ];

                    //-- add column
                    $column_html .= "<div class='".implode(' ', $column_class)."'>".$block_html."</div>";
                }

                //-- row class configs
                $row_class = [
                    'pb-row',
                    'row',
                    'pb-row-column-'.count($columns),
                ];
                if($section_full_width) {
                    $row_class[] = 'no-gutters';
                }
                if(isset($section['_meta']['section_valign']) && $section['_meta']['section_valign'] != null) {
                    $row_class[] = 'align-items-'.$section['_meta']['section_valign'];
                }
                if(isset($row['_meta']['css_class']) && $row['_meta']['css_class'] != null) {
                    $row_class[] = $row['_meta']['css_class'];
                }

                //-- add row
                $row_html .= "<div class='".implode(' ', $row_class)."'".($row['_meta']['css_style']!=NULL?" style='".$row['_meta']['css_style']."'":NULL).($row['_meta']['css_id']!=NULL?" id='".$row['_meta']['css_id']."'":NULL).">".$column_html."</div>";
            }

            //-- section class configs
            $section_class = [
                'pb-section',
                'pb-section-row-'.count($rows),
            ];
            if(isset($section['_meta']['section_theme']) && $section['_meta']['section_theme'] != null) {
                $section_class[] = 'section-'.$section['_meta']['section_theme'];
            }
            if(isset($section['_meta']['section_padding']) && $section['_meta']['section_padding'] !== '') {
                $section_class[] = 'section-pad-'.$section['_meta']['section_padding'];
            }
            if(isset($section['_meta']['css_class']) && $section['_meta']['css_class'] != null) {
                $section_class[] = $section['_meta']['css_class'];
            }

            //-- section background image
			if(isset($section['_meta']['image_file']) && $section['_meta']['image_file']) {
				$section_bg_class_name = 'bg-image-'.$section['token'];
                $section_class[] = $section_bg_class_name;

                //-- section bg css configs
                $section_bg_css = [
                    "background-image:url('".MAIN_rel."file/post/".$section['token']."/".$section['_meta']['image_file']."');",
                ];
                if(isset($section['_meta']['bg_repeat']) && $section['_meta']['bg_repeat'] != null) {
                    $section_bg_css[] = 'background-repeat:'.$section['_meta']['bg_repeat'].';';
                }
                if(isset($section['_meta']['bg_size']) && $section['_meta']['bg_size'] != null) {
                    $section_bg_css[] = 'background-size:'.$section['_meta']['bg_size'].';';
                }
                if(isset($section['_meta']['bg_position']) && $section['_meta']['bg_position'] != null) {
                    $section_bg_css[] = 'background-position:'.$section['_meta']['bg_position'].';';
                }

                //-- add section bg css
				$zulu->template->css[] = ".".$section_bg_class_name."{".implode('', $section_bg_css)."}";
			}

            //-- add section
            $section_html .= "<div class='".implode(' ', $section_class)."'".($section['_meta']['css_style']!=NULL?" style='".$section['_meta']['css_style']."'":NULL).($section['_meta']['css_id']!=NULL?" id='".$section['_meta']['css_id']."'":NULL).">";

            //-- add frame
            if(!$section_full_width) {
                $section_html .= "<div class='frame frame-master'>";
            }

            //-- row container classes
            $container_class = [
                'pb-container',
                'container-fluid'
            ];
            if($section_full_width) {
                $container_class[] = 'p-0';
            }

            //-- add row container
            $section_html .= "<div class='".implode(' ', $container_class)."'>".$row_html."</div>";

            //-- close frame
            if(!$section_full_width) {
                $section_html .= "</div>";
            }

            //-- close section
            $section_html .= "</div>";

		}

		$html = "<div class='post-builder'>
			<div class='section-container'>".$section_html."</div>
		</div>";

		return $html;
	}

	function post_builder_fe_block_html($id, $config=[]) {
		global $zulu,$class_product,$class_website_menu,$class_website,$class_file,$class_user;

		if(isset($config['data'])) {
			$block = $config['data'];
		} else {
			$block = $this->post_data(['id'=>$block_id,'type'=>'post_builder_block','status'=>'published']);
		}

        $block_class = "pb_".$block['_meta']['block_type'];
        if(!class_exists($block_class)) {
            @include_once MAIN_path."includes/post-builder/".$block['_meta']['block_type'].".php";
        }
        $block_obj = new $block_class();
        if(method_exists($block_obj,'fe_html')) {
            $html = $block_obj->fe_html($block['id'], $block);
        } else {
            $html = '';
        }

        return $html;
	}

	function custom_array($str) {
		$ret = [];
		$split = explode(',',$str);
		foreach($split as $s) {
			$v = explode('=',$s);
			$ret[$v[0]] = $v[1];
		}
		return $ret;
	}
}
