<?php

class Widget extends DataObject {
	function Blog() {
		if (empty($this->BlogID)) {
			$entry = Director::get_current_page();
			if (is_a($entry, 'BlogPost')) {
				$this->BlogID = $entry->Parent()->ID;
			} else if (is_a($entry, 'Blog')) {
				$this->BlogID = $entry->ID;
			} else {
				$this->BlogID = Blog::get()->First()->ID;
			}
			
		}
		return Blog::get()->byId($this->BlogID);
	}
}
class Widget_Controller { }