<?php

class PostMeta extends Model {
	
	protected $table = 'post_meta';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'identifier',
        'field',
        'value'
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
        return $this->belongsTo('Posts','identifier', 'id');
    }
    
}