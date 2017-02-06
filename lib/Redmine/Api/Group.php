<?php

namespace Redmine\Api;

/**
 * Handling of groups.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Groups
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Group extends AbstractApi
{
    private $groups = [];

    /**
     * List groups.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of groups found
     */
    public function all(array $params = [])
    {
        $this->groups = $this->retrieveAll('/groups.json', $params);

        return $this->groups;
    }

    /**
     * Returns an array of groups with name/id pairs.
     *
     * @param bool $forceUpdate to force the update of the groups var
     *
     * @return array list of groups (id => name)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->groups) || $forceUpdate) {
            $this->all();
        }
        $ret = [];
        foreach ($this->groups['groups'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Create a new group with a group of users assigned.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST
     *
     * @param array $params the new group data
     *
     * @throws \Exception Missing mandatory parameters
     *
     * @return \SimpleXMLElement
     */
    public function create(array $params = [])
    {
        $defaults = [
            'name' => null,
            'user_ids' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
            !isset($params['name'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->buildXML($params);

        return $this->post('/groups.xml', $xml->asXML());
    }

    /**
     * NOT DOCUMENTED in Redmine's wiki.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#PUT
     *
     * @param int   $id
     * @param array $params
     *
     * @throws \Exception Not implemented
     */
    public function update($id, array $params = [])
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Returns details of a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET-2
     * available $params :
     * - include: a coma separated list of associations to include in the response: users,memberships
     *
     * @param int   $id     the group id
     * @param array $params params to pass to url
     *
     * @return array
     */
    public function show($id, array $params = [])
    {
        return $this->get('/groups/'.urlencode($id).'.json?'.http_build_query($params));
    }

    /**
     * Delete a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#DELETE
     *
     * @param int $id id of the group
     *
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/groups/'.$id.'.xml');
    }

    /**
     * Adds an existing user to a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST-2
     *
     * @param int $id     id of the group
     * @param int $userId id of the user
     *
     * @return string
     */
    public function addUser($id, $userId)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user_id>'.$userId.'</user_id>');

        return $this->post('/groups/'.$id.'/users.xml', $xml->asXML());
    }

    /**
     * Removes a user from a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#DELETE-2
     *
     * @param int $id     id of the group
     * @param int $userId id of the user
     *
     * @return string
     */
    public function removeUser($id, $userId)
    {
        return $this->delete('/groups/'.$id.'/users/'.$userId.'.xml');
    }

    /**
     * Build the XML for a group.
     *
     * @param array $params for the new/updated group data
     *
     * @return \SimpleXMLElement
     */
    private function buildXML(array $params = [])
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><group></group>');

        foreach ($params as $k => $v) {
            if ('user_ids' === $k && is_array($v)) {
                $item = $xml->addChild($k);
                foreach ($v as $role) {
                    $item->addChild('user_id', $role);
                }
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $xml;
    }
}
