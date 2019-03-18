<?php

namespace ChristopherBolt\BoltTools\Lists;

use ChristopherBolt\BoltTools\Lists\LimitedPaginatedList;
use SilverStripe\Control\HTTP;
use IteratorIterator;


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
		if (!empty($this->request[$this->getSortColGetVar()])) {
            return $this->request[$this->getSortColGetVar()];
        } else {
            // Attempt to get sort col from SQL
            $sql = $this->list->sql();
            if (preg_match('/ORDER BY "[a-z0-9_]+"."([a-z0-9_]+)" (ASC|DESC)/i', $sql, $matches)) {
                return $matches[1];
            } else {
                return '';
            }
        }
	}
	
	function SortDir() {
		if (!empty($this->request[$this->getSortDirGetVar()])) {
            return $this->request[$this->getSortDirGetVar()];
        } else {
            // Attempt to get direction from SQL
            $sql = $this->list->sql();
            if (preg_match('/ORDER BY [a-z0-9\.\" ]+ DESC/i', $sql)) {
                return 'Down';
            } else {
                return 'Up';
            }
        }
	}
	
	/**
	 * Applies the sorting to the list
	 * @return IteratorIterator
	 */
	public function getIterator() {
		$list = clone $this->list;
		
		// Sort list
		$sortCol = isset($this->request[$this->getSortColGetVar()]) ? $this->request[$this->getSortColGetVar()] : '';
		$sortCol = preg_replace('/[^A-Za-z0-9_\.]/','',$sortCol);
		
		$sortDir = isset($this->request[$this->getSortDirGetVar()]) ? $this->request[$this->getSortDirGetVar()] : '';
		$sortDir = $sortDir=='Down' ? ' DESC' : ($sortDir=='Up' ? ' ASC' : '');
		
		if ($sortCol/* && $list->canSortBy($sortCol)*/) {
			$list = $list->Sort($sortCol.$sortDir);
		}
		
		// Limit list
		$pageLength = $this->getPageLength();
		if($this->limitItems && $pageLength) {
			$list = $list->limit($pageLength, $this->getPageStart());
		}
		
		return new IteratorIterator($list);
	}

}