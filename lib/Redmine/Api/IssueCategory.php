<?php

namespace Redmine\Api;

/**
 * Listing issue categories, creating, editing
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssueCategory extends AbstractApi
{
    private $issueCategories = array();

    /**
     * List issue categories
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET
     *
     * @param  string|int $project project id or literal identifier
     * @return array      list of issue categories found
     */
    public function all($project)
    {
        $this->issueCategories = $this->get('/projects/'.$project.'/issue_categories.json');

        return $this->issueCategories;
    }

    /**
     * Returns an array of categories with name/id pairs
     *
     * @param  string|int $project     project id or literal identifier
     * @param  boolean    $forceUpdate to force the update of the projects var
     * @return array      list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false)
    {
        if (true === $forceUpdate || empty($this->issueCategories)) {
            $this->all($project);
        }
        $ret = array();
        foreach ($this->issueCategories['issue_categories'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a category id given its name and related project
     *
     * @param  string|int $project project id or literal identifier
     * @param  string     $name
     * @return int
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
     * Get extended information about an issue category
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#GET-2
     *
     * @param  string $id the issue category id
     * @return array  information about the category
     */
    public function show($id)
    {
        return $this->get('/issue_categories/'.urlencode($id).'.json');
    }

    /**
     * Create a new issue category of $project given an array of $params
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#POST
     *
     * @param  string|int        $project project id or literal identifier
     * @param  array             $params  the new issue category data
     * @return \SimpleXMLElement
     */
    public function create($project, array $params = array())
    {
        $defaults = array(
            'name'           => null,
            'assigned_to_id' => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        if(
            !isset($params['name'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue_category></issue_category>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/projects/'.$project.'/issue_categories.xml', $xml->asXML());
    }

    /**
     * Update issue category's information
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#PUT
     *
     * @param  string            $id     the issue category id
     * @param  array             $params
     * @return \SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'name'           => null,
            'assigned_to_id' => null,
        );
        $params = array_filter(array_merge($defaults, $params));

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue_category></issue_category>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/issue_categories/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete an issue category
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueCategories#DELETE
     * available $params :
     * - reassign_to_id : when there are issues assigned to the category you are deleting, this parameter lets you reassign these issues to the category with this id
     *
     * @param  int    $id     id of the category
     * @param  array  $params extra GET parameters
     * @return string
     */
    public function remove($id, array $params = array())
    {
        return $this->delete('/issue_categories/'.$id.'.xml?'.$this->http_build_str($params));
    }
}
