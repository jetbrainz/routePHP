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
		
		if (!isset (self::$instances[$hash])) {
			try {
				self::$instances[$hash] = new PDO($dsn, $username, $password, $options);
				self::$instances[$hash]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} catch (\Exception $e) {
				// TODO: make this visible
				//print_r ($e);
			}
		}
			
		return self::$instances[$hash];
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
		throw new Exception('No connection to database. Dummy class.');
	}
}