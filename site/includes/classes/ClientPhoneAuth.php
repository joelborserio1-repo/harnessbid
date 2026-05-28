<?php

class ClientPhoneAuth extends Model {

	protected $table = 'client_phone_auth';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'client_id',
		'phone',
		'date_complete',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];

    private static $validDateString = "-1 day";

    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }

	public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

    public function send() {
		global $zulu;

        /*$client = $this->client;
		$code = $zulu->serial(6, true);

		$this->code = md5($code);
		$this->date_expire = strtotime('+1 hour');
		$this->save();

		$message = "Enter {$code} as your Harnessbid authentication code.";*/

		$twilio = new Twilio();
		$result = $twilio->verifySend($this->phone);

		return $result;
	}

	public function check($code) {
		global $zulu;

		$twilio = new Twilio();
		$result = $twilio->verifyCheck($this->phone, $code);

		return $result;
	}

	public function complete() {
		ClientMeta::updateOrCreate([
			'identifier'    =>  $this->client_id,
			'field'         =>  'web_verify_phone',
		],[
			'value'         =>  time(),
		]);

		$this->date_complete = time();
		$this->save();
	}

	public function scopeWhereActive($query) {
		return $query->where('date_complete', null);
	}

}

?>
