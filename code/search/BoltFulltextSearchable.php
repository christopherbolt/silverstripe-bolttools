<?php

/* modified search extension to work with custom search index */

class BoltFulltextSearchable extends FulltextSearchable {

	public static function enable($searchableClasses = array('SiteTree')) {// Chris Bolt, removed file
		// Chris Bolt add site tree extension
		SiteTree::add_extension('BoltSearchIndexedSiteTree');
		
		$defaultColumns = array(
			// Chris Bolt, removed file, added "SearchIndex"
			'SiteTree' => '"Title","MenuTitle","Content","MetaDescription","SearchIndex"',
		);

		if(!is_array($searchableClasses)) $searchableClasses = array($searchableClasses);
		foreach($searchableClasses as $class) {
			if(!class_exists($class)) continue;

			if(isset($defaultColumns[$class])) {
				if (class_exists('MySQLSchemaManager')) {
					Config::inst()->update(
						$class, 'create_table_options', array(MySQLSchemaManager::ID => 'ENGINE=MyISAM')
					);
				} else {
					Config::inst()->update($class, 'create_table_options', array('MySQLDatabase' => 'ENGINE=MyISAM'));
				}
				$class::add_extension("FulltextSearchable('{$defaultColumns[$class]}')");
			} else {
				throw new Exception(
					"FulltextSearchable::enable() I don't know the default search columns for class '$class'"
				);
			}
		}
		self::$searchable_classes = $searchableClasses;
		if(class_exists("ContentController")){
			ContentController::add_extension("ContentControllerSearchExtension");
		}
		
	}
	
	public static function get_extra_config($class, $extensionClass, $args) {
		return array(
			'indexes' => array(
				'SearchFields' => array(
					'type' => 'fulltext',
					'name' => 'SearchFields',
					'value' => $args[0]
				)
			)
		);
	}

}
