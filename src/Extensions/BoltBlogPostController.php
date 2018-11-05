<?php

namespace ChristopherBolt\BoltTools\Extensions;

use ChristopherBolt\BoltTools\Extensions\BoltBlogController;


class BoltBlogPostController extends BoltBlogController {
	
	function getRSSLink() {
		return $this->owner->Parent()->Link('rss');
	}
}