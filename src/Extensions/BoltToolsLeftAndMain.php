<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\Core\Extension;

/**
 * Plug-ins for additional functionality in your LeftAndMain classes.
 * 
 * @package framework
 * @subpackage admin
 */
class BoltToolsLeftAndMain extends Extension {

	protected function onInit() {
		Requirements::css('christopherbolt/silverstripe-bolttools: client/css/bolttools.css');
	}
}
