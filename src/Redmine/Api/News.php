<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_News
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class News extends AbstractApi
{
    private $news = [];

    /**
     * List news for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int $projectIdentifier project id or literal identifier
     * @param array      $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of news found
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
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of news found
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
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int $project project id or literal identifier [optional]
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of news found or error message or false
     */
    public function all($project = null, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` or `' . __CLASS__ . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            if (null === $project) {
                $this->news = $this->list($params);
            } else {
                $this->news = $this->listByProject(strval($project), $params);
            }
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->news;
    }
}
