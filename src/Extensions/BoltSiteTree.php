<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use Page;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use ChristopherBolt\BoltTools\Controllers\BoltSiteMap;
use SilverStripe\Control\Director;

class BoltSiteTree extends DataExtension {
	private static $db = array(
		'MetaTitle' => 'Varchar(255)',
		'ShowInSiteMap' => 'Boolean'
	);
	
	private static $current_cms_page = null;
	
	public static function current_cms_page() {
		if (self::$current_cms_page) {
			return Page::get()->byId(self::$current_cms_page);
		} else {
			return null;	
		}
	}
	
	//private static $defaults = array(
	//	'ShowInSiteMap' => 1
	//);
	public function populateDefaults() {
		$defaults = $this->owner->config()->get('defaults');
		if (isset($defaults['ShowInSiteMap']) && $defaults['ShowInSiteMap'] == 0) {
			$this->owner->ShowInSiteMap = 0;
		} else if ($this->owner->ClassName != 'SilverStripe\ErrorPage\ErrorPage' && $this->owner->ClassName != 'SilverStripe\Blog\Model\BlogPost') {
			$this->owner->ShowInSiteMap = 1;
		}
		parent::populateDefaults();
	}
	
	public function updateSettingsFields(FieldList $fields) {
    	$fields->addFieldToTab("Root.Settings", new CheckboxField('ShowInSiteMap', 'Show on site map page? (only if this site has a sitemap page)'), 'ShowInSearch');
	}
	
	public function updateCMSFields(FieldList $fields) {		
		// Add back metatitle
		if ($fields->fieldByName('Root.Main.Metadata')) {
			$fields->fieldByName('Root.Main.Metadata')->insertBefore($metaTitle = new TextField('MetaTitle', 'Meta Title'), 'MetaDescription');
			$metaTitle->setDescription('Browsers will display this in the title bar and search engines use this for displaying search results (although it may not influence their ranking).');
		}
		
		// Record current CMS page, quite useful for sorting etc
		self::$current_cms_page = $this->owner->ID;
	}
	
	/* hack to set defaults on ErrorPage */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->owner->ID && $this->owner->ClassName == 'SilverStripe\ErrorPage\ErrorPage') $this->owner->ShowInSiteMap = 0;
	}
	
	function SanitizedURLSegment() {
		return preg_replace("/[^a-z0-9\-]/i", "", $this->owner->URLSegment);	
	}
    
    function SanitizedClassName() {
		return preg_replace("/([a-z0-9]+\\\)/i", "", $this->owner->ClassName);	
	}
	
	/* function for site map */
	function SiteMapChildren() {
		return BoltSiteMap::getSiteMapChildrenOf($this->owner->ID);
	}
}
