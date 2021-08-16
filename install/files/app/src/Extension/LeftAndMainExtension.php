<?php

namespace MySite\Extension;

use SilverStripe\Admin\LeftAndMainExtension as SS_LeftAndMainExtension;
use SilverStripe\View\Requirements;

class LeftAndMainExtension extends SS_LeftAndMainExtension {

	public function init() {
		parent::init();
		Requirements::javascript('_resources/app/client/javascript/leftandmain.js');
		Requirements::css('_resources/app/client/css/leftandmain.css');
	}

}
