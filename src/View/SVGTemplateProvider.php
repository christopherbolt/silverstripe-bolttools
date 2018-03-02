<?php

namespace ChristopherBolt\BoltTools\View;

use SilverStripe\View\TemplateGlobalProvider;
use ChristopherBolt\BoltTools\View\SVGTemplate;


/**
 * Class SVGTemplateProvider
 */
class SVGTemplateProvider implements TemplateGlobalProvider
{
    /**
     * @return array
     */
    public static function get_template_global_variables()
    {
        return array(
            'SVG'
        );
    }

    /**
     * @param $path
     * @param $id
     * @return SVGTemplate
     */
    public static function SVG($path, $id = false)
    {
        return new SVGTemplate($path, $id);
    }

}
