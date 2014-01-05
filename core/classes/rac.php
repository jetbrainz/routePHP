<?php

/**
 * Route Access Control
 * Object Oriented Access List Control Class
 *
 * @author valentin
 */
class RAC extends Config
{
	private $logger=null;
	
	public function __construct()
	{
		$this->logger = new Logger(get_class());
	}
	
	public function getRoutesList()
	{
		$objects = $this->scanRoutes(PATH_ROUTES);
		//print_r ($objects);
	}
	
	private function scanRoutes($path)
	{
		$ret = array ();
		if (is_dir($path)) {
			$d = @dir ($path);
			if (!$d) {
				return null;
			}
			while (false !== ($entry = $d->read())) {
				if ($entry == '.' || $entry == '..') {
					continue;
				}
				$ret[$entry] = $this->scanRoutes($path.'/'.$entry);
			}
			return $ret;
		} else {
			// Scan for all methods
			$namespace = '\routes\\'.str_replace('/', '\\', str_replace(PATH_ROUTES.'/', '', $path));
			$classname = str_replace('.php', '', $namespace);
			$methods = get_class_methods($classname);
			return $methods;
		}
	}
}
