<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Events as Events;

class Model extends Eloquent{
	
	protected static $operator_keys = [
		':='	=>	'=',
		':!='	=>	'!=',
		':<='	=>	'<=',
		':>='	=>	'>=', 
		':<'	=>	'<',
		':>'	=>	'>',
		':<>'	=>	'<>',  
		':?'	=>	'like', 
		':!?'	=>	'not like', 
	];
	
	protected static function boot(){
        parent::boot();
		static::creating(function ($model){
			$columns = $model->getTableColumns();
			if(in_array('stat_add',$columns)){
				$model->stat_add = time();
			}
			//Add uuid or token if the table has those columns
			if(in_array('uuid',$columns)){
				$model->uuid = $model->uuid4();
			}elseif(in_array('token',$columns)){
				$model->token = $model->uuid4();
			}
			//If the model has a slugable column
			if(isset($model->slugable) && isset($model->{$model->slugable})){
				$model->slug = $model->slugify($model->{$model->slugable});
			}
        });
		static::saving(function ($model){
			$columns = $model->getTableColumns();
			if(in_array('stat_update',$columns)) {
				$model->stat_update = time();
			}
			//If the model has a slugable column
			if(isset($model->slugable) && isset($model->{$model->slugable})){
				$model->slug = $model->slugify($model->{$model->slugable});
			}
        });
    }
	
	public function uuid4(){
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}
	
	protected function slugify($text){
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		// trim
		$text = trim($text, '-');
		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);
		// lowercase
		$text = strtolower($text);
		if (empty($text)) {
			return 'n-a';
		}
		return $text;
	}
	
	/**
	* Filters results using a string
	* Will only filter by columns that are in the childs $api_queryable array
	* The operator can be one of the following: '=', '<', '>', '<=', '>=', '<>', '!=', 'like', 'not like', 'between', 'ilike'
	* @var object
	*/
	public function whereByStringApi($query_string){
		$query_array = [];
		$query_items = [];
		if(isset(static::$api_queryable)){
			$query_items = explode(';',$query_string);
			foreach($query_items as $query_item){
				foreach(static::$operator_keys as $key=>$operator){
					if(strstr($query_item,$key)){
						$query_parts = explode($key, $query_item);
						if(in_array($query_parts[0], static::$api_queryable)){
							$column = $query_parts[0];
							$operand = $query_parts[1];
							$query_array[] = [$column, $operator, $operand];
						}
						break;
					}
				}
			}
		}
		return self::where($query_array);
	}
	
	/**
	* Filters results using a string
	* The operator can be one of the following: '=', '<', '>', '<=', '>=', '<>', '!=', 'like', 'not like', 'between', 'ilike'
	* @var object
	*/
	public function whereByString($query_string){
		$query_array = [];
		$query_items = [];
		$query_items = explode(';',$query_string);
		foreach($query_items as $query_item){
			foreach(static::$operator_keys as $key=>$operator){
				if(strstr($query_item,$key)){
					$query_parts = explode($key, $query_item);
					$column = $query_parts[0];
					$operand = $query_parts[1];
					$query_array[] = [$column, $operator, $operand];
					break;
				}
			}
		}
		
		return self::where($query_array);
	}
	
	public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
	
	public function serial($length=24,$numeric=0) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
		if($numeric) {
			$chars = "0123456789";	
		}
	
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}
	
		return $str;
	}
    
    public function metaArray($data, $multi=false) {
		$new = [];
		foreach($data as $row) {
            if($multi) {
                if(isset($new[$row->field]) && !is_array($new[$row->field]) && $new[$row->field] != $row->value) {
                    $new[$row->field] = [$new[$row->field]];
                }
                if(isset($new[$row->field]) && is_array($new[$row->field])) {
                    if(is_array($new[$row->field])) {
                        $new[$row->field][] = (isset($row->value)?$row->value:NULL);
                    }
                } elseif(isset($row->field)) {
                    $new[$row->field] = (isset($row->value)?$row->value:NULL);
                }
            } elseif(isset($row->field)) {
				$new[$row->field] = (isset($row->value)?$row->value:NULL);
			}
		}
		return $new;	
	}
    
    function passwordHash($p) {
		return md5($p);	
	}
	
}