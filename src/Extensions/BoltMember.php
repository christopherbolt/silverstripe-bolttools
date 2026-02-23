<?php

namespace ChristopherBolt\BoltTools\Extensions;

use Silverstripe\Core\Extension;
use SilverStripe\Forms\FieldList;


class BoltMember extends Extension {
	public function updateCMSFields(FieldList $fields) {
		if ($this->owner->config()->get('hide_blog_profile_summary')) $fields->removeByName('BlogProfileSummary');
		if ($this->owner->config()->get('hide_blog_profile_image')) $fields->removeByName('BlogProfileImage');
	}
}