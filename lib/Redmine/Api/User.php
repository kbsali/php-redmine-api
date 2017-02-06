<?php

namespace Redmine\Api;

/**
 * Listing users, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Users
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class User extends AbstractApi
{
    private $users = [];

    /**
     * List users.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#GET
     *
     * @param array $params to allow offset/limit (and more) to be passed
     *
     * @return array list of users found
     */
    public function all(array $params = [])
    {
        $this->users = $this->retrieveAll('/users.json', $params);

        return $this->users;
    }

    /**
     * Returns an array of users with login/id pairs.
     *
     * @param bool  $forceUpdate to force the update of the users var
     * @param array $params      to allow offset/limit (and more) to be passed
     *
     * @return array list of users (id => username)
     */
    public function listing($forceUpdate = false, array $params = [])
    {
        if (empty($this->users) || $forceUpdate) {
            $this->all($params);
        }
        $ret = [];
        if (is_array($this->users) && isset($this->users['users'])) {
            foreach ($this->users['users'] as $e) {
                $ret[$e['login']] = (int) $e['id'];
            }
        }

        return $ret;
    }

    /**
     * Return the current user data.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#usersidformat
     *
     * @param array $params extra associated data
     *
     * @return array current user data
     */
    public function getCurrentUser(array $params = [])
    {
        return $this->show('current', $params);
    }

    /**
     * Get a user id given its username.
     *
     * @param string $username
     * @param array  $params   to allow offset/limit (and more) to be passed
     *
     * @return int|bool
     */
    public function getIdByUsername($username, array $params = [])
    {
        $arr = $this->listing(false, $params);
        if (!isset($arr[$username])) {
            return false;
        }

        return $arr[(string) $username];
    }

    /**
     * Get extended information about a user (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#GET-2
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#usersidformat
     * available $params :
     * include: fetch associated data (optional). Possible values:
     *  - memberships: adds extra information about user's memberships and roles on the projects
     *  - groups (added in 2.1): adds extra information about user's groups
     *  - api_key: the API key of the user, visible for admins and for yourself (added in 2.3.0)
     *  - status: a numeric id representing the status of the user, visible for admins only (added in 2.4.0)
     *
     * @param string $id     the user id
     * @param array  $params extra associated data
     *
     * @return array information about the user
     */
    public function show($id, array $params = [])
    {
        // set default ones
        $params['include'] = array_unique(
            array_merge(
                isset($params['include']) ? $params['include'] : [],
                [
                    'memberships',
                    'groups',
                ]
            )
        );
        $params['include'] = implode(',', $params['include']);

        return $this->get(sprintf(
            '/users/%s.json?%s',
            urlencode($id),
            http_build_query($params)
        ));
    }

    /**
     * Create a new user given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#POST
     *
     * @param array $params the new user data
     *
     * @throws \Exception Missing mandatory parameters
     *
     * @return string|false
     */
    public function create(array $params = [])
    {
        $defaults = [
            'login' => null,
            'password' => null,
            'lastname' => null,
            'firstname' => null,
            'mail' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
            !isset($params['login'])
         || !isset($params['lastname'])
         || !isset($params['firstname'])
         || !isset($params['mail'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user></user>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $this->post('/users.xml', $xml->asXML());
    }

    /**
     * Update user's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#PUT
     *
     * @param string $id     the user id
     * @param array  $params
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
            'login' => null,
            'password' => null,
            'lastname' => null,
            'firstname' => null,
            'mail' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><user></user>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $this->put('/users/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete a user.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Users#DELETE
     *
     * @param int $id id of the user
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/users/'.$id.'.xml');
    }
}
