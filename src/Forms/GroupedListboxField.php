<?php

namespace ChristopherBolt\BoltTools\Forms;

use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\FormField;


class GroupedListboxField extends ListboxField {

	public function Field($properties = array()) {
		if($this->multiple) $this->name .= '[]';
		$options = '';
		foreach($this->getSource() as $value => $title) {
			if(is_array($title)) {
				$options .= "<optgroup label=\"$value\">";
				foreach($title as $value2 => $title2) {
										
					$selected = ((!is_array($this->value) && $this->value == $value2) || ((is_array($this->value) && in_array($value2, $this->value)) || in_array($value2, $this->defaultItems))) ? ' selected="selected"' : '';
					$disabled = ($this->disabled || in_array($value2, $this->disabledItems)) ? 'disabled="disabled"' : '';
					
					$options .= "<option$selected value=\"$value2\" $disabled>$title2</option>";
				}
				$options .= "</optgroup>";
			} else { // Fall back to the standard dropdown field
				$selected = ((!is_array($this->value) && $this->value == $value) || ((is_array($this->value) && in_array($value, $this->value)) || in_array($value, $this->defaultItems))) ? ' selected="selected"' : '';
				$disabled = ($this->disabled || in_array($value, $this->disabledItems)) ? 'disabled="disabled"' : '';
				$options .= "<option$selected value=\"$value\" $disabled>$title</option>";
			}
		}

		return FormField::create_tag('select', $this->getAttributes(), $options);
	}

	public function Type() {
		return 'groupedlistbox listbox';
	}
	
}

