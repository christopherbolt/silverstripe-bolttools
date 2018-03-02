<?php

namespace ChristopherBolt\BoltTools\Extensions;

use Silverstripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;


/* 
Used to allow easy hiding of features not used in the site
Adds functions for easy display of widgets without widget holder
*/

class BoltBlog extends DataExtension{
	
	public function updateCMSFields(FieldList $fields) {
		
		if ($this->owner->config()->get('hide_categories')) $fields->removeByName('Categories');
		
		if ($this->owner->config()->get('hide_tags')) $fields->removeByName('Tags');
		
		if ($this->owner->config()->get('hide_categories') && $this->owner->config()->get('hide_tags')) 
			$fields->removeByName('Categorisation');
		
		if ($this->owner->config()->get('hide_content')) $fields->removeByName('Content');
		
		if ($this->owner->config()->get('hide_image')) $fields->removeByName('FeaturedImage');
		
	}
	
	// This does not work, Silverstripe is calling this before getSettingsFields on the owner rather than after. Is this a bug?
	public function updateSettingsFields(FieldList $fields) {
		if ($this->owner->config()->get('hide_users')) $fields->removeByName('Users');
	}
	
}
