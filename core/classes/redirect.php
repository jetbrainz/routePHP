<?php

/**
 * Description of redirect
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Redirect
{
	static private $flags = array (
		'success', 'error'
	);
	
	static public function index($message=null)
	{
		self::sendRedirect('/');
	}
	
	static public function url($url)
	{
		if (!preg_match ('|https?://|', $url)) {
			$url = URL::getBase().$url;
		}
		self::sendRedirect($url);
	}
	
	static public function errorHere($message=null)
	{
		if ($message !== null) {
			Messenger::error($message);
		}
		self::sendRedirect(\Url::getPath());
		exit;
	}
	
	static public function successHere($message=null)
	{
		if ($message !== null) {
			Messenger::success($message);
		}
		self::sendRedirect(\Url::getPath());
		exit;
	}
	
	static public function success($message=null)
	{
		if ($message !== null) {
			Messenger::success($message);
		}
		//if (!Url::isSuccess()) {
			self::sendRedirect(self::makeURL('success'));
		//}
		exit;
	}
	
	static public function error($message=null)
	{
		if ($message !== null) {
			Messenger::error($message);
		}
		//if (!Url::isError()) {
			self::sendRedirect(self::makeURL('error'));
		//}
		exit;
	}
	
	static private function makeURL($flag)
	{
		$url = Url::getValuePath();
		
		return $url.'/'.$flag;
	}
	
	static private function sendRedirect($url)
	{
		header ('Location: '.$url);
		exit;
	}
}
