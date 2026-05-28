<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class LocationRegion extends Model {
	use SoftDeletes;

	protected $table = 'location_region';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
		'location_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	public function location() {
        return $this->belongsTo('Location','location_id', 'id');
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

	public static function optionArray($location_id=0) {
		$query = self::query();
		$query->where('status', '1')->orderBy('sort', 'ASC')->orderBy('name', 'ASC');
		if($location_id > 0) {
			$query->where('location_id', $location_id);
		}
		$regions = $query->get();
		$options = [];
		foreach($regions as $region) {
			$options[$region->id] = $region->name;
		}

		return $options;
	}

}
