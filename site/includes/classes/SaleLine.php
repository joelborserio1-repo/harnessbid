<?php

use Sales;

class SaleLine extends Model{
	
	protected $table = 'sale_line';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'sale_id',
		'product_id',
		'object',
		'object_id',
		'sku',
		'description',
		'quantity',
		'quantity_r',
		'price',
		'discount',
		'extra',
		'total',
		'custom',

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
        return $this->belongsTo('Sales','sale_id', 'id');
    }
	
}