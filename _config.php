<?php

// Paths
/**
 * - BOLTTOOLS_DIR: Path relative to webroot, e.g. "boltmail"
 * - BOLTTOOLS_PATH: Absolute filepath, e.g. "/var/www/my-webroot/boltmail"
 */
//define('BOLTTOOLS_DIR', basename(dirname(__FILE__)));
//define('BOLTTOOLS_PATH', BASE_PATH . '/' . BOLTTOOLS_DIR);
//define('BOLTTOOLS_THIRDPARTY_PATH', BOLTTOOLS_PATH.'/thirdparty');
//define('BOLTTOOLS_CONF_PATH', BOLTTOOLS_PATH.'/conf');

// Short code handlers
SilverStripe\View\Parsers\ShortcodeParser::get()->register('LineBreak',array('ChristopherBolt\\BoltTools\\Helpers\\BoltShortCodeHelper','LineBreak'));

?>