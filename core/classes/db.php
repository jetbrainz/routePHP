<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of storage
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class DB
{
	static private $instances = array();
	
	static public function getInstance($dsn, $username, $password, $options)
	{
		$hash = md5($dsn . $username . $password);
		
		if (
			isset(self::$instances[$hash])
			&& self::$instances[$hash] instanceof PDO
		) {
			return self::$instances[$hash];
		}
		
		if (!is_array ($options)) {
			$options = array ();
		}
		$options[PDO::ATTR_TIMEOUT] = "1";
		
		if (!isset (self::$instances[$hash])) {
			try {
				self::$instances[$hash] = new PDO($dsn, $username, $password, $options);
				self::$instances[$hash]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			
				return self::$instances[$hash];
			} catch (\Exception $e) {
				// TODO: make this visible
				//print_r ($e);
			}
		}
	}
	
	static public function getNewInstance($dsn, $username, $password, $options)
	{
		$db = new PDO($dsn, $username, $password, $options);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}

	static public function getDummy()
	{
		return new dummyDB();
	}
}

class dummyDB
{
	public function __call($method, $params)
	{
		return false;
		throw new Exception('No connection to database. Dummy class.');
	}
}
