<?php

class BoltDBFieldExtension extends DataExtension {
	function URLEncodeSpaces() {
		return str_replace(' ', '%20', $this->owner->value);
	}
}