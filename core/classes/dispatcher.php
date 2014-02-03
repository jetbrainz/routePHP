<?php

/**
 * Description of dispatcher
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Dispatcher
{
	private $prefix = '';
	
	public function __construct($runLevel, $actionOffset=1)
	{
		if (LOGGED) {
			$this->prefix = LOGGED_TYPE.'\\';
		}
		$class = $runLevel.'\\' . $this->prefix . $this->getClassName($actionOffset);
		
		if (!class_exists($class)) {
			$class = $runLevel.'\\' . $this->getClassName($actionOffset);
			
			if (!class_exists($class)) {
				$class = $runLevel.'\\index';
			}
		}
		
		$class = new $class;
		
		$class->run();
		
		$method = $this->getMethodName($actionOffset+1);
		if (method_exists($class, $method)) {
			$class->$method();
		}
		
		$class->render();
		$class->end();
	}
	
	private function getClassName($offset)
	{
		return Url::getPart($offset) ? Url::getPart($offset) : 'index';
	}
	
	private function getMethodName($offset)
	{
		return Url::getPart($offset) ? Url::getPart($offset) : '_INDEX';
	}
}
