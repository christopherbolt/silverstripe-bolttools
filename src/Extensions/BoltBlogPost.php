<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use ChristopherBolt\BoltTools\Forms\AddNewListboxField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Convert;
use SilverStripe\Blog\Model\BlogPost;


/* 
Used to allow easy hiding of features not used in the site
Adds functions for easy display of widgets without widget holder
Adds option to use addnewlistboxfield instead of tagfield, since tag field is broken at time of writing
*/

class BoltBlogPost extends DataExtension{

	private static $defaults = array(
		'ShowInSiteMap' => 0
	);
	
	public function updateCMSFields(FieldList $fields) {
		
		if ($this->owner->Parent()->config()->get('hide_categories')) {
			$fields->removeByName('Categories');
		} else {
			if ($this->owner->config()->get('use_addnewlistboxfield')) {	// Remove this once tagfield us fixed
				$list = new AddNewListboxField('Categories', 'Categories', $this->owner->Parent()->Categories()->map("ID", "Title")->toArray());
				$list->setMultiple(true);
				$list->setModel('BlogCategory')->setDialogTitle('New Category')->setBeforeWriteCallback(array($this->owner, 'AddNewDropDownFieldCategorisationCallback'));
				$fields->replaceField('Categories', $list);
			}
		}
		
		if ($this->owner->Parent()->config()->get('hide_tags')) {
			$fields->removeByName('Tags');
		} else {
			if ($this->owner->config()->get('use_addnewlistboxfield')) {	// Remove this once tagfield us fixed
				$list = new AddNewListboxField('Tags', 'Tags', $this->owner->Parent()->Tags()->map("ID", "Title")->toArray());
				$list->setMultiple(true);
				$list->setModel('BlogTag')->setDialogTitle('New Tag')->setBeforeWriteCallback(array($this->owner, 'AddNewDropDownFieldCategorisationCallback'));
				$fields->replaceField('Tags', $list);
			}
		}
		
		if ($this->owner->config()->get('hide_authors')) {
			$fields->removeByName("Authors");
			$fields->removeByName("AuthorNames");
		}
		
		if ($this->owner->config()->get('hide_summary')) {
			$fields->removeByName("CustomSummary");
		}
		
		if ($this->owner->config()->get('hide_image')) {
			$fields->removeByName("FeaturedImage");
		} else {
			// Add description about ContentImage
			$fields->dataFieldByName('FeaturedImage')->setDescription(_t(
					'SilverStripe\Blog\Model\BlogPost.FEATURED_IMAGE_DESCRIPTION',
					'If no image is supplied then the first image found in the article content will be used if one exists.'
				)
			);	
		}
	}
	
	public function AddNewDropDownFieldCategorisationCallback($obj) {
		$obj->BlogID = $this->owner->Parent()->ID;
		return $obj;
	}
	
	// Optional replacement for $FeaturedImage that will show the first image found in the article content, just use $ContentImage instead
	function ContentImage() {
		// Check for image
		$img = $this->owner->obj('FeaturedImage');
		if ($img && $img->exists()) return $img;
		// else try to get first image tag from article.
		$matches = array();
		if (preg_match('#<img [^>]*src="([^">]+)"#smi', $this->owner->Content, $matches)) {
			if (isset($matches[1])) {
				$filename = preg_replace('#_resampled/resizedimage[0-9a-z]+/#smi', '', $matches[1]);
				$file = DataObject::get_one('File', 'Filename=\''.Convert::Raw2SQL($filename).'\'');
				if ($file) return $file;
			}
		}
	}
	
	// Bring back previous and next links
	function PreviousBlogEntry() {
		return BlogPost::get()->filter(array('ParentID'=>$this->owner->ParentID, 'PublishDate:LessThan'=>$this->owner->PublishDate))->Sort('PublishDate DESC')->First();
	}
	
	function NextBlogEntry() {
		return BlogPost::get()->filter(array('ParentID'=>$this->owner->ParentID, 'PublishDate:GreaterThan'=>$this->owner->PublishDate))->Sort('PublishDate ASC')->First();	
	}
	
}
