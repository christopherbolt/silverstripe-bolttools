<?php 

class BoltSiteTree extends DataExtension {
	private static $db = array(
		'MetaTitle' => 'Varchar(255)',
		'ShowInSiteMap' => 'Boolean'
	);
	
	//private static $defaults = array(
	//	'ShowInSiteMap' => 1
	//);
	public function populateDefaults() {
		$defaults = $this->owner->config()->get('defaults');
		if (isset($defaults['ShowInSiteMap']) && $defaults['ShowInSiteMap'] == 0) {
			$this->owner->ShowInSiteMap = 0;
		} else if ($this->owner->ClassName != 'ErrorPage' && $this->owner->ClassName != 'BlogEntry') {
			$this->owner->ShowInSiteMap = 1;
		}
		parent::populateDefaults();
	}
	
	public function updateSettingsFields(FieldList $fields) {
    	$fields->addFieldToTab("Root.Settings", new CheckboxField('ShowInSiteMap', 'Show in site map?'), 'ShowInSearch');
	}
	
	public function updateCMSFields(FieldList $fields) {		
		// Add back metatitle
		$fields->fieldByName('Root.Main.Metadata')->insertBefore($metaTitle = new TextField('MetaTitle', 'Meta Title'), 'MetaDescription');
		$metaTitle->setDescription('Browsers will display this in the title bar and search engines use this for displaying search results (although it may not influence their ranking).');
	}
	
	/* hack to set defaults on ErrorPage */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->owner->ID && $this->owner->ClassName == 'ErrorPage') $this->owner->ShowInSiteMap = 0;
	}
	
	function SanitizedURLSegment() {
		return preg_replace("/[^a-z0-9]/i", "", $this->owner->URLSegment);	
	}
	
	/* function for site map */
	function SiteMapChildren() {
		return BoltSiteMap::getSiteMapChildrenOf($this->owner->ID);
	}
	
	// function for adding and combing css and js files
	public static $themeFolderAndSubfolder;
	public static function setupRequirements($cssArray=array(), $jsArray=array()) {
		
		// Don't combine files if in admin to prevent error on "login as someone else" screen
		$inAdmin = is_subclass_of(Controller::curr(), "LeftAndMain");
		
		// Setup requirements	
		if (isset(Page_Controller::$themeFolderAndSubfolder) && 	Page_Controller::$themeFolderAndSubfolder) {
			self::$themeFolderAndSubfolder = Page_Controller::$themeFolderAndSubfolder;
		} else {
			$currentTheme = SSViewer::current_theme();
			self::$themeFolderAndSubfolder = 'themes/'.$currentTheme;
		}
		
		//Set a custom combined folder under themes so relative paths to images within CSS and JavaScript files don't break
        Requirements::set_combined_files_folder(self::$themeFolderAndSubfolder . '/combined');
		
		// CSS array
		if (count($cssArray)) {
			foreach($cssArray as $css) {
				Requirements::css($css);
			}
			if (!$inAdmin) Requirements::combine_files("combined.css",$cssArray);
		}
		
		// Javascript array
		if (count($jsArray)) {
			foreach($jsArray as $js) {
				Requirements::javascript($js);
			}
			if (!$inAdmin) Requirements::combine_files("combined.js", $jsArray);
		}
 
		if (!$inAdmin) Requirements::process_combined_files();
		
		// Google analytics
		if(!Director::isDev())  {
			$siteConfig = SiteConfig::current_site_config();
			if (isset($siteConfig->GoogleAnalyticsCode))
				Requirements::insertHeadTags(SiteConfig::current_site_config()->GoogleAnalyticsCode);
		}
		// End Google analytics	
		
	}
}
class BoltSiteTree_Controller extends Extension {
	/* Some helper functions for templates */
	function PageById($id) {
		return Page::get()->byId($id);	
	}
	function AllPages() {
		return Page::get();	
	}
}
