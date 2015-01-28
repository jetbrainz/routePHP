<?php

class Curl
{
	static public function post($url, $params, $agent='routePHP App Server', $auth=array(), $headers = array())
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		
		if (!empty($params))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
		}

		if (!empty($auth['username']) && !empty($auth['password']))
		{
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $auth['username'] . ':' . $auth['password']);
		}

		if (!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		
		return curl_exec($ch);
	}
	
	static public function get($url, $params=array(), $agent='routePHP App Server', $auth=array(), $headers = array())
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		if (!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		return curl_exec($ch);
	}
}
