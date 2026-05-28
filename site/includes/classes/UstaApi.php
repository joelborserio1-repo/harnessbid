<?php

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class UstaApi {

	const CLIENT_ID = 'hstuser02';
   	const CLIENT_SECRET = 'hstuser02p';

	private $http_client;
	private $access_token;
	private $access_token_expiry;

	function __construct($config=[]) {
        $this->http_client = new GuzzleHttp\Client();
    }

	private function connect() {
		$url = 'https://oauth-ca.ustrotting.com/connect/token';

		try {
			$result = json_decode($this->http_client->post($url, [
	            'form_params' => [
					'grant_type' => 'client_credentials',
	                'client_id' => self::CLIENT_ID,
	                'client_secret' => self::CLIENT_SECRET,
					'scope' => 'horsesearch',
	            ],
	        ])->getBody()->getContents());
		} catch (GuzzleHttp\Exception\ClientException $e) {
			$response = $e->getResponse();
			$body = $response->getBody()->getContents();
			//print_r($body);exit;
			return false;
        }
		//print_r($result);exit;

		$this->access_token = $result->access_token;
		$this->access_token_expiry = strtotime("+".$result->expires_in." seconds");

		return true;
	}

	private function request($method='GET', $url, $body=[]){

		if(!$this->access_token || $this->access_token_expiry <= time()) {
			if(!$this->connect()) {
				return false;
			}
		}

        try {
			$result = $this->http_client->request($method, $url, [
				'headers' => [
					'Authorization' => "Bearer {$this->access_token}",
					'Content-Type' => 'application/json',
				],
				//'debug' => true
			])->getBody()->getContents();

		} catch (Exception $e) {
			$response = $e->getResponse();
			//$body = $response->getBody()->getContents();
			//print_r($e);exit;
			return false;
        }

		/*if(!strstr($url, 'search')) {
			var_dump($result);exit;
		}*/

		return json_decode($result);
    }

	public function horseSearch($search) {
		$results = $this->request('GET', "https://ews-ca.ustrotting.com/horses/search?HorseName={$search}&FoalYearMin=".(date('Y')-30));
		if(!$results) {
			return false;
		}
		foreach($results as $result) {
			if(strtolower($result->horseName) == strtolower($search)) {
				return $result;
			}
		}
		return false;
	}

	public function getHorseDetails($url) {
		return $this->request('GET', $url);
	}

}
