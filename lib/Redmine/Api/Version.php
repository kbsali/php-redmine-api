<?php

namespace Redmine\Api;

/**
 * Listing versions, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Versions
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Version extends AbstractApi
{
    private $versions = [];

    /**
     * List versions.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of versions found
     */
    public function all($project, array $params = [])
    {
        $this->versions = $this->retrieveAll('/projects/'.$project.'/versions.json', $params);

        return $this->versions;
    }

    /**
     * Returns an array of name/id pairs (or id/name if not $reverse) of issue versions for $project.
     *
     * @param string|int $project     project id or literal identifier
     * @param bool       $forceUpdate to force the update of the projects var
     * @param bool       $reverse     to return an array indexed by name rather than id
     * @param array      $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false, $reverse = true, array $params = [])
    {
        if (true === $forceUpdate || empty($this->versions)) {
            $this->all($project, $params);
        }
        $ret = [];
        foreach ($this->versions['versions'] as $e) {
            $ret[(int) $e['id']] = $e['name'];
        }

        return $reverse ? array_flip($ret) : $ret;
    }

    /**
     * Get an issue version id given its name and related project.
     *
     * @param string|int $project project id or literal identifier
     * @param string     $name
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($project, $name, array $params = [])
    {
        $arr = $this->listing($project, false, true, $params);
        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * Get extended information about an issue version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET-2
     *
     * @param string $id the issue category id
     *
     * @return array information about the category
     */
    public function show($id)
    {
        return $this->get('/versions/'.urlencode($id).'.json');
    }

    /**
     * Create a new version for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#POST
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  the new issue category data
     *
     * @throws \Exception Missing mandatory parameters
     *
     * @return string|false
     */
    public function create($project, array $params = [])
    {
        $defaults = [
            'name' => null,
            'description' => null,
            'status' => null,
            'sharing' => null,
            'due_date' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
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
     * Update issue category's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#PUT
     *
     * @param string $id     the issue category id
     * @param array  $params
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'name' => null,
            'description' => null,
            'status' => null,
            'sharing' => null,
            'due_date' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);
        $this->validateStatus($params);
        $this->validateSharing($params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><version></version>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/versions/'.$id.'.xml', $xml->asXML());
    }

    private function validateStatus(array $params = [])
    {
        $arrStatus = [
            'open',
            'locked',
            'closed',
        ];
        if (isset($params['status']) && !in_array($params['status'], $arrStatus)) {
            throw new \Exception('Possible values for status : '.implode(', ', $arrStatus));
        }
    }

    private function validateSharing(array $params = [])
    {
        $arrSharing = [
            'none' => 'Not shared',
            'descendants' => 'With subprojects',
            'hierarchy' => 'With project hierarchy',
            'tree' => 'With project tree',
            'system' => 'With all projects',
        ];
        if (isset($params['sharing']) && !isset($arrSharing[$params['sharing']])) {
            throw new \Exception('Possible values for sharing : '.implode(', ', array_keys($arrSharing)));
        }
    }

    /**
     * Delete a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#DELETE
     *
     * @param int $id id of the version
     *
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/versions/'.$id.'.xml');
    }
}
