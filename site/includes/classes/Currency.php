<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model {
	use SoftDeletes;

	protected $table = 'currency';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function locations() {
        return $this->hasMany('Location','location_id', 'id');
    }

	public function products() {
        return $this->hasMany('Products','product_id', 'id');
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

	public static function optionArray() {
		$currencies = self::where('status','1')->orderBy('sort', 'ASC')->orderBy('name', 'ASC')->get();
		$options = [];
		foreach($currencies as $currency) {
			$options[$currency->id] = $currency->name;
		}

		return $options;
	}

}
