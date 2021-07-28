<?php

namespace MySite\Extension;

use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\View\Requirements;

class LeftAndMainExtension extends LeftAndMainExtension {

	public function init() {
		parent::init();
		Requirements::javascript('app: client/javascript/leftandmain.js');
		Requirements::css('app: client/css/leftandmain.css');
	}

}
