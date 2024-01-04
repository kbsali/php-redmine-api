<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;

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
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of groups found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/groups.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }
    }

    /**
     * List groups.
     *
     * @deprecated since v2.4.0, use list() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of groups found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->groups = $this->list($params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

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
            $this->groups = $this->list();
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
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return string|false
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
            throw new MissingParameterException('Theses parameters are mandatory: `name`');
        }

        return $this->post(
            '/groups.xml',
            XmlSerializer::createFromArray(['group' => $params])->getEncoded()
        );
    }

    /**
     * NOT DOCUMENTED in Redmine's wiki.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#PUT
     *
     * @param int $id
     *
     * @throws Exception Not implemented
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
        return $this->get(
            PathSerializer::create('/groups/'.urlencode($id).'.json', $params)->getPath()
        );
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
        return $this->post(
            '/groups/'.$id.'/users.xml',
            XmlSerializer::createFromArray(['user_id' => $userId])->getEncoded()
        );
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
}
