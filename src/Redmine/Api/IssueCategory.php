<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
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
    private $issueCategories = [];

    private $issueCategoriesNames = [];

    /**
     * List issue categories for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param string|int $projectIdentifier project id or literal identifier
     * @param array      $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of issue categories found
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

        $this->issueCategoriesNames[$projectIdentifier] = [];

        $list = $this->listByProject($projectIdentifier);

        if (array_key_exists('issue_categories', $list)) {
            foreach ($list['issue_categories'] as $category) {
                $this->issueCategoriesNames[$projectIdentifier][(int) $category['id']] = $category['name'];
            }
        }

        return $this->issueCategoriesNames[$projectIdentifier];
    }

    /**
     * List issue categories.
     *
     * @deprecated v2.4.0 Use listByProject() instead.
     * @see IssueCategory::listByProject()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of issue categories found or error message or false
     */
    public function all($project, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            return $this->listByProject(strval($project), $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }
    }

    /**
     * Returns an array of categories with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNamesByProject() instead.
     * @see IssueCategory::listNamesByProject()
     *
     * @param string|int $project     project id or literal identifier
     * @param bool       $forceUpdate to force the update of the projects var
     *
     * @return array list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNamesByProject()` instead.', E_USER_DEPRECATED);

        return $this->doListing($project, $forceUpdate);
    }

    /**
     * Get a category id given its name and related project.
     *
     * @deprecated v2.7.0 Use listNamesByProject() instead.
     * @see IssueCategory::listNamesByProject()
     *
     * @param string|int $project project id or literal identifier
     * @param string     $name
     *
     * @return int|false
     */
    public function getIdByName($project, $name)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNamesByProject()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing($project, false);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * Get extended information about an issue category.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET-2
     *
     * @param int $id the issue category id
     *
     * @return array|false|string information about the category as array or false|string on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/issue_categories/' . urlencode(strval($id)) . '.json',
        ));

        $body = $this->lastResponse->getContent();

        if ('' === $body) {
            return false;
        }

        try {
            return JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            return 'Error decoding body as JSON: ' . $e->getPrevious()->getMessage();
        }
    }

    /**
     * Create a new issue category of $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#POST
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  the new issue category data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return SimpleXMLElement|string
     */
    public function create($project, array $params = [])
    {
        $defaults = [
            'name' => null,
            'assigned_to_id' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

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
     * @param int $id the issue category id
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'name' => null,
            'assigned_to_id' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/issue_categories/' . urlencode(strval($id)) . '.xml',
            XmlSerializer::createFromArray(['issue_category' => $params])->getEncoded(),
        ));

        return $this->lastResponse->getContent();
    }

    /**
     * Delete an issue category.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#DELETE
     * available $params :
     * - reassign_to_id : when there are issues assigned to the category you are deleting, this parameter lets you reassign these issues to the category with this id
     *
     * @param int   $id     id of the category
     * @param array $params extra GET parameters
     *
     * @return string empty string on success
     */
    public function remove($id, array $params = [])
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            PathSerializer::create('/issue_categories/' . urlencode(strval($id)) . '.xml', $params)->getPath(),
        ));

        return $this->lastResponse->getContent();
    }

    private function doListing($project, bool $forceUpdate)
    {
        if (true === $forceUpdate || empty($this->issueCategories)) {
            $this->issueCategories = $this->listByProject($project);
        }

        $ret = [];

        foreach ($this->issueCategories['issue_categories'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}
