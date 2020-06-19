<?php

/* Quickly allow editing on an object */

class DefaultCan extends DataExtension {
	/*
	function can($method, $member=null) {
		if ($rel = $this->owner->config()->get('can_relation')) {
			if ($obj = $this->owner->obj($rel)) {
				return $obj->$methodName($member);
			} else {
				return false;	
			}
		}
		return true;
	}
    */
    /**
	 * @param Member $member
	 * @return boolean
	 */
	public function canView($member = null) {
		if (Permission::checkMember($member, 'CMS_ACCESS')) {
    		return true;
		}
	}

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canEdit($member = null) {
		if (Permission::checkMember($member, 'CMS_ACCESS')) {
    		return true;
		}
	}

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canDelete($member = null) {
		if (Permission::checkMember($member, 'CMS_ACCESS')) {
    		return true;
		}
	}

	/**
	 * @todo Should canCreate be a static method?
	 *
	 * @param Member $member
	 * @return boolean
	 */
	public function canCreate($member = null) {
		if (Permission::checkMember($member, 'CMS_ACCESS')) {
    		return true;
		}
	}
	
}