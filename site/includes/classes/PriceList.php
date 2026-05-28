<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model {
	use SoftDeletes;

	protected $table = 'price_list';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
		'location_id',
		'code',
	];

	public static $price_options = [
		'listing'	=>	[
			'title'	=>	'Listing',
			'addon'	=>	false,
		],
		'add_home'	=>	[
			'title'	=>	'Homepage Feature',
			//'price'	=>	'25',
			'desc'	=>	'Showcases your listing on the homepage for 48 hours after publishing.',
			'icon'	=>	'star',
			'addon'	=>	true,
		],
		'add_feature'	=>	[
			'title'	=>	'Featured Listing',
			//'price'	=>	'18',
			'desc'	=>	'Gives your listing a highlighted border and more prominent feature on search results.',
			'icon'	=>	'gavel',
			'addon'	=>	true,
		],
		'add_video'	=>	[
			'title'	=>	'Video Media',
			//'price'	=>	'5',
			'desc'	=>	'Add up to 2 linked videos to your listing and showcase your horse in motion.',
			'icon'	=>	'video',
			'addon'	=>	true,
		],
	];
	public static $default_location_id = 1;

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function location() {
        return $this->hasMany('Location','location_id', 'id');
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

	public static function addonArray() {
		$arr = [];
		foreach(self::$price_options as $price_key=>$price_option) {
			if($price_option['addon']) {
				$arr[$price_key] = $price_option;
			}
		}

		return $arr;
	}

	public static function addonSelectHTML($location_id=0) {
		global $zulu, $form_edit;

		if($location_id <= 0) {
			$location_id = self::$default_location_id;
		}
		$location = Location::find($location_id);
		$currency = $location->currency;

		$feature_html_arr = [];
		$feature_options = self::addonArray();
		foreach($feature_options as $key=>$feature_data) {
			$price_list = PriceList::where([['location_id', $location_id], ['code', $key]])->first();
			$price = CLIENT_subscribed ? $price_list->price_subscribe : $price_list->price;
			if($price<=0 &&CLIENT_subscribed) {
				$_POST[$key] = 1;
			}
			$feature_html_arr[] = "<div class='field w100 listing-checkbox-selector".($_POST[$key]=='1'?' selected':null).($price<=0&&CLIENT_subscribed?' hide':null)."' data-key='".$key."'>
		        ".$zulu->icon($feature_data['icon'])."
		        <p class='heading'><b>".$feature_data['title']."</b> Just $<span class='price'>".number_format($price)."</span> <span class='currency'>".$currency->code."</span></p>
		        <p>".$feature_data['desc']."</p>
		        ".$form_edit->input_html('checkbox', $key, '1', ['class'=>['hide'], 'checked'=>($_POST[$key]=='1')])."
		        ".$zulu->icon(($_POST[$key]=='1'?'check-circle':'circle'), ($_POST[$key]=='1'?'s':'r'), ['checker'])."
		    </div>";
		}

		return implode('', $feature_html_arr);
	}

}
