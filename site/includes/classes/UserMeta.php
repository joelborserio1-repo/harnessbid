<?php

class UserMeta extends Model {
	
	protected $table = 'user_meta';
	
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
	
    public function user() {
        return $this->belongsTo('Users','identifier', 'id');
    }
    
}