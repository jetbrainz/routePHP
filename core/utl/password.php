<?php

/**
 * Description of password
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Password
{
	static public function create()
	{
		return self::numeric_password();
	}
	
	static public function trivial_password($length=8)
	{
		$base = 'abcdefghijklmnopqrstuvwxyz';
		$baseD = '0123456789';
		
		$r = array();
		
		for($i=0; $i<$length; $i+=2) {
			$r[] = substr($base, rand(0, strlen($base)-1), 1);
		}
		for($i=0; $i<$length; $i+=2) {
			$r[] = substr($baseD, rand(0, strlen($baseD)-1), 1);
		}
		shuffle($r);
		
		return implode('', $r);
	}
	
	static public function numeric_password($length=8)
	{
		return rand(11111111,99999999);
	}

	static public function secure_password($length=8)
	{
		if ($length < 8) {
			return false;
		}
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$charsUpper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$digits = '0123456789';
		$symbols = '!@#$%^&*()_-=+;:,.?';

		$str = substr( str_shuffle( $chars ), 0, $length-4)
				. substr( str_shuffle( $charsUpper ), 0, 2)
				. substr( str_shuffle( $digits ), 0, 1)
				. substr( str_shuffle( $symbols ), 0, 1);

		$password = str_shuffle ($str);

		return $password;
	}
}
