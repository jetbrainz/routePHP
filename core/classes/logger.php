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

        require_once PATH_EXT.'/monolog/vendor/autoload.php';

		$this->log = new Monolog\Logger($className);
		
		$q = new Queue('include logging' && false);

        $graylogServer = 'services.fxgrow.com';

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

		if (!preg_match ('/mail/i', $className)) {
			require_once PATH_EXT.'/monolog_extend/QueueMailerHandler.php';
			$this->log->pushHandler(new \Monolog\Handler\QueueMailerHandler($q, Monolog\Logger::ERROR));
		}
		/*
		$mailer = new \Mailer('include logging'==false);
		$swift = $mailer->getSwiftMailer();
		$message = Swift_Message::newInstance('subj')
					->setTo(ADMINEMAIL)
					->setFrom(ADMINEMAIL);
		
		$this->log->pushHandler(new \Monolog\Handler\SwiftMailerHandler($swift, $message, Logger::ERROR));
		 * 
		 */
	}
	
	public function __call($method, $args)
	{
		$callers = debug_backtrace();

		$this->message = $args[0];
		
		$method = strtolower($method);
		try {
			if (in_array (strtoupper($method), $this->levels)) {
				$this->log->$method(
					$callers[2]['function'].':'.$callers[1]['line'].' - '
					.$args[0]
				);
			}
		} catch (\Exception $e) {

		}
	}
	
	public function getCurrentMessage()
	{
		return $this->message;
	}
}
