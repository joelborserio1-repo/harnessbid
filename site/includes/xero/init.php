<?php

/**
 * Persist the OAuth access token and session handle somewhere
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 * @param array $params the response parameters as an array of key=value pairs
 */
function persistSession($response)
{
    if (isset($response)) {
        $_SESSION['access_token']       = $response['oauth_token'];
        $_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
      	if(isset($response['oauth_session_handle']))  $_SESSION['session_handle']     = $response['oauth_session_handle'];
    } else {
        return false;
    }

}

/**
 * Retrieve the OAuth access token and session handle
 * In my example I am just using the session, but in real world, this is should be a storage engine
 *
 */
function retrieveSession()
	{
		global $class_setting;
		
		if (isset($_SESSION['access_token'])) {
			$response['oauth_token']            =    $_SESSION['access_token'];
			$response['oauth_token_secret']     =    $_SESSION['oauth_token_secret'];
			$response['oauth_session_handle']   =    $_SESSION['session_handle'];
			return $response;
		} elseif($class_setting->data['xero_oauth_token']!=NULL) {
			$response['oauth_token']            =    $class_setting->data['xero_oauth_token'];
			$response['oauth_token_secret']     =    $class_setting->data['xero_oauth_token_secret'];
			$response['oauth_session_handle']   =    $class_setting->data['xero_oauth_session'];
			return $response;
		} else {
			return false;
		}
	
	}

function outputError($XeroOAuth)
{
    echo 'Error: ' . $XeroOAuth->response['response'] . PHP_EOL;
    pr($XeroOAuth);
}

/**
 * Debug function for printing the content of an object
 *
 * @param mixes $obj
 */
function pr($obj)
{

    if (!is_cli())
        echo '<pre style="word-wrap: break-word">';
    if (is_object($obj))
        print_r($obj);
    elseif (is_array($obj))
        print_r($obj);
    else
        echo $obj;
    if (!is_cli())
        echo '</pre>';
}

function is_cli()
{
    return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
}
