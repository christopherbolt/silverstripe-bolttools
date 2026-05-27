<?php

// Sets gloabl template variables for this project

namespace MySite\View;

use SilverStripe\View\TemplateGlobalProvider;

class TemplateVariables implements TemplateGlobalProvider {

    public static function get_template_global_variables() {
        return array(
			'ThemeDir'
        );
    }

	public static function ThemeDir() {
		return '_resources/themes/mytheme/';
	}
}
