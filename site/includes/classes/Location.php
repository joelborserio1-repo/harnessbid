<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model {
	use SoftDeletes;

	protected $table = 'location';

	public static $file_folder = "location/";
    public static $file_rel = MAIN_rel."file/location/";
    public static $file_path = MAIN_path."file/location/";

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
		'currency_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function currency() {
        return $this->belongsTo('Currency','currency_id', 'id');
    }

	public function priceList() {
        return $this->hasMany('PriceList','location_id', 'id');
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

	public function image($path=false) {
		$main_image = $this->token."/".$this->image;
		if($this->image != null && file_exists(self::$file_path.$main_image)) {
            if($path) {
                $return = self::$file_path.$main_image;
            } else {
                $return = self::$file_rel.$main_image;
            }
		} else {
            $return = null;
        }

		return $return;
	}

	public static function optionArray() {
		$locations = self::where('status','1')->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();
		$options = [];
		foreach($locations as $location) {
			$options[$location->id] = $location->name;
		}

		return $options;
	}

}
