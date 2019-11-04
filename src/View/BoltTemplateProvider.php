<?php

namespace ChristopherBolt\BoltTools\View;

use SilverStripe\View\TemplateGlobalProvider;
use Page;

// Adds some usful global template functions

class BoltTemplateProvider implements TemplateGlobalProvider {
   
    public static function get_template_global_variables() {
        return array(
            'PageById',
			'AllPages'
        );
    }
    // Media queries for use in picture source tags
	/* Some helper functions for templates */
	public static function PageById($id) {
		return Page::get()->byId($id);	
	}
	public static function AllPages() {
		return Page::get();	
	}
}