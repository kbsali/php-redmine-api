<?php

namespace Redmine\Api;

/**
 * Searching
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
 *
 */
class Search extends AbstractApi
{
    private $results = [];
	
    /**
     * Search.
     *
     * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
     *
     * @param string $query string to search
     * @param array  $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of results (projects, issues)
     */
	public function search($query, array $params = [])
	{
		$params['q'] = $query;
		$this->results = $this->retrieveAll('/search.json', $params);
		
		return $this->results;
	}
}