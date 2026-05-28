<?php

use Sales;

class SaleMeta extends Model{
	
	protected $table = 'sale_meta';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'identifier',
		'field',
		'label',
		'value'
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
	
	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];
	
	public function sale(){
        return $this->belongsTo('Sales','identifier', 'id');
    }
	
}