<?php
if (!defined('APP_LEVEL')) {
	echo 'APP_LEVEL is not defined';
	exit;
}

if (!defined('PATH_APP') && (APP_LEVEL == 'WEB' || APP_LEVEL == 'API')) {
	echo 'PATH_APP is not defined';
	exit;
}

if (APP_LEVEL == 'SCHEDULER') {
	if (empty($argv[1])) {
		echo 'PATH_APP should be provided as first argument';
		exit;
	}
	define ('PATH_APP', $argv[1]);
}

spl_autoload_register("MainLoad");

if(in_array("__autoload", spl_autoload_functions()))
	spl_autoload_register("__autoload");

define ('PATH_CORE', __DIR__);

define ('PATH_ETC', realpath(PATH_APP.'/etc'));
define ('PATH_CLASSES', realpath(PATH_CORE.'/classes'));
define ('PATH_MODULES', realpath(PATH_APP.'/modules'));
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
define ('PATH_PROPEL_LIB', PATH_EXT.'/Propel/runtime/lib');
define ('PATH_PROPEL', PATH_APP.'/propel/build');

define ('DEFAULT_COUNTRY', \Country::getCode('Cyprus'));

define ('LOGGED', \Session::get('logged'));
define ('LOGGED_TYPE', \Session::get('logged_type'));
define ('LOGGED_NAME', \Session::get('logged_name'));
define ('LOGGED_EMAIL', \Session::get('logged_email'));

// Include project config
require_once PATH_APP.'/config.php';

// Include Propel
require_once PATH_PROPEL_LIB.'/Propel.php';

try
{
	if (APP_LEVEL == 'WEB') {
		new Dispatcher('routes');
	}
	if (APP_LEVEL == 'API') {
		new Dispatcher('api', 2);
	}
	if (APP_LEVEL == 'SCHEDULER') {
		$daemon = false;
		$args = '';
		foreach ($argv as $k=>$a) {
			if ($a == 'daemon') {
				$daemon = true;
			} elseif ($k) {
				$args .= ($args?' ':'').$a;
			}
		}
		if ($daemon) {
			$wd = __DIR__;
			do {
				// Fresh copy of script each time
				system(__DIR__.'/scheduler.php '.$args);
				//$q->run();
				sleep(1);
			} while(1);
		} else {
			$q = new Queue();
			for ($i=0;$i<15;$i++) {
				$q->run();
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
			} elseif (file_exists ($path.$classNameFile)) {
				include_once $path.$classNameFile;
			} elseif (file_exists ($path.'/'.$classNameFileNaked)) {
				include_once $path.'/'.$classNameFileNaked;
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
		PATH_EXT,
		PATH_EXT.'/monolog/src',
		PATH_PROPEL_LIB,
		PATH_PROPEL.'/classes',
	);

	if (!substr($className, 0, 1) != '\\') {
		$className = '\\' . $className;
	}

	$classNameFile = str_replace ('\\', '/', $className).'.php';

	foreach ($paths as $path) {
		if (file_exists ($path.$classNameFile)) {
			include_once $path.$classNameFile;
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

