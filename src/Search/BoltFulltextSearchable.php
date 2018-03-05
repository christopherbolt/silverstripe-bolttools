<?php

namespace ChristopherBolt\BoltTools\Search;

use SilverStripe\ORM\Search\FulltextSearchable;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\Connect\MySQLSchemaManager;
use SilverStripe\Core\Exception;
use SilverStripe\CMS\Controllers\ContentController;


/* modified search extension to work with custom search index */

class BoltFulltextSearchable extends FulltextSearchable {

	public static function enable($searchableClasses = [SiteTree::class])// Chris Bolt, removed file
    {
        $defaultColumns = array(
			// Chris Bolt, removed file, added "SearchIndex"
            SiteTree::class => ['Title','MenuTitle','Content','MetaDescription','SearchIndex'],
        );
		
		// Chris Bolt add site tree extension
		SiteTree::add_extension('ChristopherBolt\\BoltTools\\Search\\BoltSearchIndexedSiteTree');

        if (!is_array($searchableClasses)) {
            $searchableClasses = array($searchableClasses);
        }
        foreach ($searchableClasses as $class) {
            if (!class_exists($class)) {
                continue;
            }

            if (isset($defaultColumns[$class])) {
                $class::add_extension(sprintf('%s(%s)', static::class, "'" . implode("','", $defaultColumns[$class]) . "''"));
            } else {
                throw new Exception(
                    "FulltextSearchable::enable() I don't know the default search columns for class '$class'"
                );
            }
        }
        self::$searchable_classes = $searchableClasses;
        if (class_exists("SilverStripe\\CMS\\Controllers\\ContentController")) {
            ContentController::add_extension("SilverStripe\\CMS\\Search\\ContentControllerSearchExtension");
        }
    }
	
	public static function get_extra_config($class, $extensionClass, $args)
    {
        return array(
            'indexes' => array(
                'SearchFields' => array(
                    'type' => 'fulltext',
                    'name' => 'SearchFields',
                    'columns' => $args,
                )
            )
        );
    }

}
