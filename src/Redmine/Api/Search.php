<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
 */
final class Search extends AbstractApi
{
    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * list search results by Query.
     *
     * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
     *
     * @param string        $query  string to search
     * @param array<mixed>  $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of results (projects, issues)
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
     * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
     *
     * @param string        $query  string to search
     * @param array<mixed>  $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<mixed>|string|false list of results (projects, issues) found or error message or false
     */
    public function search($query, array $params = []): false|string|array
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . self::class . '::listByQuery()` instead.', E_USER_DEPRECATED);

        try {
            $results = $this->listByQuery((string) $query, $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() instanceof \Throwable) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $results;
    }
}
