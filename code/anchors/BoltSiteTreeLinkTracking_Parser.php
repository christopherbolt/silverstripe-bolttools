<?php

class BoltSiteTreeLinkTracking_Parser extends SiteTreeLinkTracking_Parser {
	
	public function process(SS_HTMLValue $htmlValue) {
		$results = parent::process($htmlValue);
		for($i=0;$i<count($results);$i++) {
			if ($results[$i]['Type'] == 'sitetree' && $results[$i]['Anchor'] && $results[$i]['Broken']) {
				$page = DataObject::get_by_id('SiteTree', $results[$i]['Target']);
				if ($page && !in_array($results[$i]['Anchor'], BoltHtmlEditorField_Toolbar::getPageAnchors($page))) {
					$results[$i]['Broken'] = true;
				} else {
					$results[$i]['Broken'] = false;
				}
			}
		}
		return $results;
	}
	
}