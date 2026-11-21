<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing Wiki pages.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Wiki extends AbstractApi
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
     * List wiki pages of a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @param int|string   $projectIdentifier project id or slug
     * @param array<mixed> $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of wiki pages found for the given project
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
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/wiki/index.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Getting [an old version of] a wiki page.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-a-wiki-page
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-an-old-version-of-a-wiki-page
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param int        $version version of the page
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the wiki page
     */
    public function show($project, $page, $version = null): array
    {
        $params = [
            'include' => 'attachments',
        ];

        if (null === $version) {
            $path = '/projects/' . $project . '/wiki/' . rawurlencode($page) . '.json';
        } else {
            $path = '/projects/' . $project . '/wiki/' . rawurlencode($page) . '/' . $version . '.json';
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create($path, $params)->getPath(),
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new wiki page given an array of $params.
     *
     * @param int|string   $project the project name
     * @param string       $page    the page name
     * @param array<mixed> $params  the new wiki page data
     */
    public function create($project, $page, array $params = []): \SimpleXMLElement|string
    {
        $params = $this->buildRequestParams([], $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . $project . '/wiki/' . rawurlencode($page) . '.xml',
            XmlSerializer::createFromArray(['wiki_page' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201, 204);

        if ($body !== '') {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Updates wiki page $page.
     *
     * @param int|string   $project the project name
     * @param string       $page    the page name
     * @param array<mixed> $params  the new wiki page data
     */
    public function update($project, $page, array $params = []): string
    {
        $this->create($project, $page, $params);

        return $this->lastResponse->getContent();
    }

    /**
     * Delete a wiki page.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Deleting-a-wiki-page
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     *
     * @return string empty string on success
     */
    public function remove($project, $page): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/projects/' . $project . '/wiki/' . rawurlencode($page) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }
}
