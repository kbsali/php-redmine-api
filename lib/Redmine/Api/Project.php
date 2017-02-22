<?php

namespace Redmine\Api;

/**
 * Listing projects, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Projects
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Project extends AbstractApi
{
    private $projects = [];

    /**
     * List projects.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of projects found
     */
    public function all(array $params = [])
    {
        $this->projects = $this->retrieveAll('/projects.json', $params);

        return $this->projects;
    }

    /**
     * Returns an array of projects with name/id pairs (or id/name if $reserse is false).
     *
     * @param bool $forceUpdate to force the update of the projects var
     * @param bool $reverse     to return an array indexed by name rather than id
     *
     * @return array list of projects (id => project name)
     */
    public function listing($forceUpdate = false, $reverse = true)
    {
        if (true === $forceUpdate || empty($this->projects)) {
            $this->all();
        }
        $ret = [];
        foreach ($this->projects['projects'] as $e) {
            $ret[(int) $e['id']] = $e['name'];
        }

        return $reverse ? array_flip($ret) : $ret;
    }

    /**
     * Get a project id given its name.
     *
     * @param string $name
     *
     * @return int|bool
     */
    public function getIdByName($name)
    {
        $arr = $this->listing();
        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * Get extended information about a project (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects#Showing-a-project
     *
     * @param string $id the project id
     * @param array $params available parameters:
     *        include: fetch associated data (optional). Possible values: trackers, issue_categories, enabled_modules (since 2.6.0)
     *
     * @return array information about the project
     */
    public function show($id, array $params = [])
    {
        if (isset($params['include']) && is_array($params['include'])) {
            $params['include'] = implode(',', $params['include']);
        } else {
            $params['include'] = 'trackers,issue_categories,attachments,relations';
        }

        return $this->get('/projects/'.urlencode($id).'.json?'.http_build_query($params));
    }

    /**
     * Create a new project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param array $params the new project data
     *
     * @throws \Exception
     *
     * @return \SimpleXMLElement
     */
    public function create(array $params = [])
    {
        $defaults = [
            'name' => null,
            'identifier' => null,
            'description' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
            !isset($params['name'])
         || !isset($params['identifier'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->prepareParamsXml($params);

        return $this->post('/projects.xml', $xml->asXML());
    }

    /**
     * Update project's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param string $id     the project id
     * @param array  $params
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
            'name' => null,
            'identifier' => null,
            'description' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $xml = $this->prepareParamsXml($params);

        return $this->put('/projects/'.$id.'.xml', $xml->asXML());
    }

    /**
     * @param array $params
     *
     * @return \SimpleXMLElement
     */
    protected function prepareParamsXml($params)
    {
        $_params = [
            'tracker_ids' => 'tracker',
            'issue_custom_field_ids' => 'issue_custom_field',
            'enabled_module_names' => 'enabled_module_names',
        ];

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><project></project>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } elseif (isset($_params[$k]) && is_array($v)) {
                $array = $xml->addChild($k, '');
                $array->addAttribute('type', 'array');
                foreach ($v as $id) {
                    $array->addChild($_params[$k], $id);
                }
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $xml;
    }

    /**
     * Delete a project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param int $id id of the project
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/projects/'.$id.'.xml');
    }
}
