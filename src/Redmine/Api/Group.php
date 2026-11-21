<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
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
final class Group extends AbstractApi
{
    use RequestParamsTrait;
    use ResponseValidationTrait;

    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var null|array<int,string>
     */
    private ?array $groupNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List groups.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of groups found
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
     * Returns an array of all groups with id/name pairs.
     *
     * @return array<int,string> list of groups (id => name)
     */
    final public function listNames(): array
    {
        if ($this->groupNames !== null) {
            return $this->groupNames;
        }

        $names = [];

        foreach ($this->list()['groups'] as $group) {
            $names[(int) $group['id']] = $group['name'];
        }

        $this->groupNames = $names;

        return $this->groupNames;
    }

    /**
     * Create a new group with a group of users assigned.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST
     *
     * @param array<mixed> $params the new group data
     *
     * @throws MissingParameterException Missing mandatory parameters
     */
    public function create(array $params = []): string|\SimpleXMLElement
    {
        $params = $this->buildRequestParams([], $params);

        if (
            !isset($params['name'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `name`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/groups.xml',
            XmlSerializer::createFromArray(['group' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

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
     * @param int          $id     the group id
     * @param array<mixed> $params
     *
     * @return string empty string
     */
    public function update(int $id, array $params = []): string
    {
        $params = $this->buildRequestParams([], $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/groups/' . $id . '.xml',
            XmlSerializer::createFromArray(['group' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Returns details of a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#GET-2
     * available $params :
     * - include: a coma separated list of associations to include in the response: users,memberships
     *
     * @param string|int $id the group id
     * @param array<mixed> $params params to pass to url
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the group
     */
    public function show($id, array $params = []): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create('/groups/' . urlencode(strval($id)) . '.json', $params)->getPath(),
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Delete a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#DELETE
     *
     * @param int $id id of the group
     *
     * @return string empty string on success
     */
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/groups/' . strval($id) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Adds an existing user to a group.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Groups#POST-2
     *
     * @param int $id     id of the group
     * @param int $userId id of the user
     */
    public function addUser($id, $userId): string|\SimpleXMLElement
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/groups/' . strval($id) . '/users.xml',
            XmlSerializer::createFromArray(['user_id' => $userId])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

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
     * @return string empty string on success
     */
    public function removeUser($id, $userId): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/groups/' . strval($id) . '/users/' . strval($userId) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }
}
