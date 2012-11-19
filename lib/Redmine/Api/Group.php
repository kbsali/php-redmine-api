<?php

namespace Redmine\Api;

/**
 * Handling of groups
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Groups
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Group extends AbstractApi
{
    private $groups = array();

    /**
     * List groups
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET
     *
     * @return array list of groups found
     */
    public function all()
    {
        $this->groups = $this->get('/groups.json');

        return $this->groups;
    }

    /**
     * Returns an array of groups with name/id pairs
     *
     * @param  boolean $forceUpdate to force the update of the groups var
     * @return array   list of groups (id => name)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->groups)) {
            $this->all();
        }
        $ret = array();
        foreach ($this->groups['groups'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Create a new group with a group of users assigned
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST
     *
     * @param  array             $params the new group data
     * @return \SimpleXMLElement
     */
    public function create(array $params = array())
    {
        $defaults = array(
            'name'     => null,
            'user_ids' => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        if(
            !isset($params['name'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><group></group>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/projects/groups.xml', $xml->asXML());
    }

    // public function update(array $params = array()) {}

    /**
     * Returns details of a group.
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET-2
     * available $params :
     * - include: a coma separated list of associations to include in the response: users,memberships
     *
     * @param  int   $id the group id
     * @return array
     */
    public function show($id, array $params = array())
    {
        return $this->get('/groups/'.urlencode($id).'.json?'.$this->http_build_str($params));
    }

    /**
     * Delete a group
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#DELETE
     *
     * @param  int    $id id of the group
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/groups/'.$id.'.xml');
    }

    /**
     * Adds an existing user to a group
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST-2
     *
     * @param  int    $id     id of the group
     * @param  int    $userId id of the user
     * @return string
     */
    public function addUser($id, $userId)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user_id>'.$userId.'</user_id>');

        return $this->post('/groups/'.$id.'/user/users.xml', $xml->asXML());
    }

    /**
     * Removes a user from a group
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Groups#DELETE-2
     *
     * @param  int    $id     id of the group
     * @param  int    $userId id of the user
     * @return string
     */
    public function removeUser($id, $userId)
    {
        return $this->delete('/groups/'.$id.'/user/'.$userId.'.xml');
    }
}
