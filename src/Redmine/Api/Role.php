<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;

/**
 * Listing roles.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Roles
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Role extends AbstractApi
{
    private $roles = [];

    private $roleNames = null;

    /**
     * List roles.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Roles#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of roles found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/roles.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all roles with id/name pairs.
     *
     * @return array<int,string> list of roles (id => name)
     */
    final public function listNames(): array
    {
        if ($this->roleNames !== null) {
            return $this->roleNames;
        }

        $this->roleNames = [];
        $list = $this->list();

        if (array_key_exists('roles', $list)) {
            foreach ($list['roles'] as $role) {
                $this->roleNames[(int) $role['id']] = $role['name'];
            }
        }

        return $this->roleNames;
    }

    /**
     * List roles.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see Role::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Roles#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of roles found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->roles = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->roles;
    }

    /**
     * Returns an array of roles with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Role::listNames()
     *
     * @param bool $forceUpdate to force the update of the roles var
     *
     * @return array list of roles (id => name)
     */
    public function listing($forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        if (empty($this->roles) || $forceUpdate) {
            $this->roles = $this->list();
        }
        $ret = [];
        foreach ($this->roles['roles'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Returns the list of permissions for a given role (Redmine v2.2.0).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Roles#GET-2
     *
     * @param int $id the role id
     *
     * @return array|false|string information about the role as array or false|string on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/roles/' . urlencode(strval($id)) . '.json',
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
}
