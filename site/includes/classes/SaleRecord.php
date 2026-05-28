<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class SaleRecord extends Model{
    use SoftDeletes;

	protected $table = 'sale_record';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'client_sell_id',
        'client_buy_id',
        'product_id',
        'listing_id',
        'bid_id',
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

    public function clientSeller() {
        return $this->belongsTo('Clients','client_sell_id', 'id');
    }

    public function clientBuyer() {
        return $this->belongsTo('Clients','client_buy_id', 'id');
    }

    public function bid() {
        return $this->belongsTo('ProductListingBid','listing_bid_id', 'id');
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
