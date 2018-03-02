<?php

namespace ChristopherBolt\BoltTools\Controllers;

use Page_Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use ChristopherBolt\BoltTools\Search\BoltSearchForm;


class BoltSiteSearch extends Page_Controller {
	
	private static $allowed_actions = array (
		'SearchForm'
	);
	
	function init() {
		// This hack prevents the ContentController from restricting access when in draft site
		$this->URLSegment = 'Security';
		parent::init();
	}
		
	/* Search functions
	----------------------------------*/
	
	// used to display the search query
	function SearchQuery () {
		return htmlspecialchars(isset($_REQUEST['Search']) ? $_REQUEST['Search'] : '');
	}
	
	/**
	 * Site search form 
	 */ 
	function SearchForm() {
		$searchText = isset($_REQUEST['Search']) ? $_REQUEST['Search'] : 'Search';
		$fields = new FieldList(
	      	new TextField("Search", "", $searchText)
	  	);
		$actions = new FieldList(
	      	new FormAction('results', 'Search')
	  	);

	  	return new BoltSearchForm($this, "SearchForm", $fields, $actions);
	}
	
	/**
	 * Process and render search results
	 */
	function results($data, $form){
	  	$form->classesToSearch(array(
			"SiteTree"			
		));
		$data = array(
	     	'Results' => $form->getResults(),
	     	'Query' => $form->getSearchQuery(),
	      	'Title' => 'Search Results',
			'MenuTitle' => 'Search Results',
			'MetaTitle' => 'Search Results'
	  	);
		//print_r($form->getResults());
		//exit;
	  	return $this->customise($data)->renderWith(array('SiteSearch', 'Page'));
	}
}