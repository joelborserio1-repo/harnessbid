<?php

class SaleCoupon extends Model{

	protected $table = 'sale_coupon';

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

	public function sale(){
        return $this->belongsTo('Sales','sale_id', 'id');
    }

}
