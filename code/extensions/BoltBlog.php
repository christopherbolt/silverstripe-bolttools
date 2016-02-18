<?php

/* 
Used to allow easy hiding of features not used in the site
Adds functions for easy display of widgets without widget holder
*/

if(!class_exists('Widget')) {
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
}

class BoltBlog extends DataExtension{
	
	public function updateCMSFields(FieldList $fields) {
		
		if ($this->owner->config()->get('hide_categories')) $fields->removeByName('Categories');
		
		if ($this->owner->config()->get('hide_tags')) $fields->removeByName('Tags');
		
		if ($this->owner->config()->get('hide_categories') && $this->owner->config()->get('hide_tags')) 
			$fields->removeByName('Categorisation');
		
		if ($this->owner->config()->get('hide_content')) $fields->removeByName('Content');
		
		if ($this->owner->config()->get('hide_image')) $fields->removeByName('FeaturedImage');
		
	}
	
	// This does not work, Silverstripe is calling this before getSettingsFields on the owner rather than after. Is this a bug?
	public function updateSettingsFields(FieldList $fields) {
		if ($this->owner->config()->get('hide_users')) $fields->removeByName('Users');
	}
	
}

class BoltBlog_Controller extends DataExtension {
	
	/* Category widget */
	function getCategoriesList($limit=0,$order='Title',$direction='DESC') {
		$w = new BlogCategoriesWidget();
		$w->Limit = $limit;
		$w->Order = $order;
		$w->Direction = $direction;
		return $w->getCategories();
	}
	function getAllCat () {
		$allCat = new BlogCategory();
		$allCat->Title = 'All';
		return $allCat;
	}
	
	/* Tags widget */
	function getTagsList($limit=0,$order='Title',$direction='DESC') {
		$w = new BlogTagsWidget();
		$w->Limit = $limit;
		$w->Order = $order;
		$w->Direction = $direction;
		return $w->getTags();
	}
	
	/* Recent posts widget */
	function getRecentPosts($numberOfPosts=5) {
		$w = new BlogRecentPostsWidget();
		$w->NumberOfPosts = $numberOfPosts;
		return $w->getPosts();
	}
	
	/* Archive widget */
	function getArchiveList($archiveType='Yearly', $numberToDisplay=0) {
		$w = new BlogArchiveWidget();
		$w->ArchiveType = $archiveType;
		$w->NumberToDisplay = $numberToDisplay;
		
		// Hack to get this working in mysql 5.7, seems that even if I turn ONLY_FULL_GROUP_BY off in my.cnf silverstripe turns it on again somewhere.
		// Get current mode
		$mode = DB::query('SELECT @@sql_mode')->value();
		// Do not remove all modes or Silverstripe will break
		// Remove both the ONLY_FULL_GROUP_BY and ANSI modes, enabling ANSI seems to automatically add ONLY_FULL_GROUP_BY
		$sql = 'SET sql_mode=\''.preg_replace('/(^|,)(ONLY_FULL_GROUP_BY)($|,)/smi', "$3", preg_replace('/(^|,)(ANSI)($|,)/smi', "$3", $mode)).'\';';
		DB::query($sql);
		
		$return = $w->getArchive();
		foreach($return as $item) {} // Make sure SQL query is run
		
		// Return to previous mode incase this affects other queries
		DB::query('SET sql_mode=\''.$mode.'\';');
		// End Hack
		
		return $return;
	}
}