<?php

/* 
Adds a linkingmode function for simple highlighting of the current category
*/

class BoltBlogCategory extends DataExtension{
	
	function BlogLink() {
		if (empty($this->owner->BlogID)) {
			$entry = Director::get_current_page();
			if (is_a($entry, 'BlogPost')) {
				$this->owner->BlogID = $entry->Parent()->ID;
			} else if (is_a($entry, 'Blog')) {
				$this->owner->BlogID = $entry->ID;
			} else {
				$this->owner->BlogID = Blog::get()->First()->ID;
			}
			
		}
		return Blog::get()->byId($this->owner->BlogID)->Link();
	}
	
	function LinkingMode() {
		
		$entry = Director::get_current_page();
		if (is_a($entry, 'Blog')) {
			$currentCategory = Controller::curr()->getCurrentCategory();
			if ($currentCategory) {
				if ($currentCategory->ID == $this->owner->ID) {
					return 'current';	
				}
			} else if (!$this->owner->ID) {
				// this must be the allcat
				return 'current';	
			}
		}

	}
	
}