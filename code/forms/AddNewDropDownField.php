<?php

class AddNewDropDownField extends DropDownField {
	
	public static $allowed_actions = array(
		'AddNewListboxForm',
		'AddNewListboxFormHTML',
		'AddNewFieldHolderHTML',
		'doSave',
	);
	
	protected $addNewModel = '';
	protected $dialogTitle = '';
	protected $onBeforeWriteCallback = array();
		
	public function setModel($model) {
		$this->addNewModel = $model;
		return $this;
	}
	
	public function getModel() {
		return $this->addNewModel;
	}
	
	public function setDialogTitle($title) {
		$this->dialogTitle = $title;
		return $this;
	}
	
	public function getDialogTitle() {
		return $this->dialogTitle;
	}
	
	public function setBeforeWriteCallback($callback) {
		$this->onBeforeWriteCallback = $callback;
		return $this;
	}
	
	public function getBeforeWriteCallback() {
		return $this->onBeforeWriteCallback;
	}
	
	public function Field($properties = array()){
		Requirements::javascript(BOLTTOOLS_DIR . '/javascript/addnewlistboxfield.js');
		$this->setTemplate('AddNewListboxField');
		$this->addExtraClass('has-chzn');
		return parent::Field($properties);
	}
	
	public function AddNewListboxForm(){

		$action = FormAction::create('doSave', 'Save')->setUseButtonTag('true');

		if(!$this->isFrontend){
			$action->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept');
		}
		
		$model = $this->getModel();
		$link = singleton($model);

		$fields = $link->getCMSFields();
		
		$title = $this->getDialogTitle() ? $this->getDialogTitle() : 'New Item';
		$fields->insertBefore(HeaderField::create('AddNewHeader', $title), $fields->first()->getName());
		$actions = FieldList::create($action);
		$form = Form::create($this, 'AddNewListboxForm', $fields, $actions);
		
		$fields->push(HiddenField::create('model', 'model', $model));
		
		/*
		if($link){
			$form->loadDataFrom($link);
			$fields->push(HiddenField::create('LinkID', 'LinkID', $link->ID));
		}
		
		// Chris Bolt, fixed this
		//$this->owner->extend('updateLinkForm', $form);
		$this->extend('updateLinkForm', $form);
		// End Chris Bolt
		*/
		return $form;
	}
	
	public function doSave($data, $form){
		$model = $this->getModel();
		$link = Object::create($model);//eval("return {$model}::create()");
		$form->saveInto($link);
		$callback = $this->getBeforeWriteCallback();
		if (is_callable($callback)) {
			$link = call_user_func($callback, $link);
		}
		//return print_r($link, true);
		try {
			$link->write();	
		} catch (ValidationException $e) {
			$form->sessionMessage($e->getMessage(), 'bad');
			return $form->forTemplate();
		}
		//$this->setValue($link->ID);
		$this->setForm($form);
		return $this->FieldHolder();
	}
	
	public function AddNewListboxFormHTML(){
		return $this->AddNewListboxForm()->forTemplate();
	}
	
	public function AddNewFieldHolderHTML() {
		$selected = $this->request->getVar('selected');
		$multiple = isset($this->multiple) ? $this->multiple : false;
		if($multiple && !is_array($selected)) {
			$selected = explode(',', $selected);
		}
		$this->setValue($selected);
		return $this->FieldHolder();
	}
}