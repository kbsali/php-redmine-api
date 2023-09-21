<?php

namespace Redmine\Api;

use Redmine\Exception\MissingParameterException;
use Redmine\Serializer\XmlSerializer;

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
        $this->memberships = $this->retrieveData('/projects/'.$project.'/memberships.json', $params);

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
     * @throws MissingParameterException Missing mandatory parameters
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
            throw new MissingParameterException('Theses parameters are mandatory: `user_id`, `role_ids`');
        }

        return $this->post(
            '/projects/'.$project.'/memberships.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded()
        );
    }

    /**
     * Update membership information's by id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#PUT
     *
     * @param int   $id     id of the membership
     * @param array $params the new membership data
     *
     * @throws MissingParameterException Missing mandatory parameters
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
            throw new MissingParameterException('Missing mandatory parameters');
        }

        return $this->put(
            '/memberships/'.$id.'.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded()
        );
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
     * Delete membership of project by user id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#DELETE
     *
     * @param int   $projectId id of project
     * @param int   $userId    id of user
     * @param array $params    optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return false|\SimpleXMLElement|string
     */
    public function removeMember($projectId, $userId, array $params = [])
    {
        $memberships = $this->all($projectId, $params);
        if (!isset($memberships['memberships']) || !is_array($memberships['memberships'])) {
            return false;
        }
        $removed = false;
        foreach ($memberships['memberships'] as $membership) {
            if (isset($membership['id']) && isset($membership['user']['id']) && $membership['user']['id'] === $userId) {
                $removed = $this->remove($membership['id']);
            }
        }

        return $removed;
    }
}
