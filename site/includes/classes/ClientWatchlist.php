<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class ClientWatchlist extends Model {
	use SoftDeletes;

	protected $table = 'client_watchlist';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
		'client_id',
		'product_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

    public function product() {
        return $this->belongsTo('Products','product_id', 'id');
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

	public function closingSoonEmail($hours=1) {
		global $zulu;

		$client = $this->client;
		$product = $this->product;

		$to = $client->email;
		$sub = stripslashes($product->name)." Closing Soon";
		$mess = "<p>Hi ".stripslashes($client->name_first).",</p>
		<p>We're just reminding you the listing, <b>".stripslashes($product->name)."</b>, on your watchlist is closing in ".$hours." hour".$zulu->s($hours)."</p>
		<p><a href='".$product->feURL(true)."'>View the lsiting here</a></p>";

		$zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'client_watchlist', 'object_id'=>$this->id]);
	}

}
