<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
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
final class Membership extends AbstractApi
{
    use RequestParamsTrait;
    use ResponseValidationTrait;

    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List memberships for a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#GET
     *
     * @param string|int   $projectIdentifier project id or literal identifier
     * @param array<mixed> $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of memberships found
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
     * Create a new membership for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#POST
     *
     * @param string|int   $project project id or literal identifier
     * @param array<mixed> $params  the new membership data
     *
     * @throws MissingParameterException Missing mandatory parameters
     */
    public function create($project, array $params = []): \SimpleXMLElement|string
    {
        $params = $this->buildRequestParams([], $params);

        if (!isset($params['user_id']) || !isset($params['role_ids'])) {
            throw new MissingParameterException('Theses parameters are mandatory: `user_id`, `role_ids`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects/' . $project . '/memberships.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

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
     * @param int          $id     id of the membership
     * @param array<mixed> $params the new membership data
     *
     * @throws MissingParameterException Missing mandatory parameters
     */
    public function update($id, array $params = []): string
    {
        $params = $this->buildRequestParams([], $params);

        if (!isset($params['role_ids'])) {
            throw new MissingParameterException('Theses parameters are mandatory: `role_ids`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/memberships/' . $id . '.xml',
            XmlSerializer::createFromArray(['membership' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
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
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/memberships/' . $id . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Delete membership of project by user id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Memberships#DELETE
     *
     * @param int          $projectId id of project
     * @param int          $userId    id of user
     * @param array<mixed> $params    optional parameters to be passed to the api (offset, limit, ...)
     */
    public function removeMember($projectId, $userId, array $params = []): false|string
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
