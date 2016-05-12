<?php

class BoltHtmlEditorField_Toolbar extends HtmlEditorField_Toolbar {
	
	private static $allowed_actions = array(
		'LinkForm',
		'MediaForm',
		'viewfile',
		'getanchors'
	);
	
	public function getanchors() {
		
		$id = (int)$this->getRequest()->getVar('PageID');
		$anchors = array();

		if (($page = Page::get()->byID($id)) && !empty($page)) {
			if (!$page->canView()) {
				throw new SS_HTTPResponse_Exception(
					_t(
						'HtmlEditorField.ANCHORSCANNOTACCESSPAGE',
						'You are not permitted to access the content of the target page.'
					),
					403
				);
			}
			
			// Chris Bolt
			$anchors = self::getPageAnchors($page);
			// End Chris Bolt

		} else {
			throw new SS_HTTPResponse_Exception(
				_t('HtmlEditorField.ANCHORSPAGENOTFOUND', 'Target page not found.'),
				404
			);
		}
		
		return json_encode($anchors);
	}
	
	// Chris Bolt
	public static function getPageAnchors($page) {
		$anchors = array();
		
		$db = $page->config()->get('db');
		foreach($db as $k => $v) {
			if ($v == 'HTMLText') {
				// Similar to the regex found in HtmlEditorField.js / getAnchors method.
				if (preg_match_all("/\s(name|id)=\"([^\"]+?)\"|\s(name|id)='([^']+?)'/im", $page->$k, $matches)) {
					$anchors = array_merge($anchors, array_filter(array_merge($matches[2], $matches[4])));
				}
			}
		}
		
		$has_one = $page->config()->get('has_one');
		if ($has_one && is_array($has_one)) {
			foreach($has_one as $k => $v) {
				if (($obj = $page->obj($k)) && $obj->exists()) {
					$anchors = array_merge($anchors, self::getObjectAnchors($obj));
					if (is_a($obj, 'ContentModuleArea')) {
						if (($modules = $obj->Modules()) && $modules && $modules->count()) {
							foreach ($modules as $module) {
								$anchors = array_merge($anchors, self::getObjectAnchors($module));		
							}
						}
					}
				}
			}
		}
		$has_many = $page->config()->get('has_many');
		if ($has_many && is_array($has_many)) {
			foreach($has_many as $k => $v) {
				if (($list = $page->$k()) && $list && $list->count()) {
					foreach ($list as $obj) {
						$anchors = array_merge($anchors, self::getObjectAnchors($obj));
					}
				}
			}
		}
		
		return $anchors;
		
	}
	public static function getObjectAnchors($obj) {
		$anchors = array();
		if ($obj->hasMethod('Anchor') && ($anchor = $obj->Anchor()) && is_string($anchor)) {
			$anchors[] = $anchor;
		}
		if ($obj->hasMethod('Anchors') && ($anchor = $obj->Anchors()) && is_array($anchor)) {
			$anchors = array_merge($anchors, $anchor);
		}
		$db = $obj->config()->get('db');
		foreach($db as $k => $v) {
			if ($v == 'HTMLText') {
				// Similar to the regex found in HtmlEditorField.js / getAnchors method.
				if (preg_match_all("/\s(name|id)=\"([^\"]+?)\"|\s(name|id)='([^']+?)'/im", $obj->$k, $matches)) {
					$anchors = array_merge($anchors, array_filter(array_merge($matches[2], $matches[4])));
				}
			}
		}
		return $anchors;	
	}
	// End Chris Bolt
}