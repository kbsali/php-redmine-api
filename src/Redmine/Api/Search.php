<?php

declare(strict_types=1);

namespace Redmine\Api;

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
}
