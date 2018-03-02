<?php

namespace ChristopherBolt\BoltTools\Forms;

use SilverStripe\Forms\DropDownField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ValidationException;


class AddNewDropDownField extends DropDownField {
	
	public static $allowed_actions = array(
		'AddNewListboxForm',
		'AddNewListboxFormHTML',
		'AddNewFieldHolderHTML',
		'doSave',
	);
	
	protected $showEditButton = false;
	protected $addNewModel = '';
	protected $dialogTitle = '';
	protected $editDialogTitle = '';
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
	
	public function setEditDialogTitle($title) {
		$this->editDialogTitle = $title;
		return $this;
	}
	
	public function getEditDialogTitle() {
		return $this->editDialogTitle;
	}
	
	public function setBeforeWriteCallback($callback) {
		$this->onBeforeWriteCallback = $callback;
		return $this;
	}
	
	public function getBeforeWriteCallback() {
		return $this->onBeforeWriteCallback;
	}
	
	public function setShowEditButton($bool=true) {
		$this->showEditButton = $bool;
		return $this;
	}
	
	public function getShowEditButton($bool=true) {
		return $this->showEditButton;
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
		$item = null;
        if ($itemID = (int) $this->request->getVar('ItemID')) {
            $item = $model::get()->byID($itemID);
        }
        $item = $item ? $item : singleton($model);

		$fields = $item->getCMSFields();
		
		if ($item->ID) {
			$title = $this->getEditDialogTitle() ? $this->getEditDialogTitle() : 'Edit Item';
		} else {
			$title = $this->getDialogTitle() ? $this->getDialogTitle() : 'New Item';
		}
		
		$fields->insertBefore(HeaderField::create('AddNewHeader', $title), $fields->first()->getName());
		$actions = FieldList::create($action);
		$form = Form::create($this, 'AddNewListboxForm', $fields, $actions);
		
		$fields->push(HiddenField::create('model', 'model', $model));
		
		
		if($item){
			$form->loadDataFrom($item);
			$fields->push(HiddenField::create('ItemID', 'ItemID', $item->ID));
		}
		
		/*
		// Chris Bolt, fixed this
		//$this->owner->extend('updateitemForm', $form);
		$this->extend('updateitemForm', $form);
		// End Chris Bolt
		*/
		return $form;
	}
	
	public function doSave($data, $form){
		$model = $this->getModel();
		
		$item = null;
        if ($itemID = (int) Controller::curr()->request->requestVar('ItemID')) {
            $item = $model::get()->byID($itemID);
        }
        $item = $item ? $item : singleton($model);
		
		$form->saveInto($item);
		$callback = $this->getBeforeWriteCallback();
		if (is_callable($callback)) {
			$item = call_user_func($callback, $item);
		}
		//return print_r($item, true);
		try {
			$item->write();	
		} catch (ValidationException $e) {
			$form->sessionMessage($e->getMessage(), 'bad');
			return $form->forTemplate();
		}
		//$this->setValue($item->ID);
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