<?php
/**
 * Description of menu
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Menu {
	
	public static function tabs($links, $class=null, $header=null)
	{
		$class = $class ? $class : 'nav-tabs';
		$menu = '<ul class="nav '.$class.'">';
		
		if ($header) {
			$menu .= '<li class="nav-header">'.$header.'</li>';
		}
		$haveActive = false;
		foreach ($links as $link=>$text) {
			if (stristr(\Url::getPath(), $link) !== false) {
				// we have acive item
				$haveActive = $link;
				break;
			}
		}
		foreach ($links as $link=>$text) {
			if (!$haveActive) {
				$haveActive = $link;
			}
			$menu .= 
				'<li class="nav-item' .
				(($haveActive == $link) ? ' active' : '' ) .
				'"><a class="nav-link' .
				(($haveActive == $link) ? ' active' : '' ) .
				'" href="' . $link . '">' . $text . '</a>' .
				'</li>';
		}
		$menu .= '</ul>';
		
		return $menu;
	}
}
