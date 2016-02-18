<?php

class BoltShortCodeHelper {
	
	public static function LineBreak ($arguments=array(), $caption="", $parser = null) {
		$classes = array();
		if (isset($arguments['hide'])) {
			$arr = explode (',', $arguments['hide']);
			$classes = array();
			foreach($arr as $k) {
				$k = trim(strtolower($k));
				if ($k == 'bigdesktop') {
					array_push($classes, 'bigDesktopHide');
				} else if ($k == 'desktop') {
					array_push($classes, 'desktopHide');
				} else if ($k == 'tablet') {
					array_push($classes, 'tabletHide');
				} else if ($k == 'mobile') {
					array_push($classes, 'mobileHide');
				} else if ($k == 'mobilelandscape') {
					array_push($classes, 'mobileLandscapeHide');
				} else if ($k == 'mobileportrait') {
					array_push($classes, 'mobilePortraitHide');
				} else {
					array_push($classes, $k);	
				}
			}
		} else if (isset($arguments['show'])) {
			$arr = explode (',', $arguments['show']);
			$classes = array('bigDesktopHide','desktopHide','tabletHide','mobileHide','mobileLandscapeHide','mobilePortraitHide');
			foreach($arr as $k) {
				$k = trim(strtolower($k));
				if ($k == 'bigdesktop') {
					if(($key = array_search('bigDesktopHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else if ($k == 'desktop') {
					if(($key = array_search('desktopHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else if ($k == 'tablet') {
					if(($key = array_search('tabletHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else if ($k == 'mobile') {
					if(($key = array_search('mobileHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else if ($k == 'mobilelandscape') {
					if(($key = array_search('mobileLandscapeHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else if ($k == 'mobileportrait') {
					if(($key = array_search('mobilePortraitHide', $classes)) !== false) {
						unset($classes[$key]);
					}
				} else {
					array_push($classes, $k);	
				}
			}
		}
		return '<br class="'.implode(' ', $classes).'">';
	}
	
}