<?php

namespace Redmine\Api;

/**
 * Custom queries retrieval.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Queries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Query extends AbstractApi
{
    private $query = [];

    /**
     * Returns the list of all custom queries visible by the user (public and private queries) for all projects.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Queries#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of queries found
     */
    public function all(array $params = [])
    {
        $this->query = $this->retrieveAll('/queries.json', $params);

        return $this->query;
    }
}
