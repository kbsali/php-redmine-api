<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_News
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class News extends AbstractApi
{
    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see News::fromHttpClient()
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
     * List news for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int   $projectIdentifier project id or literal identifier
     * @param array<mixed> $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
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

    /**
     * List news (if no $project is given, it will return ALL the news).
     *
     * @deprecated v2.4.0 Use list() or listByProject() instead.
     * @see News::list()
     * @see News::listByProject()
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int   $project project id or literal identifier [optional]
     * @param array<mixed> $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<mixed>|string|false list of news found or error message or false
     */
    public function all($project = null, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . self::class . '::list()` or `' . self::class . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            $news = null === $project ? $this->list($params) : $this->listByProject(strval($project), $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() instanceof \Throwable) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $news;
    }
}
