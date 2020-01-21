<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;

class PlaceholderFields extends Extension {
    // Add the placeholder attribute to supported form fields
    function updateAttributes(&$attributes) {
        $placeholders_supported = $this->owner->config()->get('placeholders_supported');
        if (!is_array($placeholders_supported)) $placeholders_supported = [];
        if (($title = $this->owner->Title()) && !isset($attributes['placeholder']) && in_array(get_class($this->owner), $placeholders_supported) && !$this->owner->hasClass('hasLabel') && !$this->owner->hasClass('has-label')) {
            $attributes['placeholder'] = $title;
        }
    }
}