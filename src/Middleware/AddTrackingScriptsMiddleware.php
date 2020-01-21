<?php

namespace ChristopherBolt\BoltTools\Middleware;

use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\SiteConfig\SiteConfig;

class AddTrackingScriptsMiddleware implements HTTPMiddleware {
    
    use Configurable;
    
    private static $enabled = false;
    
    public function process(HTTPRequest $request, callable $delegate) {
                
        $response = $delegate($request);
        
        if($this->config()->get('enabled'))  {
            $siteConfig  = SiteConfig::current_site_config();
            
            $responseBody = $response->getBody();
            
			// Head tags
			if (isset($siteConfig->TrackingCodeHead)) {
				$responseBody = preg_replace('/(<\/head>)/i', $this->escapeReplacement($siteConfig->TrackingCodeHead)."\n$1", $responseBody);
			}
            
            // Body open
            if (isset($siteConfig->TrackingCodeBodyOpen)) {
				$responseBody = preg_replace('/(<body[^>]*>)/i', "$1\n".$this->escapeReplacement($siteConfig->TrackingCodeBodyOpen), $responseBody);
			}
            
            // Body close
            if (isset($siteConfig->TrackingCodeBodyClose)) {
				$responseBody = preg_replace('/(<\/body>)/i', $this->escapeReplacement($siteConfig->TrackingCodeBodyClose)."\n$1", $responseBody);
			}
            
            $response->setBody($responseBody);
		}
        
        return $response;
    }
    
    protected function escapeReplacement($replacement) {
        return addcslashes($replacement, '\\$');
    }
}