<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\Core\Extension;

class BoltLinkField extends Extension {
	// replacement for link object for use in the CMS template so that object is displayed when new
    public function getBetterLinkObject()
    {
        $object = $this->owner->getLinkObject();
		if (!$object) {
			return ($this->owner->Value());
		}
       return $object;
    }
}