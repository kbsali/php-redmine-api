<?php

namespace Redmine\Api;

/**
 * Handling project memberships.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Memberships
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Membership extends AbstractApi
{
    private $memberships = [];

    /**
     * List memberships for a given $project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of memberships found
     */
    public function all($project, array $params = [])
    {
        $this->memberships = $this->retrieveAll('/projects/'.$project.'/memberships.json', $params);

        return $this->memberships;
    }

    /**
     * Create a new membership for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#POST
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  the new membership data
     *
     * @throws \Exception Missing mandatory parameters
     *
     * @return string|false
     */
    public function create($project, array $params = [])
    {
        $defaults = [
            'user_id' => null,
            'role_ids' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (!isset($params['user_id']) || !isset($params['role_ids'])) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->buildXML($params);

        return $this->post('/projects/'.$project.'/memberships.xml', $xml->asXML());
    }

    /**
     * Update membership information's by id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#PUT
     *
     * @param int   $id     id of the membership
     * @param array $params the new membership data
     *
     * @throws \Exception Missing mandatory parameters
     *
     * @return string|false
     */
    public function update($id, array $params = [])
    {
        $defaults = [
            'role_ids' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (!isset($params['role_ids'])) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->buildXML($params);

        return $this->put('/memberships/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete a membership.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#DELETE
     *
     * @param int $id id of the membership
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/memberships/'.$id.'.xml');
    }

    /**
     * Build the XML for a membership.
     *
     * @param array $params for the new/updated membership data
     *
     * @return \SimpleXMLElement
     */
    private function buildXML(array $params = [])
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><membership></membership>');

        foreach ($params as $k => $v) {
            if ('role_ids' === $k && is_array($v)) {
                $item = $xml->addChild($k);
                $item->addAttribute('type', 'array');
                foreach ($v as $role) {
                    $item->addChild('role_id', $role);
                }
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $xml;
    }
}
