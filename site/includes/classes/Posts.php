<?php

class Posts extends Model {
	
	protected $table = 'post';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'identifier',
        'type',
        'status',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
	
	public $timestamps = false;
    
    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }
	
    public function meta() {
        return $this->hasMany('PostMeta','identifier', 'id');
    }
    
    public function post_data() {
        global $class_post, $class_user;
        
        $post_data = $this->toArray();
        $class_post->vars->data = $post_data;
        $meta = $this->metaArray($this->meta);

        $arr_data['frame'] = $meta['frame'];
        $arr_data['url'] = $this->feURL(true);
        $arr_data['template'] = $class_post->post_template($post_data['type']);
        if($post_data['author_id']>0) {
            $arr_data['author_name'] = $class_user->name(['id'=>$post_data['author_id']]);
        }

        //Main Compile
        $post_data['_root'] = $post_data;
        $post_data['_data'] = $arr_data;
        $post_data['_meta'] = $meta;

        $class_post->vars->data = $post_data;
        return $post_data;
    }
    
    function feURL($full=false, $config=[]) {
        global $class_post;
        
		$folder = $class_post->config->template[$this->type]['slug'];
		return ($full?FE_url:FE_rel).($folder!=NULL?$folder.'/':NULL).$this->slug."/".($config['version']!=NULL?"?version=".$config['version']:NULL);
	}
    
    public function images($config=[]) {
		global $zulu,$class_file, $class_post;
		
		$main_path = 'post/'.$this->token.'/';
		$main_gallery = 'post/'.$this->token.'/gallery/';
		
        $meta = $this->meta_array($this->meta);
		$main_image = $main_path.$meta['image_main'];
        $header_image = $main_path.$meta['image_header'];
        
		$return = [];
		if($meta['image_main']!=NULL && file_exists($class_file->file_root."../".$main_image)) {
			$return['main'] = ($config['base']!=NULL?$config['base']:NULL).$main_image;
			$return['file'] = $class_post->config->file_rel.$main_image; 
		}
		
		if($class_post->config->template[$this->type]['config']['image']['gallery']) {
            $post_gallery = Posts::where([['type','image'],['parent_id',$this->id]]);
			foreach($post_gallery as $gallery_item) {
				$image = $gallery_item->images($gallery_item['id']);
				if($image['main']!=NULL) {
					$retain_gallery[] = ['title'=>$gallery_item->title,'image'=>($config['base']!=NULL?$config['base']:NULL).$image['main'],'post_id'=>$this->id];	
				}
			}
			$return['gallery']  = $retain_gallery;
		}

        return $return;
	}
    
}