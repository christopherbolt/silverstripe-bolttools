<?php

/* 
Adds file uploads and a few other things to the Link class
*/

class BoltLink extends DataExtension{
	private static $db = array(
		'ExtraAttributes' => 'HTMLText'
	);
	
	public function updateCMSFields(FieldList $fields) {
		
		// Changed Type to an optionset because drop down does not work sometimes in a dialog :-(
		$types = $this->owner->config()->get('types');
        $i18nTypes = array();
        foreach ($types as $key => $label) {
            $i18nTypes[$key] = _t('Linkable.TYPE'.strtoupper($key), $label);
        }
		$fields->replaceField('Type', OptionsetField::create('Type', _t('Linkable.LINKTYPE', 'Link Type'), $i18nTypes), 'OpenInNewWindow');
		
		// Chris Bolt, replaced file choose with an upload field
		$fields->replaceField('FileID', UploadField::create('File', _t('Linkable.FILE', 'File')), 'OpenInNewWindow');
		
		// Chris Bolt, added functionality for adding custom attributes
		$fields->addFieldToTab('Root.Main', TextField::create('ExtraAttributes', 'Extra Attributes')->setDescription('e.g. onClick="_gaq.push([\'_trackEvent\', \'whitepaper\', \'download\', pdfName, pdfValue, true]);"'));
		
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

class BoltLinkField extends Extension {
	// replacement for link object for use in the CMS template so that object is displayed when new
    public function getBetterLinkObject()
    {
        $object = $this->owner->getLinkObject();
		if (!$object) {
			return ($this->owner->Value());
		}
       return $object;
    }
}
