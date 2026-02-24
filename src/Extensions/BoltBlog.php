<?php

namespace ChristopherBolt\BoltTools\Extensions;

use Silverstripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Control\Controller;

/* 
Used to allow easy hiding of features not used in the site
Adds functions for easy display of widgets without widget holder
*/

class BoltBlog extends Extension{
	
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

	/* Archive widget */
	function getArchiveList($archiveType='Yearly', $numberToDisplay=0) {

		$controller = Controller::curr();
		$blog = $this->owner;
		$year = null;
		$month = null;
		$day = null;
		
		if (is_a($controller, 'SilverStripe\Blog\Model\BlogController')) {
			$year = $controller->getArchiveYear();
			$month = $controller->getArchiveMonth();
			$day = $controller->getArchiveDay();
		}
        
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
        $array = [];
        foreach ($posts as $post) {
            if ($archiveType == 'Yearly') {
                $pyear  = $post['PublishDate'];
                $pmonth = null;
                $title = $pyear;
				$linkingMode = ($pyear == $year) ? 'current' : '';
            } else {
                $date = DBDate::create();
                $date->setValue(strtotime($post['PublishDate']));

                $pyear  = $date->Format('y');
                $pmonth = $date->Format('MM');
                $title = $date->Format('MMMM y');
				$linkingMode = ($pyear == $year && $pmonth == $month) ? 'current' : '';
            }
			$array[] = [
				'Title' => $title,
				'LinkingMode' => $linkingMode,
                'Link' => Controller::join_links($blog->Link('archive'), $pyear, $pmonth)
			];
        }
		$return = new ArrayList($array);
		
		return $return;
	}
	
}
