<?php

/**
 * Description of base
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Base extends Config
{
	protected $logger = null;
	protected $dbo = null;
	
	public function __construct($logger=true)
	{
		parent::__construct();

		if ($logger) {
			$this->logger = new Logger(get_class($this));
		}
	}
	
	public function beginTransaction()
	{
		return $this->db()->beginTransaction();
	}
	
	public function commitTransaction()
	{
		return $this->db()->commit();
	}
	
	public function rollbackTransaction()
	{
		return $this->db()->rollBack();
	}
	
	/**
	 * Return instance of DB class
	 * @return DB
	 */
	protected function db($prefix='db')
	{
		if ($this->dbo === null) {
			$params = null;
			if (is_string($prefix)) {
				$params = $this->getConfig($prefix);
			} elseif (is_array($prefix)) {
				$params = $prefix;
			}
			if (!empty($params)) {
				$this->dbo = DB::getInstance(
					$params['dsn'],
					$params['username'],
					$params['password'],
					$params['options']
				);
			} else {
				$this->dbo = DB::getDummy();
			}

		}
		return $this->dbo;
	}
	
	/**
	 * Return instance of Token class
	 * @return Token
	 */
	protected function token()
	{
		if (!$this->token) {
			$this->token = new Token($this->db());
		}
		return $this->token;
	}
	
	public function queueRun($task)
	{
		;
	}
	
	public function isConstant($param) {
		$ref = new ReflectionClass($this);
		$const = $ref->getConstants();
		$ret = false;
		foreach ($const as $con=>$val) {
			if ($param == $val) {
				$ret = true;
				break;
			}
		}
		return $ret;
	}
}
