<?php

namespace ChristopherBolt\BoltTools\Search;

use SilverStripe\CMS\Search\SearchForm;
use Translatable;
use Exception;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\ArrayList;

/* extends search form to support the custom SearchIndex, 
just replaces calls to DB::get_conn()->searchEngine() with a new function, only difference is this function uses a different field list */

class BoltSearchForm extends SearchForm {
	
	/* identical to the original function except replaces the DB::get_conn()->searchEngine() function calls with self::searchEngine() */
	public function getResults()
    {
        // Get request data from request handler
        $request = $this->getRequestHandler()->getRequest();

        // set language (if present)
        $locale = null;
        $origLocale = null;
        if (class_exists('Translatable')) {
            $locale = $request->requestVar('searchlocale');
            if (SiteTree::singleton()->hasExtension('Translatable') && $locale) {
                if ($locale === "ALL") {
                    Translatable::disable_locale_filter();
                } else {
                    $origLocale = Translatable::get_current_locale();

                    Translatable::set_current_locale($locale);
                }
            }
        }

        $keywords = $request->requestVar('Search');

        $andProcessor = function ($matches) {
            return ' +' . $matches[2] . ' +' . $matches[4] . ' ';
        };
        $notProcessor = function ($matches) {
            return ' -' . $matches[3];
        };

        $keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
        $keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);

        $keywords = $this->addStarsToKeywords($keywords);

        $pageLength = $this->getPageLength();
        $start = $request->requestVar('start') ?: 0;

        $booleanSearch =
            strpos($keywords, '"') !== false ||
            strpos($keywords, '+') !== false ||
            strpos($keywords, '-') !== false ||
            strpos($keywords, '*') !== false;
        $results = self::searchEngine($this->classesToSearch, $keywords, $start, $pageLength, "\"Relevance\" DESC", "", $booleanSearch);

        // filter by permission
        if ($results) {
            foreach ($results as $result) {
                if (!$result->canView()) {
                    $results->remove($result);
                }
            }
        }

        // reset locale
        if (class_exists('Translatable')) {
            if (SiteTree::singleton()->hasExtension('Translatable') && $locale) {
                if ($locale == "ALL") {
                    Translatable::enable_locale_filter();
                } else {
                    Translatable::set_current_locale($origLocale);
                }
            }
        }

        return $results;
    }
	
	
	/* replacement for DB::get_conn()->searchEngine(), same as original just added 'SearchIndex' to the field list */
	public function searchEngine(
        $classesToSearch,
        $keywords,
        $start,
        $pageLength,
        $sortBy = "Relevance DESC",
        $extraFilter = "",
        $booleanSearch = false,
        $alternativeFileFilter = "",
        $invertedMatch = false
    ) {
        $pageClass = SiteTree::class;
        $fileClass = File::class;
        if (!class_exists($pageClass)) {
            throw new Exception('MySQLDatabase->searchEngine() requires "SiteTree" class');
        }
        if (!class_exists($fileClass)) {
            throw new Exception('MySQLDatabase->searchEngine() requires "File" class');
        }

        $keywords = $this->escapeString($keywords);
        $htmlEntityKeywords = htmlentities($keywords, ENT_NOQUOTES, 'UTF-8');

        $extraFilters = array($pageClass => '', $fileClass => '');

        $boolean = '';
        if ($booleanSearch) {
            $boolean = "IN BOOLEAN MODE";
        }

        if ($extraFilter) {
            $extraFilters[$pageClass] = " AND $extraFilter";

            if ($alternativeFileFilter) {
                $extraFilters[$fileClass] = " AND $alternativeFileFilter";
            } else {
                $extraFilters[$fileClass] = $extraFilters[$pageClass];
            }
        }

        // Always ensure that only pages with ShowInSearch = 1 can be searched
        $extraFilters[$pageClass] .= " AND ShowInSearch <> 0";

        // File.ShowInSearch was added later, keep the database driver backwards compatible
        // by checking for its existence first
        $fileTable = DataObject::getSchema()->tableName($fileClass);
        $fields = $this->getSchemaManager()->fieldList($fileTable);
        if (array_key_exists('ShowInSearch', $fields)) {
            $extraFilters[$fileClass] .= " AND ShowInSearch <> 0";
        }

        $limit = (int)$start . ", " . (int)$pageLength;

        $notMatch = $invertedMatch
                ? "NOT "
                : "";
        if ($keywords) {
            $match[$pageClass] = "
				MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$keywords' $boolean)
				+ MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$htmlEntityKeywords' $boolean)
			";
            $fileClassSQL = Convert::raw2sql($fileClass);
            $match[$fileClass] = "MATCH (Name, Title) AGAINST ('$keywords' $boolean) AND ClassName = '$fileClassSQL'";

            // We make the relevance search by converting a boolean mode search into a normal one
            $relevanceKeywords = str_replace(array('*', '+', '-'), '', $keywords);
            $htmlEntityRelevanceKeywords = str_replace(array('*', '+', '-'), '', $htmlEntityKeywords);
            $relevance[$pageClass] = "MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) "
                    . "AGAINST ('$relevanceKeywords') "
                    . "+ MATCH (Title, MenuTitle, Content, MetaDescription, SearchIndex) AGAINST ('$htmlEntityRelevanceKeywords')";
            $relevance[$fileClass] = "MATCH (Name, Title) AGAINST ('$relevanceKeywords')";
        } else {
            $relevance[$pageClass] = $relevance[$fileClass] = 1;
            $match[$pageClass] = $match[$fileClass] = "1 = 1";
        }

        // Generate initial DataLists and base table names
        $lists = array();
        $sqlTables = array($pageClass => '', $fileClass => '');
        foreach ($classesToSearch as $class) {
            $lists[$class] = DataList::create($class)->where($notMatch . $match[$class] . $extraFilters[$class]);
            $sqlTables[$class] = '"' . DataObject::getSchema()->tableName($class) . '"';
        }

        $charset = static::config()->get('charset');

        // Make column selection lists
        $select = array(
            $pageClass => array(
                "ClassName", "{$sqlTables[$pageClass]}.\"ID\"", "ParentID",
                "Title", "MenuTitle", "URLSegment", "Content",
                "LastEdited", "Created",
                "Name" => "_{$charset}''",
                "Relevance" => $relevance[$pageClass], "CanViewType"
            ),
            $fileClass => array(
                "ClassName", "{$sqlTables[$fileClass]}.\"ID\"", "ParentID",
                "Title", "MenuTitle" => "_{$charset}''", "URLSegment" => "_{$charset}''", "Content" => "_{$charset}''",
                "LastEdited", "Created",
                "Name",
                "Relevance" => $relevance[$fileClass], "CanViewType" => "NULL"
            ),
        );

        // Process and combine queries
        $querySQLs = array();
        $queryParameters = array();
        $totalCount = 0;
        foreach ($lists as $class => $list) {
            /** @var SQLSelect $query */
            $query = $list->dataQuery()->query();

            // There's no need to do all that joining
            $query->setFrom($sqlTables[$class]);
            $query->setSelect($select[$class]);
            $query->setOrderBy(array());

            $querySQLs[] = $query->sql($parameters);
            $queryParameters = array_merge($queryParameters, $parameters);

            $totalCount += $query->unlimitedRowCount();
        }
        $fullQuery = implode(" UNION ", $querySQLs) . " ORDER BY $sortBy LIMIT $limit";

        // Get records
        $records = $this->preparedQuery($fullQuery, $queryParameters);

        $objects = array();

        foreach ($records as $record) {
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


