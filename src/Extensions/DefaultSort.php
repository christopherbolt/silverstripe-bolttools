<?php

namespace Christop~erBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\ClassInfo;
 

class DefaultSort extends DataExtension {
	
	function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$owner = $this->owner;
		
		// Get field to sort by
		$sortField = $owner->config()->get('sort_field');
		if (!$sortField) {
			$db = $owner->config()->get('db');
			$common_sort_fields = array('Sort', 'SortOrder');
			foreach ($db as $k => $v) {
				if (in_array($k,$common_sort_fields)) {
					$sortField = $k;
					break;
				}
			}
		}
		
		// If not sorted then sort it
		if (!$owner->$sortField) {
			
			// Get parent id
			$parentID = $owner->config()->get('sort_parent');
			if (!$parentID) {
				$has_one = $owner->config()->get('has_one');
				$common_parent_classes = ClassInfo::subclassesFor('SiteTree');
				$common_parent_classes[] = 'SiteConfig';
				$common_parent_classes[] = 'ContentModuleArea';
				if ($has_one && is_array($has_one)) {
					foreach ($has_one as $k => $v) {
						if (in_array($v,$common_parent_classes)) {
							$parentID = $k;
							break;
						}
					}
				}
			}
			if ($parentID && substr($parentID, strlen($parentID)-2) != 'ID') $parentID .= 'ID';
			
			// Apply sort order
			if ($parentID && !empty($owner->$parentID) && ($list = $owner::get()->filter(array($parentID => $owner->$parentID)))) {
				$owner->SortOrder = $list->max($sortField) + 1;
			} else {
				$owner->SortOrder = $owner::get()->max($sortField) + 1;
			}
		}
	}
}