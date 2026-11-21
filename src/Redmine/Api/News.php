<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_News
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class News extends AbstractApi
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
     * List news for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int   $projectIdentifier project id or literal identifier
     * @param array<mixed> $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of news found
     */
    final public function listByProject($projectIdentifier, array $params = []): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        try {
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/news.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * List news for all projects.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of news found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/news.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

}
