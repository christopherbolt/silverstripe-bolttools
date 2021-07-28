<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\TextField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextareaField;

class BoltSiteConfig extends DataExtension {
		
	private static $db = array(
		'Copyright' => 'Varchar(255)',
		'ContactEmail' => 'Varchar',
		'ContactPhone' => 'Varchar',
		'TrackingCodeHead' => 'Text',
        'TrackingCodeBodyOpen' => 'Text',
        'TrackingCodeBodyClose' => 'Text',
	);
	
	public function updateCMSFields(FieldList $fields) {
		
		$fields->removeByName('Tagline');
		
		$fields->addFieldsToTab("Root.Main", array(
			EmailField::create("ContactEmail", 'Contact email'),
			TextField::create("ContactPhone", 'Contact phone'),
			TextField::create("Copyright", 'Copyright'),
					
			HeaderField::create('Tracking codes'),
            LiteralField::create("TrackingCodesExplained", '<p class="message">Enter your tracking codes for services such as Google Analytics below. Tracking codes usually need to be placed into one of three places; before the closing head tag, after the opening body tag or before the closing body tag. Paste your code into the appropriate box below. You can add code from multiple services into each box; simply separate each with a new line.</p>'),
            TextareaField::create("TrackingCodeHead", 'Before the closing head tag (</head>)'),
            TextareaField::create("TrackingCodeBodyOpen", 'After the opening body tag (<body>)'),
            TextareaField::create("TrackingCodeBodyClose", 'Before the closing body tag (</body>)')
		));
		
	}
	
}

