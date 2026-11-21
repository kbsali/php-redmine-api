<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing time entries, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class TimeEntry extends AbstractApi
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
     * List time entries.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of time entries found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/time_entries.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Get extended information about a time entry (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id the time entry id
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the time entry
     */
    public function show($id): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/time_entries/' . urlencode(strval($id)) . '.json',
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new time entry given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array<mixed> $params the new time entry data
     *
     * @throws MissingParameterException Missing mandatory parameters
     */
    public function create(array $params = []): \SimpleXMLElement|string
    {
        $params = $this->buildRequestParams([], $params);

        if (
            (!isset($params['issue_id']) && !isset($params['project_id']))
         || !isset($params['hours'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/time_entries.xml',
            XmlSerializer::createFromArray(['time_entry' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        if ('' !== $body) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Update time entry's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int          $id
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
            '/time_entries/' . $id . '.xml',
            XmlSerializer::createFromArray(['time_entry' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Delete a time entry.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id id of the time entry
     *
     * @return string empty string on success
     */
    public function remove($id): string
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/time_entries/' . $id . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }
}
