<?php

declare(strict_types=1);

namespace Redmine\Api;

use InvalidArgumentException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing projects, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Projects
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Project extends AbstractApi
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
    private ?array $projectNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List projects.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of projects found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/projects.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all projects with id/name pairs.
     *
     * @return array<int,string> list of projects (id => name)
     */
    final public function listNames(): array
    {
        if ($this->projectNames !== null) {
            return $this->projectNames;
        }

        $names = [];

        $limit = 100;
        $offset = 0;

        do {
            $list = $this->list([
                'limit' => $limit,
                'offset' => $offset,
            ]);

            $listCount = 0;
            $offset += $limit;

            if (array_key_exists('projects', $list)) {
                $listCount = count($list['projects']);

                foreach ($list['projects'] as $issueStatus) {
                    $names[(int) $issueStatus['id']] = (string) $issueStatus['name'];
                }
            }
        } while ($listCount === $limit);

        $this->projectNames = $names;

        return $this->projectNames;
    }

    /**
     * Get extended information about a project (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects#Showing-a-project
     *
     * @param string|int   $id     project id or identifier
     * @param array<mixed> $params available parameters:
     *                             include: fetch associated data (optional). Possible values: trackers, issue_categories, enabled_modules (since 2.6.0)
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the project
     */
    public function show($id, array $params = []): array
    {
        if (isset($params['include']) && is_array($params['include'])) {
            $params['include'] = implode(',', $params['include']);
        } else {
            $params['include'] = 'trackers,issue_categories,attachments,relations';
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create('/projects/' . urlencode(strval($id)) . '.json', $params)->getPath(),
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param array<mixed> $params the new project data
     *
     * @throws MissingParameterException
     */
    public function create(array $params = []): \SimpleXMLElement|string
    {
        $params = $this->buildRequestParams([], $params);

        if (
            !isset($params['name'])
         || !isset($params['identifier'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `name`, `identifier`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects.xml',
            XmlSerializer::createFromArray(['project' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        if ('' !== $body) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Update project's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param string|int $id project id or identifier
     * @param array<mixed> $params
     */
    public function update($id, array $params): string
    {
        $defaults = [
            'id' => $id,
        ];
        $params = $this->buildRequestParams($defaults, $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . urlencode(strval($id)) . '.xml',
            XmlSerializer::createFromArray(['project' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Close a project.
     *
     * @see https://www.redmine.org/issues/35507
     *
     * @param string|int $projectIdentifier project id or identifier
     *
     * @throws InvalidArgumentException if $projectIdentifier is not provided as int or string
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function close($projectIdentifier): bool
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . strval($projectIdentifier) . '/close.xml',
            '',
        ));

        $this->throwOnUnexpectedStatusCode(204);

        return true;
    }

    /**
     * Reopen a project.
     *
     * @see https://www.redmine.org/issues/35507
     *
     * @param string|int $projectIdentifier project id or identifier
     *
     * @throws InvalidArgumentException if $projectIdentifier is not provided as int or string
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function reopen($projectIdentifier): bool
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . strval($projectIdentifier) . '/reopen.xml',
            '',
        ));

        $this->throwOnUnexpectedStatusCode(204);

        return true;
    }

    /**
     * Archive a project.
     *
     * @see https://www.redmine.org/issues/35420
     *
     * @param string|int $projectIdentifier project id or identifier
     *
     * @throws InvalidArgumentException if $projectIdentifier is not provided as int or string
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function archive($projectIdentifier): bool
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . strval($projectIdentifier) . '/archive.xml',
            '',
        ));

        $this->throwOnUnexpectedStatusCode(204);

        return true;
    }

    /**
     * Unarchive a project.
     *
     * @see https://www.redmine.org/issues/35420
     *
     * @param string|int $projectIdentifier project id or identifier
     *
     * @throws InvalidArgumentException if $projectIdentifier is not provided as int or string
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return true if the request was successful
     */
    final public function unarchive($projectIdentifier): bool
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . strval($projectIdentifier) . '/unarchive.xml',
            '',
        ));

        $this->throwOnUnexpectedStatusCode(204);

        return true;
    }

    /**
     * Delete a project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Projects
     *
     * @param int $id id of the project
     *
     * @return string empty string on success
     */
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/projects/' . $id . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

}
