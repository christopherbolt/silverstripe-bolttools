<?php

namespace ChristopherBolt\BoltTools\Lists;

use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\HTTP;


// Extends the paginated list to add some functions for simplifying the creation of a tool for setting the page length

class LimitedPaginatedList extends PaginatedList {
	protected $lengthGetVar = 'limit';
	protected $unlimitedLength = 1000000;
	protected $unlimitedLengthText = 'All';
	
	/**
	 * Constructs a new paginated list instance around a list.
	 *
	 * @param SS_List $list The list to paginate. The getRange method will
	 *        be used to get the subset of objects to show.
	 * @param array|ArrayAccess Either a map of request parameters or
	 *        request object that the pagination offset is read from.
	 * @param int the default length of a page if none is set
	 */
	public function __construct(SS_List $list, $request = array(), $defaultLength=0) {
		if ($defaultLength) $this->setPageLength($defaultLength);
		parent::__construct($list, $request);
	}
	
	/**
	 * The magic happens in this function?
	 */
	public function getPageLength() {
		$pageLength = isset($this->request[$this->getLengthGetVar()]) ? $this->request[$this->getLengthGetVar()] : 0;
		if ($pageLength) {
			if ($pageLength == $this->unlimitedLengthText) $pageLength = $this->unlimitedLength;
			$this->setPageLength($pageLength);
		}
		return parent::getPageLength();
	}
	
	/**
	 * Returns the GET var that is used to set the page length. This defaults
	 * to "length".
	 *
	 * If there is more than one paginated list on a page, it is neccesary to
	 * set a different get var for each using {@link setLengthGetVar()}.
	 *
	 * @return string
	 */
	public function getLengthGetVar() {
		return $this->lengthGetVar;
	}

	/**
	 * Sets the GET var used to set the page length.
	 *
	 * @param string $var
	 */
	public function setLengthGetVar($var) {
		$this->lengthGetVar = $var;
		return $this;
	}

	/* Creates a list of length options with links for use in templates
	must re-set the page to 1
	 */
	public function PageLengthLimits() {
		$lengths = func_get_args();
		$result = new ArrayList();
		
		foreach ($lengths as $length) {
			$result->push(new ArrayData(array(
				'PageLength'     => $length,
				'Link'        => HTTP::setGetVar($this->getPaginationGetVar(), 0, HTTP::setGetVar($this->getLengthGetVar(), $length, null, '&')),
				'CurrentBool' => ($this->getPageLength() == $length || ($length == $this->unlimitedLengthText && $this->getPageLength() == $this->unlimitedLength))
			)));
		}

		return $result;
	}

}