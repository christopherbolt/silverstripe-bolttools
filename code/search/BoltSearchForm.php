<?php

/* extends search form to support the custom SearchIndex, 
just replaces calls to DB::getConn()->searchEngine() with a new function, only difference is this function uses a different field list */

class BoltSearchForm extends SearchForm {
	
	/* identical to the original function except replaces the DB::getConn()->searchEngine() function calls with self::searchEngine() */
	public function getResults($pageLength = null, $data = null){
	 	// legacy usage: $data was defaulting to $_REQUEST, parameter not passed in doc.silverstripe.org tutorials
		if(!isset($data) || !is_array($data)) $data = $_REQUEST;
		
		// set language (if present)
		if(class_exists('Translatable')) {
			if(singleton('SiteTree')->hasExtension('Translatable') && isset($data['searchlocale'])) {
				if($data['searchlocale'] == "ALL") {
					Translatable::disable_locale_filter();
				} else {
					$origLocale = Translatable::get_current_locale();

					Translatable::set_current_locale($data['searchlocale']);
				}
			}
		}

		$keywords = $data['Search'];

	 	$andProcessor = create_function('$matches','
	 		return " +" . $matches[2] . " +" . $matches[4] . " ";
	 	');
	 	$notProcessor = create_function('$matches', '
	 		return " -" . $matches[3];
	 	');

	 	$keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
	 	$keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);
		
		$keywords = $this->addStarsToKeywords($keywords);

		if(!$pageLength) $pageLength = $this->pageLength;
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		
		if(strpos($keywords, '"') !== false || strpos($keywords, '+') !== false || strpos($keywords, '-') !== false || strpos($keywords, '*') !== false) {
			$results = self::searchEngine($this->classesToSearch, $keywords, $start, $pageLength, "\"Relevance\" DESC", "", true);
		} else {
			$results = self::searchEngine($this->classesToSearch, $keywords, $start, $pageLength);
		}
		
		// filter by permission
		if($results) foreach($results as $result) {
			if(!$result->canView()) $results->remove($result);
		}
		
		// reset locale
		if(class_exists('Translatable')) {
			if(singleton('SiteTree')->hasExtension('Translatable') && isset($data['searchlocale'])) {
				if($data['searchlocale'] == "ALL") {
					Translatable::enable_locale_filter();
				} else {
					Translatable::set_current_locale($origLocale);
				}
			}
		}

		return $results;
	}
	
	
	/* replacement for DB::getConn()->searchEngine(), same as original just added 'SearchIndex' to the field list */
	public function searchEngine($classesToSearch, $keywords, $start, $pageLength, $sortBy = "Relevance DESC",
			$extraFilter = "", $booleanSearch = false, $alternativeFileFilter = "", $invertedMatch = false) {

		if(!class_exists('SiteTree')) throw new Exception('MySQLDatabase->searchEngine() requires "SiteTree" class');
		if(!class_exists('File')) throw new Exception('MySQLDatabase->searchEngine() requires "File" class');
		
		$fileFilter = '';
		$keywords = Convert::raw2sql($keywords);
		$htmlEntityKeywords = htmlentities($keywords, ENT_NOQUOTES, 'UTF-8');

		$extraFilters = array('SiteTree' => '', 'File' => '');

		if($booleanSearch) $boolean = "IN BOOLEAN MODE";

		if($extraFilter) {
			$extraFilters['SiteTree'] = " AND $extraFilter";

			if($alternativeFileFilter) $extraFilters['File'] = " AND $alternativeFileFilter";
			else $extraFilters['File'] = $extraFilters['SiteTree'];
		}

		// Always ensure that only pages with ShowInSearch = 1 can be searched
		$extraFilters['SiteTree'] .= " AND ShowInSearch <> 0";
		
		// File.ShowInSearch was added later, keep the database driver backwards compatible 
		// by checking for its existence first
		$fields = DB::getConn()->fieldList('File');
		if(array_key_exists('ShowInSearch', $fields)) $extraFilters['File'] .= " AND ShowInSearch <> 0";

		$limit = $start . ", " . (int) $pageLength;

		$notMatch = $invertedMatch ? "NOT " : "";
		if($keywords) {
			$match['SiteTree'] = "
				MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$keywords' $boolean)
				+ MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$htmlEntityKeywords' $boolean)
			";
			$match['File'] = "MATCH (Filename, Title, Content) AGAINST ('$keywords' $boolean) AND ClassName = 'File'";

			// We make the relevance search by converting a boolean mode search into a normal one
			$relevanceKeywords = str_replace(array('*','+','-'),'',$keywords);
			$htmlEntityRelevanceKeywords = str_replace(array('*','+','-'),'',$htmlEntityKeywords);
			$relevance['SiteTree'] = "MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) "
				. "AGAINST ('$relevanceKeywords') "
				. "+ MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$htmlEntityRelevanceKeywords')";
			$relevance['File'] = "MATCH (Filename, Title, Content) AGAINST ('$relevanceKeywords')";
		} else {
			$relevance['SiteTree'] = $relevance['File'] = 1;
			$match['SiteTree'] = $match['File'] = "1 = 1";
		}

		// Generate initial DataLists and base table names
		$lists = array();
		$baseClasses = array('SiteTree' => '', 'File' => '');
		foreach($classesToSearch as $class) {
			$lists[$class] = DataList::create($class)->where($notMatch . $match[$class] . $extraFilters[$class], "");
			$baseClasses[$class] = '"'.$class.'"';
		}

		// Make column selection lists
		$select = array(
			'SiteTree' => array(
				"ClassName", "$baseClasses[SiteTree].\"ID\"", "ParentID",
				"Title", "MenuTitle", "URLSegment", "Content",
				"LastEdited", "Created",
				"Filename" => "_utf8''", "Name" => "_utf8''",
				"Relevance" => $relevance['SiteTree'], "CanViewType"
			),
			'File' => array(
				"ClassName", "$baseClasses[File].\"ID\"", "ParentID" => "_utf8''",
				"Title", "MenuTitle" => "_utf8''", "URLSegment" => "_utf8''", "Content",
				"LastEdited", "Created",
				"Filename", "Name",
				"Relevance" => $relevance['File'], "CanViewType" => "NULL"
			),
		);

		// Process and combine queries
		$querySQLs = array();
		$totalCount = 0;
		foreach($lists as $class => $list) {
			$query = $list->dataQuery()->query();

			// There's no need to do all that joining
			$query->setFrom(array(str_replace(array('"','`'), '', $baseClasses[$class]) => $baseClasses[$class]));
			$query->setSelect($select[$class]);
			$query->setOrderBy(array());
			
			$querySQLs[] = $query->sql();
			$totalCount += $query->unlimitedRowCount();
		}
		$fullQuery = implode(" UNION ", $querySQLs) . " ORDER BY $sortBy LIMIT $limit";

		// Get records
		$records = DB::query($fullQuery);

		$objects = array();

		foreach($records as $record) {
			$objects[] = new $record['ClassName']($record);
		}

		$list = new PaginatedList(new ArrayList($objects));
		$list->setPageStart($start);
		$list->setPageLength($pageLength);
		$list->setTotalItems($totalCount);

		// The list has already been limited by the query above
		$list->setLimitItems(false);

		return $list;
	}

}


