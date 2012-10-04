<?php

namespace Redmine\Api;

/**
 * Listing users, creating, editing
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Users
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class User extends AbstractApi
{
    private $users = array();

    /**
     * List users
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Users#GET
     *
     * @return array list of users found
     */
    public function all()
    {
        $this->users = $this->get('/users.json');

        return $this->users;
    }

    /**
     * Returns an array of users with login/id pairs
     *
     * @param  $forceUpdate to force the update of the users var
     * @return array list of users (id => username)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->users)) {
            $this->all();
        }
        $ret = array();
        foreach ($this->users['users'] as $e) {
            $ret[$e['login']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a user id given its username
     * @param  string $username
     * @return int
     */
    public function getIdByUsername($username)
    {
        $arr = $this->listing();
        if (!isset($arr[$username])) {
            return false;
        }

        return $arr[(string) $username];
    }

    /**
     * Get extended information about a user (including memberships + groups)
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Users#GET-2
     *
     * @param  string $id the user id
     * @return array  information about the user
     */
    public function show($id)
    {
        return $this->get('/users/'.urlencode($id).'.json?include=memberships,groups');
    }

    /**
     * Create a new user given an array of $params
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Users#POST
     *
     * @param  array             $params the new user data
     * @return \SimpleXMLElement
     */
    public function create(array $params = array())
    {
        $defaults = array(
            'login'     => null,
            'password'  => null,
            'lastname'  => null,
            'firstname' => null,
            'mail'      => null,
            // 'auth_source_id' => null,
        );
        $params = array_filter(array_merge($defaults, $params));
        if(
            !isset($params['login'])
         || !isset($params['lastname'])
         || !isset($params['firstname'])
         || !isset($params['mail'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user></user>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/users.xml', $xml->asXML());
    }

    /**
     * Update user's information
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Users#PUT
     *
     * @param  string            $id     the user id
     * @param  array             $params
     * @return \SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'id'        => $id,
            'login'     => null,
            'password'  => null,
            'lastname'  => null,
            'firstname' => null,
            'mail'      => null,
            // 'auth_source_id' => null,
        );
        $params = array_filter(array_merge($defaults, $params));

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user></user>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/users/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete a user
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Users#DELETE
     *
     * @param  int  $id id of the user
     * @return void
     */
    public function remove($id)
    {
        return $this->delete('/users/'.$id.'.xml');
    }
}
