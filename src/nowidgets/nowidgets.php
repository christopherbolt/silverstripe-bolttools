<?php

namespace SilverStripe\Widgets\Model;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;
use SilverStripe\Blog\Model\Blog;

class Widget extends DataObject {
	function Blog() {
		if (empty($this->BlogID)) {
			$entry = Director::get_current_page();
			if (is_a($entry, 'SilverStripe\Blog\Model\BlogPost')) {
				$this->BlogID = $entry->Parent()->ID;
			} else if (is_a($entry, 'SilverStripe\Blog\Model\Blog')) {
				$this->BlogID = $entry->ID;
			} else {
				$this->BlogID = Blog::get()->First()->ID;
			}
			
		}
		return Blog::get()->byId($this->BlogID);
	}
}
class WidgetController { }