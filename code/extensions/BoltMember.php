<?php

class BoltMember extends DataExtension {
	public function updateCMSFields(FieldList $fields) {
		if ($this->owner->config()->get('hide_blog_profile_summary')) $fields->removeByName('BlogProfileSummary');
		if ($this->owner->config()->get('hide_blog_profile_image')) $fields->removeByName('BlogProfileImage');
	}
}