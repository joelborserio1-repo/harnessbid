<?php

class Twilio {

	const ACCOUNT_SID = 'TWILIO_ACCOUNT_SID';
	const AUTH_TOKEN = 'TWILIO_AUTH_TOKEN';
	const SERVICE_SID = 'TWILIO_VERIFY_SERVICE_SID';

	private $client;

	function __construct($config=[]) {
		$this->client = new Twilio\Rest\Client(self::ACCOUNT_SID, self::AUTH_TOKEN);
    }

	public function verifySend($to) {
		try {
			$verification = $this->client->verify->v2->services(self::SERVICE_SID)
               ->verifications
               ->create($to, "sms");

			//print_r($verification);exit;
			if($verification->status == 'pending') {
				return true;
			}

		} catch(Exception $e) {
			//print_r($e);exit;
		}

		return false;
	}

	public function verifyCheck($to, $code) {
		try {
			$verification_check = $this->client->verify->v2->services(self::SERVICE_SID)
            	->verificationChecks
                ->create([
					"to" => $to,
					"code" => $code
				]);

			//print_r($verification_check->status);exit;
			if($verification_check->status == 'approved') {
				return true;
			}

		} catch(Exception $e) {
			//print_r($e);exit;
		}

		return false;
	}

}
