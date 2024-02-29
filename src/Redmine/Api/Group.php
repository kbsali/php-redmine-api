<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

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
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * List groups.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see Group::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of groups found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->groups = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
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
     * @return string|SimpleXMLElement|false
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

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/groups.xml',
            XmlSerializer::createFromArray(['group' => $params])->getEncoded()
        ));

        $body = $this->lastResponse->getContent();

        if ($body === '') {
            return $body;
        }

        return new SimpleXMLElement($body);
    }

    /**
     * Updates a group.
     *
     * NOT DOCUMENTED in Redmine's wiki.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#PUT
     *
     * @param int $id the group id
     *
     * @return string empty string
     */
    public function update(int $id, array $params = [])
    {
        $defaults = [
            'name' => null,
            'user_ids' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        return $this->put(
            '/groups/' . $id . '.xml',
            XmlSerializer::createFromArray(['group' => $params])->getEncoded()
        );
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
     * @return array|false|string information about the group as array or false|string on error
     */
    public function show($id, array $params = [])
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create('/groups/' . urlencode(strval($id)) . '.json', $params)->getPath()
        ));

        $body = $this->lastResponse->getContent();

        if ('' === $body) {
            return false;
        }

        try {
            return JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            return 'Error decoding body as JSON: ' . $e->getPrevious()->getMessage();
        }
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
        return $this->delete('/groups/' . $id . '.xml');
    }

    /**
     * Adds an existing user to a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST-2
     *
     * @param int $id     id of the group
     * @param int $userId id of the user
     *
     * @return SimpleXMLElement|string
     */
    public function addUser($id, $userId)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/groups/' . $id . '/users.xml',
            XmlSerializer::createFromArray(['user_id' => $userId])->getEncoded()
        ));

        $body = $this->lastResponse->getContent();

        if ($body === '') {
            return $body;
        }

        return new SimpleXMLElement($body);
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
        return $this->delete('/groups/' . $id . '/users/' . $userId . '.xml');
    }
}
