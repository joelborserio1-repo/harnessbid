<?php

class Clients extends Model{

	protected $table = 'client';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'user_id',
		'name_first',
		'name_last',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		'password'
	];

	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];

	public static $phoneExtensions = [
		'+1' => '+1',
		'+61' => '+61',
		'+64' => '+64',
		'Other' => 'Other'
	];

    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }

    public function meta() {
        return $this->hasMany('ClientMeta','identifier', 'id');
    }

	public function clientMeta(){
        return $this->meta;
    }

    public function passwordResets() {
        return $this->hasMany('ClientPasswordReset','client_id', 'id');
    }

    public function productReviews() {
        return $this->hasMany('ProductReview','client_id', 'id');
    }

    public function sales() {
        return $this->hasMany('Sales','client_id', 'id');
    }

	public function products() {
        return $this->belongsTo('Products','client_id', 'id');
    }

	public function location() {
        return $this->belongsTo('Location','location_id', 'id');
    }

	public function region() {
        return $this->belongsTo('LocationRegion','region_id', 'id');
    }

	public function watchlists() {
        return $this->hasMany('ProductWatchlist','client_id', 'id');
    }

	public function saleRecordsSeller() {
        return $this->hasMany('SaleRecord','client_sell_id', 'id');
    }

	public function saleRecordsBuyer() {
        return $this->hasMany('SaleRecord','client_buy_id', 'id');
    }

	public function productsLost() {
        return $this->belongsToMany('Products', 'product_lost', 'client_id', 'product_id');
    }

	public function phoneAuths() {
        return $this->hasMany('ClientPhoneAuth','client_id', 'id');
    }

    public function beLink() {
        global $zulu;
        return "<a href='".$zulu->link_page('client',['query'=>['Action'=>'edit','id'=>$this->id]])."'>".$this->nameFull()."</a>";
    }

    public function nameFull(){
		return $this->name_first." ".$this->name_last.(trim($this->company)!=NULL?" (".$this->company.")":NULL);
	}

    public function hasPurchased($product) {
        $sales = $this->sales;
        foreach($sales as $sale) {
            $sale_lines = $sale->lines;
            foreach($sale_lines as $sale_line) {
                if($sale_line->product_id == $product->id) {
                    return true;
                }
            }
        }
        return false;
    }

    public function verifyEmail() {
        global $zulu;

        $link = FE_url."members/login.php?action=activate&token=".$this->token;
        $message = "
        <p>Hello ".$this->name_first.",</p>
        <p>We have recieved your account application. Please click the link below to confirm your account for instant access.<br><a href=\"".$link."\">Activate Account</a></p>
        <p>For reference, your login is your email address and password is the one you entered when signing up.</p>";
        $subject = "Activate Your Sign In";
        $zulu->mail_send($this->email, $subject, $message, '', false, ['client'=>true]);
    }

	public function verifyPhone() {
        global $zulu, $class_user;

		$phoneAuth = new ClientPhoneAuth();
		$phoneAuth->user_id = $class_user->authorised->id;
		$phoneAuth->client_id = $this->id;
		$phoneAuth->phone = $this->phone;
		$phoneAuth->save();
		$result = $phoneAuth->send();
		return $result;
    }

	public function isVerified() {
        $meta = $this->metaArray($this->meta);
		if(isset($meta['web_verify_phone']) && $meta['web_verify_phone'] > 0) {
			return true;
		}
		return false;
    }

}
