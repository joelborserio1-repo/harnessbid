<?php

class PostVersion extends Model {
	
	protected $table = 'post_version';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'post_id',
        'parent_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
	
	public $timestamps = false;
	
    public function post() {
        return $this->belongsTo('Posts','post_id', 'id');
    }
    
    public function version_decode() {
        global $class_post;
		
        $post = $this->post;
		$post_data = unserialize($data['data_post']);
		$meta = unserialize($data['data_post_meta']);
		
		$class_post->vars->data = $post_data;
        $arr_data = [];
		$arr_data['url'] = $post->feURL(true);
		$arr_data['template'] = $class_post->post_template($post_data['type']);
		$post_data['_root'] = $post_data;
		$post_data['_data'] = $arr_data;
		$post_data['_meta'] = $meta;
		$class_post->vars->data = $post_data;
		
		return $post_data;
	}
    
}