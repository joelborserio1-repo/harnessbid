<?php

class ProductMeta extends Model{
	
	protected $table = 'product_meta';
	
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
    
    public $timestamps = false;
	
	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];
	
	public function product(){
        return $this->belongsTo('Product','identifier', 'id');
    }
	
}