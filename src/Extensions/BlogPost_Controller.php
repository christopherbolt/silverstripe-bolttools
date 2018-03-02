<?php

namespace ChristopherBolt\BoltTools\Extensions;

use ChristopherBolt\BoltTools\Extensions\BoltBlog_Controller;


class BoltBlogPost_Controller extends BoltBlog_Controller {
	
	function getRSSLink() {
		return $this->owner->Parent()->Link('rss');
	}
}