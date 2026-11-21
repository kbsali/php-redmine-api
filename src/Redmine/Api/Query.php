<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * Custom queries retrieval.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Queries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Query extends AbstractApi
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
     * Returns the list of all custom queries visible by the user (public and private queries) for all projects.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Queries#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of queries found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/queries.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

}
