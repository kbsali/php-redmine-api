<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

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
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of queries found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/queries.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns the list of all custom queries visible by the user (public and private queries) for all projects.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see Query::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Queries#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of queries found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->query = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->query;
    }
}
