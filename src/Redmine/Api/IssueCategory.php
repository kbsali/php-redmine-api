<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing issue categories, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssueCategory extends AbstractApi
{
    use RequestParamsTrait;
    use ResponseValidationTrait;

    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @var array<mixed>
     */
    private array $issueCategoriesNames = [];

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see IssueCategory::fromHttpClient()
     *
     * @param HttpClient $client
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
     * List issue categories for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param string|int   $projectIdentifier project id or literal identifier
     * @param array<mixed> $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code (only if future mode is enabled)
     *
     * @return array<mixed> list of issue categories found
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
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/issue_categories.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all issue categories by a project with id/name pairs.
     *
     * @param string|int $projectIdentifier project id or literal identifier
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code (only if future mode is enabled)
     *
     * @return array<int,string> list of issue category names (id => name)
     */
    final public function listNamesByProject($projectIdentifier): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        if (array_key_exists($projectIdentifier, $this->issueCategoriesNames)) {
            return $this->issueCategoriesNames[$projectIdentifier];
        }

        $names = [];

        $list = $this->listByProject($projectIdentifier);

        if (array_key_exists('issue_categories', $list)) {
            foreach ($list['issue_categories'] as $category) {
                $names[(int) $category['id']] = $category['name'];
            }
        }

        $this->issueCategoriesNames[$projectIdentifier] = $names;

        return $this->issueCategoriesNames[$projectIdentifier];
    }

    /**
     * Get extended information about an issue category.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET-2
     *
     * @param int $id the issue category id
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the category
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/issue_categories/' . urlencode(strval($id)) . '.json',
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new issue category of $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#POST
     *
     * @param string|int   $project project id or literal identifier
     * @param array<mixed> $params  the new issue category data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return SimpleXMLElement|string
     */
    public function create($project, array $params = [])
    {
        $params = $this->buildRequestParams([], $params);

        if (
            !isset($params['name'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `name`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects/' . $project . '/issue_categories.xml',
            XmlSerializer::createFromArray(['issue_category' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        if ($body === '') {
            return $body;
        }

        return new SimpleXMLElement($body);
    }

    /**
     * Update issue category's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#PUT
     *
     * @param int          $id     the issue category id
     * @param array<mixed> $params
     *
     * @return string
     */
    public function update($id, array $params)
    {
        $params = $this->buildRequestParams([], $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/issue_categories/' . urlencode(strval($id)) . '.xml',
            XmlSerializer::createFromArray(['issue_category' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Delete an issue category.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#DELETE
     * available $params :
     * - reassign_to_id : when there are issues assigned to the category you are deleting, this parameter lets you reassign these issues to the category with this id
     *
     * @param int          $id     id of the category
     * @param array<mixed> $params extra GET parameters
     *
     * @return string empty string on success
     */
    public function remove($id, array $params = [])
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            PathSerializer::create('/issue_categories/' . urlencode(strval($id)) . '.xml', $params)->getPath(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

}
