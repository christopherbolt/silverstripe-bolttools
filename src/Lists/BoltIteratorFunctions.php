<?php

namespace ChristopherBolt\BoltTools\Lists;

use SilverStripe\View\TemplateIteratorProvider;


class BoltIteratorFunctions implements TemplateIteratorProvider {

	protected $iteratorPos;
	protected $iteratorTotalItems;

	public static function get_template_iterator_variables() {
		return array('PosIsGreaterThan','PosFromBottom','PosMultipleOf','PosBeforeMultipleOf','PosAfterMultipleOf','Third','Fourth','Fifth');
	}

	public function iteratorProperties($pos, $totalItems) {
		$this->iteratorPos        = $pos;
		$this->iteratorTotalItems = $totalItems;
	}

	function PosIsGreaterThan($num) {
		return $this->iteratorPos > $num;
	}
	
    function PosFromBottom($num=null){
		if ($num === null) {
			return ($this->iteratorTotalItems - $this->iteratorPos);
		} else {
    		return (($this->iteratorTotalItems - $this->iteratorPos) == $num);
		}
  	}
	
	function PosMultipleOf($num){
    	return ((($this->iteratorPos+1) % $num) == 0) ? true : false; 
  	}
	
	function PosBeforeMultipleOf($num){
    	return ((($this->iteratorPos-1) % $num) == 0) ? true : false; 
  	}
	
	function PosAfterMultipleOf($num){
		if ($this->iteratorPos) return ((($this->iteratorPos) % $num) == 0) ? true : false; 
  	}
	
	function Third() { 
   		return ((($this->iteratorPos+1) % 3) == 0) ? 'third' : ''; 
	}
	
	function Fourth() {
   		return ((($this->iteratorPos+1) % 4) == 0) ? 'fourth' : ''; 
	}
	
	function Fifth() { 
   		return ((($this->iteratorPos+1) % 5) == 0) ? 'fifth' : ''; 
	}
}