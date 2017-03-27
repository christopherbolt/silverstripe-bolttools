<?php

// Extends the paginated list to add some functions for simplifying sorting table columns

class SortedLimitedPaginatedList extends LimitedPaginatedList {
	protected $sortColGetVar = 'sortby';
	protected $sortDirGetVar = 'sortdir';
	
	public function getSortColGetVar() {
		return $this->sortColGetVar;
	}
	
	public function setSortColGetVar($var) {
		$this->sortColGetVar = $var;
		return $this;
	}
	
	public function getSortDirGetVar() {
		return $this->sortDirGetVar;
	}

	
	public function setSortDirGetVar($var) {
		$this->sortDirGetVar = $var;
		return $this;
	}

	public function SortLink($column) {
		// Col
		$sortCol = $this->SortCol();
		
		// direction?
		$sortDir = $this->SortDir();
		
		if ($sortCol == $column) {
			// reverse sort dir
			$sortDir = $sortDir=='Up' ? 'Down' : 'Up';
		}
								
		return HTTP::setGetVar($this->getSortColGetVar(), $column, HTTP::setGetVar($this->getSortDirGetVar(), $sortDir, null, '&'), '&');
	}
	
	function SortCol() {
		return isset($this->request[$this->getSortColGetVar()]) ? $this->request[$this->getSortColGetVar()] : '';
	}
	
	function SortDir() {
		return isset($this->request[$this->getSortDirGetVar()]) ? $this->request[$this->getSortDirGetVar()] : 'Up';
	}
	
	/**
	 * Applies the sorting to the list
	 * @return IteratorIterator
	 */
	public function getIterator() {
		$list = clone $this->list;
		
		// Sort list
		$sortCol = isset($this->request[$this->getSortColGetVar()]) ? $this->request[$this->getSortColGetVar()] : '';
		$sortCol = preg_replace('/[^A-Za-z_]/','',$sortCol);
		
		$sortDir = isset($this->request[$this->getSortDirGetVar()]) ? $this->request[$this->getSortDirGetVar()] : '';
		$sortDir = $sortDir=='Down' ? ' DESC' : ($sortDir=='Up' ? ' ASC' : '');
		
		if ($sortCol && $this->canSortBy($sortCol)) {
			$list = $this->List->Sort($sortCol.$sortDir);
		}
		
		// Limit list
		$pageLength = $this->getPageLength();
		if($this->limitItems && $pageLength) {
			$list = $list->limit($pageLength, $this->getPageStart());
		}
		
		return new IteratorIterator($list);
	}

}