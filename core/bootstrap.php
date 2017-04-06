<?php
if (!\RunLevel::defined()) {
	echo 'RunLevel is not defined';
	exit;
}

if (!defined('PATH_APP') && (\RunLevel::isWEB() || \RunLevel::isAPI())) {
	echo 'PATH_APP is not defined';
	exit;
}

if (\RunLevel::isSCHEDULER() || \RunLevel::isCOMMAND()) {
	if (empty($argv[1])) {
		echo 'PATH_APP should be provided as first argument';
		exit;
	}
	define ('PATH_APP', $argv[1]);
}

if (\RunLevel::isPHPUNIT()) {
	define ('PATH_APP', getcwd());
}

spl_autoload_register("MainLoad");

if(in_array("__autoload", spl_autoload_functions()))
	spl_autoload_register("__autoload");

define ('PATH_CORE', __DIR__);

$remoteIP = 
	!empty ($_SERVER['HTTP_X_FORWARDED_FOR'])
	? $_SERVER['HTTP_X_FORWARDED_FOR']
	: (
		!empty ($_SERVER['REMOTE_ADDR'])
		? $_SERVER['REMOTE_ADDR']
		: ''
	);
}
if (filter_var($remoteIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
	$remoteIP = file_get_contents('https://api.ipify.org'));
}
define ('REMOTE_ADDR', $remoteIP);

define ('PATH_ETC', realpath(PATH_APP.'/etc'));
define ('PATH_CLASSES', realpath(PATH_CORE.'/classes'));
define ('PATH_MODULES', realpath(PATH_APP.'/modules'));
define ('PATH_BUSINESS', realpath(PATH_APP.'/business'));
define ('PATH_CORE_MODULES', realpath(PATH_CORE.'/modules'));
define ('PATH_ROUTES', realpath(PATH_APP.'/routes'));
define ('PATH_API', realpath(PATH_APP.'/api'));
define ('PATH_VIEWS', realpath(PATH_APP.'/views'));
define ('PATH_EXT', realpath(PATH_CORE.'/ext'));
define ('PATH_UTL', realpath(PATH_APP.'/utl'));
define ('PATH_CORE_UTL', realpath(PATH_CORE.'/utl'));
define ('PATH_WWW', realpath(PATH_APP.'/www'));
define ('PATH_VAR', realpath(PATH_APP.'/var'));
define ('PATH_LOG', realpath(PATH_VAR.'/log'));
define ('PATH_LIB', realpath(PATH_APP.'/lib'));
define ('PATH_PROPEL_LIB', PATH_EXT.'/Propel/runtime/lib');
define ('PATH_PROPEL', PATH_APP.'/propel/build');

define ('DEFAULT_COUNTRY', \Country::getCode('Cyprus'));

define ('LOGGED', \Session::get('logged'));
define ('LOGGED_TYPE', \Session::get('logged_type'));
define ('LOGGED_ACL', \Session::get('logged_acl'));
define ('LOGGED_NAME', \Session::get('logged_name'));
define ('LOGGED_EMAIL', \Session::get('logged_email'));

// Include project config
require_once PATH_APP.'/config.php';

try
{
	if (\RunLevel::isWEB()) {
		new Dispatcher('routes');
	}
	if (\RunLevel::isAPI()) {
		new Dispatcher('api', 2);
	}
	if (\RunLevel::isCOMMAND()) {
		// No start point
		$module = $argv[2];
		$command = $argv[3];
		if (class_exists($module)) {
			$module = new $module;
			if (method_exists($module, 'command'.$command)) {
				$module->{'command'.$command}();
			}
		}
	}
	if (\RunLevel::isTESTS()) {
		// No start point
	}
	if (\RunLevel::isPHPUNIT()) {
		//new Dispatcher('tests');
	}
	if (\RunLevel::isSCHEDULER()) {
	    $logger = new \Logger('scheduler');
		$daemon = false;
		$daemon_task = false;
		$args = '';

		foreach ($argv as $k=>$a) {
			if ($a == 'daemon') {
				$daemon = true;
				$daemon_task = false;
			} elseif ($a == 'daemon_task') {
				$daemon_task = true;
				$daemon = false;
			} elseif ($k>1) {
				$args .= ($args?' ':'').$a;
			}
		}

		if ($daemon) {
            $logger->debug("Scheduler started as daemon");
			$q = new Queue();
			$tn = $q->getTaskNames();
			foreach ($tn as $task) {
				exec('ps auxwww|grep "'.PATH_APP.' ' .$task.' daemon_task"|grep -v grep', $output);
				if (empty ($output)) {
                    $logger->debug("Scheduler TASK was not found, start daemon_task...");
					$exec = __DIR__.'/scheduler.php '.PATH_APP.' ' .$task.' daemon_task '.$args.' >/dev/null 2>&1 &';
					system($exec);
				} else {
                    $logger->debug("Scheduler TASK already started: ".$output);
                }
			}
		} elseif ($daemon_task) {
			do {
                $logger->debug("Scheduler started as daemon_task with arguments: ".$args);
				$q = new Queue();
				// Fresh copy of script each time
				$exec = __DIR__.'/scheduler.php '.PATH_APP.' ' .$args;
				system($exec);
				//$q->run();
				sleep(1);
			} while(1);
		} else {
            $logger->debug("Scheduler started as queue processor");
			$q = new Queue();
			$task = null;
			if (!empty($argv[2])) {
				// Here should be a task name
				if (in_array ($argv[2], $q->getTaskNames())) {
					$task = $argv[2];
				}
			}
			for ($i=0;$i<10;$i++) {
                $logger->debug("Run queue: ".json_encode($task));
				$q->run($task);
			}
		}
	}

}
catch (AppException $ex)
{
	// some shit happens
	$logger = new Logger('bootstrap');
	$logger->error("\n".$ex->getTraceAsString());
}
catch (PDOException $ex) {
	$logger = new Logger('sql');
	$logger->error(
		"\n"
		.$ex->getMessage()
		."\n"
		.$ex->getTraceAsString());
}

function MainLoad($className)
{
	$paths = array (
		PATH_MODULES,
		PATH_CORE_MODULES,
		PATH_CLASSES,
		PATH_UTL,
		PATH_CORE_UTL,
		PATH_BUSINESS,
	);

	$className = strtolower($classNameCase = $className);

	if (!substr($className, 0, 1) != '\\') {
		$className = '\\' . $className;
	}

	$classNameFile = str_replace ('\\', '/', $className).'.php';
	$classNameFileNaked = basename($classNameFile);
	$dirNameFile = str_replace ('\\', '/', $className);

	if (preg_match ('|^\/routes|', $classNameFile)) {
		// Load Route
		$classNameFile = str_replace ('/routes', '', $classNameFile);
		if (file_exists (PATH_ROUTES.$classNameFile)) {
			include_once PATH_ROUTES.$classNameFile;
			return;
		}
	} elseif (preg_match ('|^\/api\/|', $classNameFile)) {
		// Load Route
		$classNameFile = str_replace ('/api/', '/', $classNameFile);
		if (file_exists (PATH_API.$classNameFile)) {
			include_once PATH_API.$classNameFile;
			return;
		}
	} else {
		foreach ($paths as $path) {
			if (file_exists ($path.$dirNameFile.$classNameFile)) {
				include_once $path.$dirNameFile.$classNameFile;
			} elseif (file_exists ($path.strtolower($dirNameFile.$classNameFile))) {
				include_once $path.strtolower($dirNameFile.$classNameFile);
			} elseif (file_exists ($path.$classNameFile)) {
				include_once $path.$classNameFile;
			} elseif (file_exists ($path.strtolower($classNameFile))) {
				include_once $path.strtolower($classNameFile);
			} elseif (file_exists ($path.'/'.$classNameFileNaked)) {
				include_once $path.'/'.$classNameFileNaked;
			} elseif (file_exists ($path.'/'.strtolower($classNameFileNaked))) {
				include_once $path.'/'.strtolower($classNameFileNaked);
			} elseif (file_exists (PATH_APP.$classNameFile)) {
				include_once PATH_APP.$classNameFile;
			} elseif (file_exists (PATH_APP.strtolower($classNameFile))) {
				include_once PATH_APP.strtolower($classNameFile);
			}
		}
	}
	ExtLoad($classNameCase);
	if (function_exists ('AppLoader')) {
		AppLoader($classNameCase);
	}
}

function ExtLoad($className) {
	$paths = array (
		PATH_LIB,
		PATH_EXT,
		PATH_EXT.'/monolog/src',
		PATH_EXT.'/PhpAmqpLib',
		PATH_PROPEL_LIB,
		PATH_PROPEL.'/classes',
	);

	if (!substr($className, 0, 1) != '\\') {
		$className = '\\' . $className;
	}
	$className = str_replace ('\\', '/', $className);

	$classNameFile = $className.'.php';

	foreach ($paths as $path) {
		if (file_exists ($path.$classNameFile)) {
			include_once $path.$classNameFile;
		} elseif (file_exists ($path.$className.$classNameFile)) {
			include_once $path.$className.$classNameFile;
		} elseif (file_exists ($path.strtolower($classNameFile))) {
			include_once $path.strtolower($classNameFile);
		} elseif (file_exists ($path.strtolower($className.$classNameFile))) {
			include_once $path.strtolower($className.$classNameFile);
		}
	}
}
function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
	$merged = $array1;

	foreach ( $array2 as $key => &$value ) {
		if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
			$merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
		} else {
			$merged [$key] = $value;
		}
	}

	return $merged;
}

function debugParams($params)
{
	$str = "Arguments BEGIN:\n";
	$str .= print_r ($params, true);
	$str .= 'END';

	return $str;
}

