<?php

use Illuminate\Database\Capsule\Manager as DB;

class Sales extends Model{

	protected $table = 'sale';

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

	private static $reference_start = 1;
	public static $breadcrumb_divider = "<div class='breadcrumb-divider'><span class='far fa-chevron-right'></span></div>";

	protected static function boot(){
        parent::boot();
		static::creating(function ($model){
			$columns = $model->getTableColumns();
			if(in_array('reference',$columns)){
				$model->reference = Sales::getNextReferenceNumber($model->user_id);
			}
        });
    }

	private static function getNextReferenceNumber($user_id){
		$last_sale = DB::table('sale')->select('reference')->where([['user_id','=', $user_id],['reference', '>', '0']])->orderBy('reference', 'desc')->first();
		$last_no = Sales::$reference_start;
		if($last_sale != NULL){
			$last_no = (int)preg_replace("/[^0-9]/", "", $last_sale->reference);
		}
		return ($last_no+1);
	}

	public function salelines(){
        return $this->hasMany('SaleLine','sale_id', 'id');
    }

	public function saleMeta(){
        return $this->hasMany('SaleMeta','identifier', 'id');
    }

	public function meta() {
        return $this->hasMany('SaleMeta','identifier', 'id');
    }

	public function salePayments(){
        return $this->hasMany('SalePayment','sale_id', 'id');
    }

	public function saleCoupon(){
        return $this->hasOne('SaleCoupon','id', 'coupon_id');
    }

    public function lines() {
        return $this->hasMany('SaleLine','sale_id', 'id');
    }

    public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

	public function checkoutBreadcrumbHTML() {
        global $zulu, $class_module;

        $meta = $this->metaArray($this->meta);
        if(!$meta['checkout_step']) {
            $meta['checkout_step'] = 1;
        }
        $step = $meta['checkout_step'];

        $parts = [
            "<div class='breadcrumb'><a href='".$zulu->front_link(LINK_basket)."'>Basket</a></div>",
            "<div class='breadcrumb".($step==1?' current':null)."'>".($step>1?"<a href='".$zulu->front_link(LINK_checkout)."'>":null)."Details</a></div>",
        ];

        $show_shipping = false;
        if($meta['delivery_method'] == 'ship') {
            $show_shipping = true;
        } elseif($meta['delivery_method'] == null) {
            $shipping_options = $class_module->delivery_options();
            if($shipping_options['ship'] && $shipping_options['pickup']) {
                $show_shipping = true;
            }
        }
        if($show_shipping) {
            $parts[] = "<div class='breadcrumb".($step==2?' current':null)."'>".($step>2?"<a href='".$zulu->front_link(LINK_checkout_shipping)."'>":null)."Shipping</a></div>";
        }

        $parts[] = "<div class='breadcrumb".($step==3?' current':null)."'>".($step>3?"<a href='".$zulu->front_link(LINK_checkout_payment)."'>":null)."Payment</a></div>";

        return "<div class='breadcrumbs breadcrumbs-checkout'>".implode(self::$breadcrumb_divider, $parts)."</div>";
    }

    public function checkoutReviewHTML() {
        global $zulu;

        $meta = $this->metaArray($this->meta);

        $list_items = [];
        if($meta['checkout_step'] > 1) {
            $list_items[] = "<li class='list-group-item'>
                <div class='lgi-label'>Contact</div>
                <div class='lgi-content'>".stripslashes($this->name)."</div>
                <div class='lgi-link'><a href='".$zulu->front_link(LINK_checkout)."'>Change</a></div>
            </li>";
            if($meta['delivery_method'] == 'ship') {
                $address_name = ($meta['ship_company']?$meta['ship_company']:$meta['ship_name_first']." ".$meta['ship_name_last']);
                $list_items[] = "<li class='list-group-item'>
                    <div class='lgi-label'>Ship to</div>
                    <div class='lgi-content'>".stripslashes($zulu->compile(', ',[$address_name, $meta['ship_address'], $meta['ship_suburb'], $meta['ship_city'].($meta['ship_post']?" ".$meta['ship_post']:null), $meta['ship_country']]))."</div>
                    <div class='lgi-link'><a href='".$zulu->front_link(LINK_checkout)."'>Change</a></div>
                </li>";
            }
        }
        if($meta['checkout_step'] > 2) {
            $list_items[] = "<li class='list-group-item'>
                <div class='lgi-label'>Method</div>
                <div class='lgi-content'>".stripslashes($meta['ship_method']).($meta['ship_method_note']?"<br><span class='lgi-content-note'>".stripslashes($meta['ship_method_note'])."</span>":null)."</div>
                <div class='lgi-link'><a href='".$zulu->front_link(LINK_checkout_shipping)."'>Change</a></div>
            </li>";
        }

        if(count($list_items) > 0) {
            $html = "<ul class='list-group customer-summary'>".implode('',$list_items)."</ul>";
        } else {
            $html = null;
        }

        return $html;
    }

}
