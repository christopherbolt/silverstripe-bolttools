<?php

/* adds the search index to SiteTree */

class BoltSearchIndexedSiteTree extends DataExtension {
		
	private static $db = array(
		'SearchIndex' => 'HTMLText',
	);
	
	private static $search_index = array(
		'MetaTitle',
		//'MenuTitle',
		//'Title',
		//'MetaDescription',
		//'Content',
	);
	
	static function buildSearchIndex($item) {
		$index = array();
		$fields = $item->config()->get('search_index');
		if ($fields && count($fields)) {
			$db = $item->config()->get('db');
			$has_one = $item->config()->get('has_one');
			$has_many = $item->config()->get('has_many');
			$many_many = $item->config()->get('many_many');
			$translatable = ($item::has_extension('TranslatableHasOne') || $item::has_extension('TranslatableDataObject')) ? true : false;
			$translatableHasOne = $item::has_extension('TranslatableHasOne');
			foreach ($fields as $f) {
				try {
					if (isset($db[$f])) {
						if ($translatable && $item->isLocalizedField($f)) {
							$index[] = 	$item->getLocalizedValue($f);
						} else {
							$index[] = 	$item->$f;
						}
					} else if (isset($has_one[$f])) {
						$object = $item->$f();
						if ($object && !is_subclass_of($object, 'SiteTree')) { // because better to show the page itself in results?
							if ($object->config()->get('search_index')) {
								$index[] = self::buildSearchIndex($object);
							} else {
								self::buildGeneric($object, $index);
							}
						}
					} else if (isset($has_many[$f]) || isset($many_many[$f])) {
						if ($item::has_extension('TranslatableUtility')) {
							$set = $item->Master()->$f();
						} else {
							$set = $item->$f();
						}
						if ($set) { 
							foreach ($set as $object) {
								if (!is_subclass_of($object, 'SiteTree')) { // because better to show the page itself in results?
									if ($object->config()->get('search_index')) {
										$index[] = self::buildSearchIndex($object);
									} else /*if (!is_subclass_of($object, 'Page'))*/ {
										self::buildGeneric($object, $index);
									}
								}
							}
						}
					// Allow index to be built from a method
					} else if ($item->hasMethod($f)) {
						$data = $item->$f();
						if (is_string($data) || is_a($data, 'DBField')) {
							$index[] = $item->$data;
						} else if (is_subclass_of($data, 'DataObject')) {
							$object = $data;
							if (!is_subclass_of($object, 'SiteTree')) { // because better to show the page itself in results?
								if ($object->config()->get('search_index')) {
									$index[] = self::buildSearchIndex($object);
								} else {
									self::buildGeneric($object, $index);
								}
							}
						} else if (is_a($data, 'DataList')) {
							$set = $data;
							foreach ($set as $object) {
								if (!is_subclass_of($object, 'SiteTree')) { // because better to show the page itself in results?
									if ($object->config()->get('search_index')) {
										$index[] = self::buildSearchIndex($object);
									} else /*if (!is_subclass_of($object, 'Page'))*/ {
										self::buildGeneric($object, $index);
									}
								}
							}
						}
					}
				} catch (Exception $e) {
					// we need to log exceptions or something.
				}
			}
		}
		return implode("\n", $index);
	}
	
	static function buildGeneric($object, &$index) {
		$generic = array('Title', 'Name', 'Content');
		$translatable = ($object::has_extension('TranslatableHasOne') || $object::has_extension('TranslatableDataObject')) ? true : false;
		foreach ($generic as $g) {
			if ($translatable && $object->isLocalizedField($g)) {
				$index[] = 	$object->getLocalizedValue($g);
			} else {
				if (isset($object->$g)) $index[] = $object->$g;
			}
		}
	}
	
	/* only used for testing, will remove in the future */
	function getTheBuildSearchIndex() {
		return self::buildSearchIndex($this->owner);
	}
	
	/* updates search index, does not save */
	function updateSearchIndex() {
		$this->owner->SearchIndex = self::buildSearchIndex($this->owner);
	}
	
	/* Updates the search index on a stage, useful if search index needs to be changed outside of the page */
	function writeSearchIndexOnStage($stage="Stage") {
		$this->owner->writeToStage($stage); // Mmmm this seems too easy?
	}
	function writeSearchIndexOnBothStages() {
		$this->writeSearchIndexOnStage("Stage");
		$this->writeSearchIndexOnStage("Live");
	}
	
	function onBeforeWrite() {
		// update search index
		$this->updateSearchIndex();
		parent::onBeforeWrite();
	}
}

?>