<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Blog\Model\BlogCategory;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Controller;

if(!class_exists('SilverStripe\Widgets\Model\Widget')) {
	include_once(dirname(__DIR__).'/nowidgets/nowidgets.php');
}

class BoltBlogController extends DataExtension {
	
	/* Category widget */
	function getCategoriesList($limit=0,$order='Title',$direction='ASC') {
		$w = new \SilverStripe\Blog\Widgets\BlogCategoriesWidget();
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
		$w = new \SilverStripe\Blog\Widgets\BlogTagsWidget();
		$w->Limit = $limit;
		$w->Order = $order;
		$w->Direction = $direction;
		return $w->getTags();
	}
	
	/* Recent posts widget */
	function getRecentPosts($numberOfPosts=5) {
		$w = new \SilverStripe\Blog\Widgets\BlogRecentPostsWidget();
		$w->NumberOfPosts = $numberOfPosts;
		return $w->getPosts();
	}
	
	/* Archive widget */
	function getAllArchive () {
		$linkingMode = '';
		
		if (is_a($this->owner, 'SilverStripe\Blog\Model\BlogController')) {
			$blog = $this->owner;
			$year = $blog->getArchiveYear();
			$month = $blog->getArchiveMonth();
			$day = $blog->getArchiveDay();
			
			if (!$year && !$month && !$day/* && !$blog->getCurrentCategory() && !$blog->getCurrentTag()*/) $linkingMode = 'current';
			
		} else if (is_a($this->owner, 'SilverStripe\Blog\Model\BlogPostController')) {
			$blog = $this->owner->Parent();
		} else {
			$blog = Blog::get()->First();
		}
		
		return new ArrayData(array('Title'=>'All','LinkingMode'=>$linkingMode,'Link'=>$blog->Link()));
	}
	function getArchiveList($archiveType='Yearly', $numberToDisplay=0) {
		
		// For linking mode
		if (is_a($this->owner, 'SilverStripe\Blog\Model\BlogController')) {
			$year = $this->owner->getArchiveYear();
			$month = $this->owner->getArchiveMonth();
			$day = $this->owner->getArchiveDay();
            $blog = $this->owner;
		} else if (is_a($this->owner, 'SilverStripe\Blog\Model\BlogPostController')) {
        	$blog = $this->owner->Parent();
		} else {
			$blog = Blog::get()->First();
		}
		
        ///////////////////////////
        // The default Blog Archive widget gets all posts rather than just the posts from the current blog, so until this is fixed we have our own routine below.....
        /*
        $w = new \SilverStripe\Blog\Widgets\BlogArchiveWidget();
		$w->ArchiveType = $archiveType;
		$w->NumberToDisplay = $numberToDisplay;
        $w->BlogID = $blog->ID;
		$return = $w->getArchive();
        */
        
        $format = ($archiveType == 'Yearly') ? '%Y' : '%Y-%m';
        $publishDate = DB::get_conn()->formattedDatetimeClause('"PublishDate"', $format);
        $fields = [
            'PublishDate' => $publishDate,
            'Total' => "COUNT('\"PublishDate\"')"
        ];

        $stage = Versioned::get_stage();
        $suffix = ($stage === Versioned::LIVE) ? '_' . Versioned::LIVE : '';
        $query = SQLSelect::create($fields, '"BlogPost' . $suffix . '"')
            ->addInnerJoin('SiteTree','"SiteTree"."ID" = "BlogPost' . $suffix . '"."ID"')
            ->addGroupBy($publishDate)
            ->addOrderBy('"PublishDate" DESC')
            ->addWhere(['"PublishDate" <= ?' => DBDatetime::now()->Format(DBDatetime::ISO_DATETIME)])
            ->addWhere(['"SiteTree"."ParentID" = ?' => $blog->ID])
            ;

        $posts = $query->execute();
        $return = ArrayList::create();
        foreach ($posts as $post) {
            if ($archiveType == 'Yearly') {
                $pyear  = $post['PublishDate'];
                $pmonth = null;
                $title = $pyear;
            } else {
                $date = DBDate::create();
                $date->setValue(strtotime($post['PublishDate']));

                $pyear  = $date->Format('y');
                $pmonth = $date->Format('MM');
                $title = $date->Format('MMMM y');
            }

            $return->push(ArrayData::create([
                'Title' => $title,
                'Link' => Controller::join_links($blog->Link('archive'), $pyear, $pmonth)
            ]));
        }
        // End own routine
        ///////////////////////////
        
		foreach($return as $item) {
			if (is_a($this->owner, 'SilverStripe\Blog\Model\BlogController')) {
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
		}
		
		return $return;
	}
}