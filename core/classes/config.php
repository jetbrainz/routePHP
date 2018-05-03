<?php

/**
 * Description of config
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Config
{
    const DS = DIRECTORY_SEPARATOR;

	protected $config = array();
	
    public function __construct()
    {
        $class = strtolower(str_replace('\\', DS, get_class($this)));

        if (defined ('BRAND')) {
            $file_brand = PATH_ETC.DS.'brands'.DS.BRAND.DS.$class.'.php';
        }
        if (defined('DEVMODE') && DEVMODE && file_exists(PATH_ETC.DS.$class.'.local.php')) {
            // Try to load dev (local) config if exists and DEVMODE = true
            $file = PATH_ETC.DS.$class.'.local.php';
        } else {
            $file = PATH_ETC.DS.$class.'.php';
        }

        $local_config = array ();

        if (file_exists($file)) {
            $config = array();
            include $file;
            $local_config = array_merge ($local_config, $config);
        }
        if (file_exists($file_brand)) {
            include $file_brand;
            $local_config = array_merge_recursive_distinct ($local_config, $config);
        }

        if (defined('DEVMODE') && DEVMODE && file_exists(PATH_ETC.'/global.local.php')) {
            $file = PATH_ETC.DS.'global.local.php';
        } else {
            $file = PATH_ETC.DS.'global.php';
        }

        if (file_exists($file)) {
            include $file;
            $config = array_merge($config, $local_config);
        }

        if (isset ($config)) {
            foreach ($config as $k=>$v) {
                $this->config[$k] = $v;
            }
        }
    }
	
	protected function getConfig($name)
	{
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		return null;
	}
	
	public function getVar($group, $name, $lang=null)
	{
		$path = array ();

		$path[] = realpath(PATH_VAR.DS.$group.DS.str_replace('\\', DS, strtolower(get_class($this))).DS.$name);
		$path[] = realpath(PATH_VAR.DS.$group.DS.str_replace('\\', DS, strtolower(get_class($this))).DS.BRAND.DS.$name);
		$path[] = realpath(PATH_VAR.DS.$group.DS.$name);
		$path[] = realpath(PATH_VAR.DS.$group.DS.BRAND.DS.$name);
		
		foreach ($path as $p) {
			if (!$p) {
				continue;
			}
			if ($lang) {
				$pl = str_replace(DS.$name, DS.$lang.DS.$name, $p);
				if (($ret = $this->returnVar($pl)) !== null) {
					return $ret;
				}
			}
			if (($ret = $this->returnVar($p)) !== null) {
				return $ret;
			}
		}
		return null;
	}
	
	private function returnVar($path)
	{
		if (empty ($path)) {
			return false;
		}
		if (stristr(PATH_VAR, $path) === null) {
			return false;
		}
		if (file_exists($path)) {
			return file_get_contents($path);
		}
		return null;
	}
}
