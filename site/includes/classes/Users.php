<?php

class Users extends Model {
	
	protected $table = 'user';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
	
	public $timestamps = false;
	
    public function meta() {
        return $this->hasMany('UserMeta','identifier', 'id');
    }
    
}