<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductListingBid extends Model{
    use SoftDeletes;

	protected $table = 'product_listing_bid';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'client_id',
        'product_id',
        'listing_id',
        'bid_amount',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function product() {
        return $this->belongsTo('Products','product_id', 'id');
    }

    public function listing() {
        return $this->belongsTo('ProductListing','listing_id', 'id');
    }

    public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

    public static function boot() {
        parent::boot();

        self::creating(function($model) {
			global $class_user, $zulu;
			$model->user_id = $class_user->authorised->id;
        });
    }

    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }

}
