<?php
class RunLevel {
	const RUNLEVEL_WEB = 'WEB';
	const RUNLEVEL_API = 'API';
	const RUNLEVEL_SCHEDULER = 'SCHEDULER';
	const RUNLEVEL_COMMAND = 'COMMAND';
	const RUNLEVEL_PHPUNIT = 'PHPUNIT';
	const RUNLEVEL_TESTS = 'TESTS';

	static public function isWEB()
	{
		return APP_LEVEL == self::RUNLEVEL_WEB;
	}

	static public function isAPI()
	{
		return APP_LEVEL == self::RUNLEVEL_API;
	}

	static public function isSCHEDULER()
	{
		return APP_LEVEL == self::RUNLEVEL_SCHEDULER;
	}

	static public function isCOMMAND()
	{
		return APP_LEVEL == self::RUNLEVEL_COMMAND;
	}

	static public function isPHPUNIT()
	{
		return APP_LEVEL == self::RUNLEVEL_PHPUNIT;
	}

	static public function isTESTS()
	{
		return APP_LEVEL == self::RUNLEVEL_TESTS;
	}

	static public function defined()
	{
		return defined('APP_LEVEL');
	}

	static public function define($level)
	{
		define('APP_LEVEL', $level);
	}
}
