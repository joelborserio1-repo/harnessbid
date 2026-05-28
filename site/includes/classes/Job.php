<?php

class Job extends Model {

	protected $table = 'jobs';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'type',
		'object',
		'object_id',
	];

	protected $dates = ['created_at', 'updated_at'];

	public static function newJob($type, $object=null, $object_id=null) {
		$job = self::create([
			'type' => $type,
			'object' => $object,
			'object_id' => $object_id,
		]);
	}

}
