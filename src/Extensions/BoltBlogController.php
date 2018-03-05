<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Blog\Model\BlogCategory;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\DB;


if(!class_exists('SilverStripe\Widgets\Model\Widget')) {
	include_once(dirname(__DIR__).'/nowidgets/nowidgets.php');
}

class BoltBlogController extends DataExtension {
	
	/* Category widget */
	function getCategoriesList($limit=0,$order='Title',$direction='ASC') {
		$w = new SilverStripe\Blog\Widgets\BlogCategoriesWidget();
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
	function getTagsList($limit=0,$order='Title',$direction='ASC') {
		$w = new SilverStripe\Blog\Widgets\BlogTagsWidget();
		$w->Limit = $limit;
		$w->Order = $order;
		$w->Direction = $direction;
		return $w->getTags();
	}
	
	/* Recent posts widget */
	function getRecentPosts($numberOfPosts=5) {
		$w = new SilverStripe\Blog\Widgets\BlogRecentPostsWidget();
		$w->NumberOfPosts = $numberOfPosts;
		return $w->getPosts();
	}
	
	/* Archive widget */
	function getAllArchive () {
		$linkingMode = '';
		
		if (is_a($this->owner, 'SilverStripe\Blog\Controllers\BlogController')) {
			$blog = $this->owner;
			$year = $blog->getArchiveYear();
			$month = $blog->getArchiveMonth();
			$day = $blog->getArchiveDay();
			
			if (!$year && !$month && !$day/* && !$blog->getCurrentCategory() && !$blog->getCurrentTag()*/) $linkingMode = 'current';
			
		} else if (is_a($this->owner, 'SilverStripe\Blog\Controllers\BlogPostController')) {
			$blog = $this->owner->Parent();
		} else {
			$blog = Blog::get()->First();
		}
		
		return new ArrayData(array('Title'=>'All','LinkingMode'=>$linkingMode,'Link'=>$blog->Link()));
	}
	function getArchiveList($archiveType='Yearly', $numberToDisplay=0) {
		$w = new SilverStripe\Blog\Widgets\BlogArchiveWidget();
		$w->ArchiveType = $archiveType;
		$w->NumberToDisplay = $numberToDisplay;
		
		// Hack to get this working in mysql 5.7, seems that even if I turn ONLY_FULL_GROUP_BY off in my.cnf silverstripe turns it on again somewhere.
		// Get current mode
		$mode = DB::query('SELECT @@sql_mode')->value();
		// Do not remove all modes or Silverstripe will break
		// Remove both the ONLY_FULL_GROUP_BY and ANSI modes, enabling ANSI seems to automatically add ONLY_FULL_GROUP_BY
		$sql = 'SET sql_mode=\''.preg_replace('/(^|,)(ONLY_FULL_GROUP_BY)($|,)/smi', "$3", preg_replace('/(^|,)(ANSI)($|,)/smi', "$3", $mode)).'\';';
		DB::query($sql);
		
		// For linking mode
		if (is_a($this->owner, 'SilverStripe\Blog\Controllers\BlogController')) {
			$year = $this->owner->getArchiveYear();
			$month = $this->owner->getArchiveMonth();
			$day = $this->owner->getArchiveDay();
		}
		
		$return = $w->getArchive();
		foreach($return as $item) {
			if (is_a($this->owner, 'SilverStripe\Blog\Controllers\BlogController')) {
				// add linking mode
				$link = $item->getField('Link');
				$parts = explode('/', $link);
				
				$foundArchive = false;
				$foundYear = false;
				$lyear = null;
				$foundMonth = false;
				$lmonth = null;
				$foundDay = false;
				$lday = null;
				
				foreach ($parts as $part) {
					if ($part == 'archive') {
						$foundArchive = true;
						continue;
					}
					if ($foundMonth) {
						$lday = $part;	
					} elseif ($foundYear) {
						$lmonth = $part;	
					} elseif ($foundArchive) {
						$lyear = $part;	
					}
				}
				
				if ($year==$lyear && $month==$lmonth && $day==$lday) {
					$item->setField('LinkingMode', 'current');
				}
			}
		} // Make sure SQL query is run
		
		// Return to previous mode incase this affects other queries
		DB::query('SET sql_mode=\''.$mode.'\';');
		// End Hack
		
		return $return;
	}
}