<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
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
class Wiki extends AbstractApi
{
    private $wikiPages = [];

    /**
     * List wiki pages of a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @param int|string $projectIdentifier project id or slug
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of wiki pages found for the given project
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
     * List wiki pages of given $project.
     *
     * @deprecated v2.4.0 Use listByProject() instead.
     * @see Wiki::listByProject()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @param int|string $project project name
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of wiki pages found or error message or false
     */
    public function all($project, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            $this->wikiPages = $this->listByProject(strval($project), $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->wikiPages;
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
     * @return array|false|string information about the wiki page as array or false|string on error
     */
    public function show($project, $page, $version = null)
    {
        $params = [
            'include' => 'attachments',
        ];

        if (null === $version) {
            $path = '/projects/' . $project . '/wiki/' . urlencode($page) . '.json';
        } else {
            $path = '/projects/' . $project . '/wiki/' . urlencode($page) . '/' . $version . '.json';
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create($path, $params)->getPath(),
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
     * Create a new wiki page given an array of $params.
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param array      $params  the new wiki page data
     *
     * @return SimpleXMLElement|string|false
     */
    public function create($project, $page, array $params = [])
    {
        $defaults = [
            'text' => null,
            'comments' => null,
            'version' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/projects/' . $project . '/wiki/' . urlencode($page) . '.xml',
            XmlSerializer::createFromArray(['wiki_page' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        if ($body !== '') {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Updates wiki page $page.
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param array      $params  the new wiki page data
     *
     * @return string|false
     */
    public function update($project, $page, array $params = [])
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
    public function remove($project, $page)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/projects/' . $project . '/wiki/' . urlencode($page) . '.xml',
        ));

        return $this->lastResponse->getContent();
    }
}
