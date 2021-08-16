<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\Core\Extension;

class BoltLinkField extends Extension {
    public function updateLinkForm(&$form) {
		$form->Actions()->first()->addExtraClass('btn-primary')->addExtraClass('font-icon-save');
	}
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