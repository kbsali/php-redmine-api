<?php

namespace Redmine\Api;

/**
 * Handling project memberships
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Memberships
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Membership extends AbstractApi
{
    private $memberships = array();

    /**
     * List memberships for a given $project
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#GET
     *
     * @param  string|int $project project id or literal identifier
     * @return array      list of memberships found
     */
    public function all($project)
    {
        $this->memberships = $this->get('/projects/'.$project.'/memberships.json');

        return $this->memberships;
    }

    /**
     * Create a new membership for $project given an array of $params
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#POST
     *
     * @param  string|int        $project project id or literal identifier
     * @param  array             $params  the new membership data
     * @return \SimpleXMLElement
     */
    public function create($project, array $params = array())
    {
        $defaults = array(
            'user_id'  => null,
            'role_ids' => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        if(
            !isset($params['user_id'])
         || !isset($params['role_ids'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->buildMembershipXML($params);

        return $this->post('/projects/'.$project.'/memberships.xml', $xml->asXML());
    }

    /**
     * Update membership information's by id
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#PUT
     *
     * @param  int        $id     id of the membership
     * @param  array      $params the new membership data
     * @return \SimpleXMLElement
     */
    public function update($id, array $params = array())
    {
        $defaults = array(
            'role_ids' => null
        );
        $params = array_filter(array_merge($defaults, $params));
        if(!isset($params['role_ids'])) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = $this->buildMembershipXML($params);

        return $this->put('/memberships/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete a membership
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#DELETE
     *
     * @param  int  $id id of the membership
     * @return void
     */
    public function remove($id)
    {
        return $this->delete('/memberships/'.$id.'.xml');
    }

    /**
     * Build the XML for a membership
     * @param  array             $params for the new/updated membership data
     * @return \SimpleXMLElement
     */
    private function buildMembershipXML(array $params = array())
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
