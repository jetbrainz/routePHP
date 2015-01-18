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
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
		$password = substr( str_shuffle( $chars ), 0, $length );
		return $password;
	}
}
