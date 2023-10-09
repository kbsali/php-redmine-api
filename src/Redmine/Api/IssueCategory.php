<?php

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;

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

    /**
     * List issue categories for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param string|int $projectIdentifier project id or literal identifier
     * @param array      $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     *
     * @return array list of issue categories found
     */
    final public function listByProject($projectIdentifier, array $params = []): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__
            ));
        }

        $this->issueCategories = $this->retrieveData('/projects/'.strval($projectIdentifier).'/issue_categories.json', $params);

        return $this->issueCategories;
    }

    /**
     * List issue categories.
     *
     * @deprecated since v2.4.0, use listByProject() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of issue categories found
     */
    public function all($project, array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::listByProject()` instead.', E_USER_DEPRECATED);

        return $this->listByProject(strval($project), $params);
    }

    /**
     * Returns an array of categories with name/id pairs.
     *
     * @param string|int $project     project id or literal identifier
     * @param bool       $forceUpdate to force the update of the projects var
     *
     * @return array list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false)
    {
        if (true === $forceUpdate || empty($this->issueCategories)) {
            $this->listByProject($project);
        }
        $ret = [];
        foreach ($this->issueCategories['issue_categories'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a category id given its name and related project.
     *
     * @param string|int $project project id or literal identifier
     * @param string     $name
     *
     * @return int|false
     */
    public function getIdByName($project, $name)
    {
        $arr = $this->listing($project);
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
     * @param string $id the issue category id
     *
     * @return array information about the category
     */
    public function show($id)
    {
        return $this->get('/issue_categories/'.urlencode($id).'.json');
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
     * @return string|false
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

        return $this->post(
            '/projects/'.$project.'/issue_categories.xml',
            XmlSerializer::createFromArray(['issue_category' => $params])->getEncoded()
        );
    }

    /**
     * Update issue category's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#PUT
     *
     * @param string $id the issue category id
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

        return $this->put(
            '/issue_categories/'.$id.'.xml',
            XmlSerializer::createFromArray(['issue_category' => $params])->getEncoded()
        );
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
     * @return string
     */
    public function remove($id, array $params = [])
    {
        return $this->delete(
            PathSerializer::create('/issue_categories/'.$id.'.xml', $params)->getPath()
        );
    }
}
