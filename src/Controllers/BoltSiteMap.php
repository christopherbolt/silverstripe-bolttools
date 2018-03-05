<?php

namespace ChristopherBolt\BoltTools\Controllers;

use PageController;
use Page;


class BoltSiteMap extends PageController {
	
	function init() {
		// This hack prevents the ContentController from restricting access when in draft site
		$this->URLSegment = 'Security';
		parent::init();
	}
	
	function index() {
		$data = array(
	      	'Title' => 'Site Map',
			'MenuTitle' => 'Site Map',
			'MetaTitle' => 'Site Map',
	  	);
	  	return $this->customise($data)->renderWith(array('SiteMap', 'Page'));
	}
	
	static function getSiteMapChildrenOf($parent=0, $excludeClasses=array('ErrorPage','PopupPage','SiteMap')) {
		$level = Page::get()->filter(array(
			'ParentID' => $parent, 
			'ShowInSiteMap' => 1,
		))->exclude(array(
			'ClassName' => $excludeClasses,
		));
		return $level;
	}
}