<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use ChristopherBolt\BoltTools\Middleware\AddTrackingScriptsMiddleware;
use SilverStripe\Core\Config\Config;

class BoltContentController extends Extension {
	public static function onAfterInit() {
		// Tracking scripts
		if(Director::isLive())  {
            Config::inst()->set(AddTrackingScriptsMiddleware::class, 'enabled', true);
		}
		
		// Prevent indexing of draft sites
		if (Director::isDev() || Director::isTest() || stristr($_SERVER['HTTP_HOST'], 'draftsite.co.nz')) {
			Requirements::insertHeadTags('<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">');
		}
		
		Requirements::set_force_js_to_bottom(true);
		Requirements::block('silverstripe/admin: thirdparty/jquery/jquery.js');
		Requirements::block('silverstripe/admin: thirdparty/jquery/jquery.min.js');
	}
}
