<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

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
     * List memberships for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#GET
     *
     * @param string|int $projectIdentifier project id or literal identifier
     * @param array      $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of memberships found
     */
    final public function listByProject($projectIdentifier, array $params = []): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        try {
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/memberships.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * List memberships for a given $project.
     *
     * @deprecated v2.4.0 Use listByProject() instead.
     * @see Membership::listByProject()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of memberships found or error message or false
     */
    public function all($project, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            $this->memberships = $this->listByProject(strval($project), $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

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
     * @return SimpleXMLElement|string
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

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects/' . $project . '/memberships.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        if ('' !== $body) {
            return new SimpleXMLElement($body);
        }

        return $body;
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
            throw new MissingParameterException('Theses parameters are mandatory: `role_ids`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/memberships/' . $id . '.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded(),
        ));

        return $this->lastResponse->getContent();
    }

    /**
     * Delete a membership.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#DELETE
     *
     * @param int $id id of the membership
     *
     * @return string empty string on success
     */
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/memberships/' . $id . '.xml',
        ));

        return $this->lastResponse->getContent();
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
        $memberships = $this->listByProject($projectId, $params);
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
