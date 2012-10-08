<?php

namespace Redmine\Api;

/**
 * Listing versions, creating, editing
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Versions
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Version extends AbstractApi
{
    private $versions = array();

    /**
     * List versions
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET
     *
     * @param  string|int $project project id or literal identifier
     * @return array      list of versions found
     */
    public function all($project)
    {
        $this->versions = $this->get('/projects/'.$project.'/versions.json');

        return $this->versions;
    }

    /**
     * Returns an array of projects with name/id pairs
     *
     * @param string|int $project project id or literal identifier
     * @param  $forceUpdate to force the update of the projects var
     * @return array list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false)
    {
        if (true === $forceUpdate || empty($this->versions)) {
            $this->all($project);
        }
        $ret = array();
        foreach ($this->versions['versions'] as $e) {
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
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET-2
     *
     * @param  string $id the issue category id
     * @return array  information about the category
     */
    public function show($id)
    {
        return $this->get('/versions/'.urlencode($id).'.json');
    }

    /**
     * Create a new issue category of $project given an array of $params
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Versions#POST
     *
     * @param  string|int        $project project id or literal identifier
     * @param  array             $params  the new issue category data
     * @return \SimpleXMLElement
     */
    public function create($project, array $params = array())
    {
        $defaults = array(
            'name'        => null,
            'description' => null,
            'status'      => null,
            'sharing'     => null,
            'due_date'    => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        if(
            !isset($params['name'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }
        $this->validateStatus($params);
        $this->validateSharing($params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><version></version>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/projects/'.$project.'/versions.xml', $xml->asXML());
    }

    /**
     * Update issue category's information
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Versions#PUT
     *
     * @param  string            $id     the issue category id
     * @param  array             $params
     * @return \SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'name'        => null,
            'description' => null,
            'status'      => null,
            'sharing'     => null,
            'due_date'    => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        $this->validateStatus($params);
        $this->validateSharing($params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><version></version>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/versions/'.$id.'.xml', $xml->asXML());
    }

    private function validateStatus(array $params = array())
    {
        $arrStatus = array(
            'open',
            'locked',
            'closed'
        );
        if (isset($params['status']) && !in_array($params['status'], $arrStatus)) {
            throw new \Exception('Possible values for status : '.join(', ', $arrStatus));
        }
    }

    private function validateSharing(array $params = array())
    {
        $arr = array(
            'none'        => 'Not shared',
            'descendants' => 'With subprojects',
            'hierarchy'   => 'With project hierarchy',
            'tree'        => 'With project tree',
            'system'      => 'With all projects',
        );
        if (isset($params['sharing']) && !isset($arrSharing[ $params['sharing'] ])) {
            throw new \Exception('Possible values for sharing : '.join(', ', array_keys($arrSharing)));
        }
    }

    /**
     * Delete an issue category
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Versions#DELETE
     *
     * @param  int    $id id of the category
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/versions/'.$id.'.xml');
    }
}
