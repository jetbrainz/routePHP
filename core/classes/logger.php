<?php

/**
 * Description of logger
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Logger extends Config
{
	const DEBUG		= 'DEBUG';
	const WARNING	= 'WARNING';
	const ERROR		= 'ERROR';
	const FATAL		= 'FATAL';
	
	private $levels = array(
		self::DEBUG, self::WARNING, self::ERROR, self::FATAL
	);
	
	private $log = null;
	
	private $message = null;
	
	public function __construct($className)
	{
	    parent::__construct();

	    $gelf = $this->getConfig('gelf');

		$logName = str_replace ('\\', '-', $className);

		$this->log = new Monolog\Logger($className);
		
		$q = new Queue('include logging' && false);

        	$this->log->pushHandler(new Monolog\Handler\StreamHandler(PATH_LOG.'/'.$logName, Monolog\Logger::DEBUG));
	        $this->log->pushHandler(new Monolog\Handler\StreamHandler(PATH_LOG.'/errors', Monolog\Logger::ERROR));

        	if (!empty($gelf)) {
            		$gelfHandler = new Monolog\Handler\GelfHandler(
                		new Gelf\Publisher(
                	    		new Gelf\Transport\UdpTransport($gelf['url'], $gelf['port'])
	                	),
        	        	Monolog\Logger::DEBUG
            		);

            		$this->log->pushHandler($gelfHandler);
	        }
	}
	
	public function __call($method, $args)
	{
		$callers = debug_backtrace();

		$this->message = $args[0];

        $context = [];
        if (LOGGED) {
            $context['user_id'] = LOGGED;
        }
        if (LOGGED_EMAIL) {
            $context['user_email'] = LOGGED_EMAIL;
        }

		$method = strtolower($method);
		try {
			if (in_array (strtoupper($method), $this->levels)) {
				return $this->log->$method(
                    "{$args[0]}\n{$callers[2]['file']}:{$callers[1]['line']}",
                    $context
				);
			}
		} catch (\Exception $e) {
		    return false;
		}
	}
	
	public function getCurrentMessage()
	{
		return $this->message;
	}
}
