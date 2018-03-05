<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\Admin\LeftAndMainExtension;

/**
 * Plug-ins for additional functionality in your LeftAndMain classes.
 * 
 * @package framework
 * @subpackage admin
 */
class BoltToolsLeftAndMain extends LeftAndMainExtension {

	public function init() {
		parent::init();
		Requirements::css('christopherbolt/silverstripe-bolttools: client/css/bolttools.css');
	}
	
	//public function accessedCMS() {
	//}
	
	//public function augmentNewSiteTreeItem(&$item) {
	//}

}
