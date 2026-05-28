<?php

use Sales;

class SalePayment extends Model{
	
	protected $table = 'sale_payment';
	
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
	
	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];
	
	public function sale(){
        return $this->belongsTo('Sales','sale_id', 'id');
    }
	
}