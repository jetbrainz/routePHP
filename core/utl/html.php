<?php

/**
 * Description of html
 *
 * @author Valentin Balt <valentin.balt@gmail.com>
 */
class HTML
{
	public static function autoJS($prefix='/js/auto', $part=1)
	{
		$fn = $prefix.'/'.\Url::getPart($part).'.js';
		if (file_exists(PATH_WWW.$fn)) {
			return '<script type="text/javascript" src="'.$fn.'"></script>';
		}
		return '';
	}
}
