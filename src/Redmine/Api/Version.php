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
 * Listing versions, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Versions
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Version extends AbstractApi
{
    use RequestParamsTrait;
    use ResponseValidationTrait;

    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var array<array<int,string>>
     */
    private array $versionNames = [];

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List versions of a project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET
     *
     * @param string|int   $projectIdentifier project id or literal identifier
     * @param array<mixed> $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<array<array<string>>> list of versions found
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
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/versions.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all versions by a project with id/name pairs.
     *
     * @param string|int $projectIdentifier project id or literal identifier
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<int,string> list of version names (id => name)
     */
    final public function listNamesByProject($projectIdentifier): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        if (array_key_exists($projectIdentifier, $this->versionNames)) {
            return $this->versionNames[$projectIdentifier];
        }

        $names = [];

        $list = $this->listByProject($projectIdentifier);

        if (array_key_exists('versions', $list)) {
            foreach ($list['versions'] as $version) {
                $names[(int) $version['id']] = $version['name'];
            }
        }

        $this->versionNames[$projectIdentifier] = $names;

        return $this->versionNames[$projectIdentifier];
    }

    /**
     * Get extended information about a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET-2
     *
     * @param int $id the version id
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the version
     */
    public function show($id): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/versions/' . urlencode(strval($id)) . '.json',
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new version for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#POST
     *
     * @param string|int   $project project id or literal identifier
     * @param array<mixed> $params  the new version data
     *
     * @throws MissingParameterException Missing mandatory parameters
     */
    public function create($project, array $params = []): \SimpleXMLElement|string
    {
        $params = $this->buildRequestParams([], $params);

        if (
            !isset($params['name'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `name`');
        }
        $this->validateStatus($params);
        $this->validateSharing($params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects/' . $project . '/versions.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        if ('' !== $body) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Update version's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#PUT
     *
     * @param int          $id the version id
     * @param array<mixed> $params
     */
    public function update($id, array $params): string
    {
        $params = $this->buildRequestParams([], $params);
        $this->validateStatus($params);
        $this->validateSharing($params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/versions/' . $id . '.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * @param array<mixed> $params
     *
     * @throws InvalidParameterException
     */
    private function validateStatus(array $params = []): void
    {
        $arrStatus = [
            'open',
            'locked',
            'closed',
        ];

        if (isset($params['status']) && !in_array($params['status'], $arrStatus)) {
            throw new InvalidParameterException('Possible values for status are: ' . implode(', ', $arrStatus));
        }
    }

    /**
     * @param array<mixed> $params
     *
     * @throws InvalidParameterException
     */
    private function validateSharing(array $params = []): void
    {
        $arrSharing = [
            'none' => 'Not shared',
            'descendants' => 'With subprojects',
            'hierarchy' => 'With project hierarchy',
            'tree' => 'With project tree',
            'system' => 'With all projects',
        ];

        if (isset($params['sharing']) && !isset($arrSharing[$params['sharing']])) {
            throw new InvalidParameterException('Possible values for sharing are: ' . implode(', ', array_keys($arrSharing)));
        }
    }

    /**
     * Delete a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#DELETE
     *
     * @param int $id id of the version
     *
     * @return string empty string on success
     */
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/versions/' . $id . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

}
