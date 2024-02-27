<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
 */
class Search extends AbstractApi
{
    private $results = [];

    /**
     * list search results by Query.
     *
     * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
     *
     * @param string $query  string to search
     * @param array  $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of results (projects, issues)
     */
    final public function listByQuery(string $query, array $params = []): array
    {
        $params['q'] = $query;

        try {
            return $this->retrieveData('/search.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Search.
     *
     * @deprecated v2.4.0 Use listByQuery() instead.
     * @see Search::listByQuery()
     *
     * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
     *
     * @param string $query  string to search
     * @param array  $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of results (projects, issues) found or error message or false
     */
    public function search($query, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByQuery()` instead.', E_USER_DEPRECATED);

        try {
            $this->results = $this->listByQuery($query, $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->results;
    }
}
