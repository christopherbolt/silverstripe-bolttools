<?php

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
	 * @param int the default length of a page if none is set
	 * @param array|ArrayAccess Either a map of request parameters or
	 *        request object that the pagination offset is read from.
	 */
	public function __construct(SS_List $list, $request = array(), $defaultLength=0) {
		if (!is_array($request) && !$request instanceof ArrayAccess) {
			throw new Exception('The request must be readable as an array.');
		}

		$this->request = $request;
		
		if (!$defaultLength) $defaultLength = $this->getPageLength();
		
		if ($length = $this->request[$this->getLengthGetVar()]) {
			if ($length == $this->unlimitedLengthText) $length = $this->unlimitedLength;
		} else {
			$length = $defaultLength;	
		}
		$this->setPageLength($length);
		parent::__construct($list, $request);
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