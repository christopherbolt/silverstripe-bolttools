<?php

namespace ChristopherBolt\BoltTools\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
//use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Extension;
//use SilverStripe\Forms\FileHandleField;
//use SilverStripe\Core\Injector\Injector;
//use UncleCheese\DisplayLogic\Forms\Wrapper;

/* 
Adds file uploads and a few other things to the Link class
*/

class BoltLink extends DataExtension{
	private static $db = array(
		'ExtraAttributes' => 'HTMLText'
	);
	
	private static $defaults = array(
		'Type' => 'SiteTree'
	);
	
	// Reorder types
	private static $types = array(
       	'SiteTree' => 'Page on this website',
	    'URL' => 'URL',
        'Email' => 'Email address',
        'File' => 'File on this website',
    );

	
	public function updateCMSFields(FieldList $fields) {
		/*
		// Changed Type to an optionset because drop down does not work sometimes in a dialog :-(
		// Also move sitetree to first item, I like that better.
		$types = $this->owner->config()->get('types');
        $i18nTypes = array();
		// Move SiteTree to first
		$i18nTypes['SiteTree'] = _t('Linkable.TYPE'.strtoupper('SiteTree'), $types['SiteTree']);
        foreach ($types as $key => $label) {
            $i18nTypes[$key] = _t('Linkable.TYPE'.strtoupper($key), $label);
        }
		$fields->replaceField('Type', OptionsetField::create('Type', _t('Linkable.LINKTYPE', 'Link Type'), $i18nTypes));
		
		// Chris Bolt, replaced file choose with an upload field, this doesn't currently work, maybe ss bug, will revist later
		/*$fields->removeByName('FileID');
		$fields->addFieldToTab('Root.Main', $file = Wrapper::create(
			Injector::inst()->create(FileHandleField::class, 'File', _t('Linkable.FILE', 'File'))
		), 'Email');
		$file
            ->displayIf('Type')
            ->isEqualTo('File')
            ->end();*/
		
		if ($this->owner->config()->get('show_extra_attributes')) {
			// Chris Bolt, added functionality for adding custom attributes
			$fields->addFieldToTab('Root.Main', TextField::create('ExtraAttributes', 'Extra Attributes')->setDescription('e.g. onClick="_gaq.push([\'_trackEvent\', \'whitepaper\', \'download\', pdfName, pdfValue, true]);"'));
		} else {
			$fields->removeByName('ExtraAttributes');	
		}
		
	}
	
	// Chris Bolt, extra attributes function, use this instead of getTargetAttr
	public function getAttributes(){
        return ($this->owner->OpenInNewWindow ? "target='_blank'" : '').($this->owner->ExtraAttributes ? ' '.$this->owner->ExtraAttributes : '');
	}
	
	// Add extra attributes
	public function updateLinkTemplate($object, $link) {
		if ($this->owner->ExtraAttributes) {
			$link = str_replace('">', '" '.$this->owner->ExtraAttributes.'>', $link);
		}
	}
	
	public function onAfterWrite() {
		// Chris Bolt, removed auto setting title, this annoys me
		/*if ($this->Title == 'Link-' . $this->ID) {
			$this->Title = $this->getLinkURL();
			$this->write();	
		}*/
	}
	
	function getTitle() {
		// Chris Bolt, removed auto setting title, this annoys me
		if ($this->Title == 'Link-' . $this->ID) {
			return $this->owner->getLinkURL();
		}
		return $this->owner->Title;
	}
	
}
