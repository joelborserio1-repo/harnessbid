<?php

class ClientPasswordReset extends Model {
	
	protected $table = 'client_password_reset';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
		'client_id',
		'email',
		'date_complete',
		'ip_complete',
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
    
    public function feURL($full=false) {
        $url = "members/reset-password/".$this->uuid."/";
        if(!$full) {
            $url = FE_rel.$url;
        } else {
            $url = FE_url.$url;
        }
        
        return $url;
    }
    
    public function sendEmail() {
		global $zulu;
		
        $client = $this->client;
		if($client != null) {
            $link = $this->feURL(true);
			$message = "
            <p>Hello ".$client->name_first.",</p>
            <p>Please click the link below to reset your password:<br><a href=\"".$link."\">Reset Password</a></p>
            <p>This request is valid for 24 hours. If you continue to have trouble signing in please contact us for assistance.</p>
            ";
			$res = $zulu->mail_send($this->email, "Reset your password", $message, '', false, ['client'=>true, 'object'=>'client_password_reset', 'object_id'=>$this->id]);
			return true;
		}
		return false;
	}
    
    function canReset() {
        $client = $this->client;
        if($client != null) {
            $cpr = $client->passwordResets()->latest()->first();
            if($this->id == $cpr->id && $this->date_complete == 0 && $this->stat_add >= strtotime(self::$validDateString)) {
                return true;
            }
        }
		return false;
	}
	
}

?>