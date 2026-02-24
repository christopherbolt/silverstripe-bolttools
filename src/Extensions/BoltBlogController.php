<?php

namespace ChristopherBolt\BoltTools\Extensions;

use Silverstripe\Core\Extension;

class BoltBlogController extends Extension {
    
    // Ensure meta title is set correctly because blog module has it's own metatitle function
    function updateMetaTitle(&$title) {
        if ($meta = $this->owner->data()->MetaTitle) {
            $pagetitle = $this->owner->data()->Title;
            $title = $meta.substr($title, strlen($pagetitle));
        }
    }
}