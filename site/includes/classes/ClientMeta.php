<?php
use Client;

class ClientMeta extends Model{
	
	protected $table = 'client_meta';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'identifier',
		'field',
		'label',
		'value'
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
    
    public $timestamps = false;
	
	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];
	
	public function client(){
        return $this->belongsTo('Clients','identifier', 'id');
    }
	
}