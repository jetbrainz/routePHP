<?php

/**
 * Description of url
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Url
{
	static public function isSuccess()
	{
		return preg_match ('/\/success/', $_SERVER['REQUEST_URI']);
	}
	
	static public function isError()
	{
		return preg_match ('/\/error/', $_SERVER['REQUEST_URI']);
	}
	
	static public function getBase()
	{
		return
			'http' .
			(isset($_SERVER['HTTPS'])?'s':'') .
			'://' .
			$_SERVER['HTTP_HOST'];
	}
	
	static public function getPath()
	{
		return parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}
	
	static public function getValuePath()
	{
		$path = self::getPath();
		$path = explode ('/', $path);
		
		return self::getBase() . (isset($path[1]) ? '/'.$path[1] : '');
	}
	
	static public function getParams()
	{
		$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		
		return empty($p) ? null : $p;
	}

	static public function getURL()
	{
		$path = self::getPath();
		$params = self::getParams();
		return self::getBase().$path.($params?'?'.$params:'');
	}
	
	static public function getPart($num)
	{
		$parts = explode('/', self::getPath());
		
		if (isset ($parts[$num])) {
			return rawurldecode($parts[$num]);
		}
		
		return false;
	}
}
