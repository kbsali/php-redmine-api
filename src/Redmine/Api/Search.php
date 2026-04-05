<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Search
 */
class Search extends AbstractApi
{
    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see Search::fromHttpClient()
     *
     * @param Client|HttpClient $client
     */
    public function __construct($client/*, bool $privatelyCalled = false*/)
    {
        $privatelyCalled = (func_num_args() > 1) ? func_get_arg(1) : false;

        if ($privatelyCalled === true) {
            parent::__construct($client);

            return;
        }

        if (static::class !== self::class) {
            $className = (new \ReflectionClass($this))->isAnonymous() ? '' : ' in `' . static::class . '`';
            @trigger_error('Class `' . self::class . '` will declared as final in v3.0.0, stop extending it' . $className . '.', E_USER_DEPRECATED);
        } else {
            @trigger_error('Method `' . __METHOD__ . '()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `' . self::class . '::fromHttpClient()` instead.', E_USER_DEPRECATED);
        }

        parent::__construct($client);
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
    public function search($query, array $params = [])
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