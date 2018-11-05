<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;


class BoltTextExtension extends DataExtension {
    private static $casting = array(
        "LimitWordCountNoHTML" => "HTMLText",
    );
	function LimitWordCountNoHTML($numWords = 26, $add = '...') {
		//$this->value = trim(Convert::xml2raw($this->value));
		$this->owner->value = $this->owner->Plain();
		$ret = explode(' ', $this->owner->value, $numWords + 1);
		
		if(count($ret) <= $numWords - 1) {
			$ret = $this->owner->value;
		} else {
			array_pop($ret);
			$ret = implode(' ', $ret) . $add;
		}
		
		return $ret;
	}
}