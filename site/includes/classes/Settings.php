<?php

class Settings extends Model {
	
	protected $table = 'config';
	
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
	
	public $timestamps = false;
    
    public static function makeArray($data=[]) {
        $return = [];
		foreach($data as $row) {
			$return[$row->field] = $row->value;
		}
        
        return $return;
    }
    
    public static function loadSettings($user_id=USER_id) {
        global $class_setting;
        
        $settings = self::where('user_id',$user_id)->get();
        $setting = self::makeArray($settings);
        $class_setting->set_global($setting);
        
        return $setting;
    }
    
}