<?php

namespace ChristopherBolt\BoltTools\Extensions;

use Silverstripe\Core\Extension;


class BoltDBFieldExtension extends Extension {
	function URLEncodeSpaces() {
		return str_replace(' ', '%20', $this->owner->value);
	}
}