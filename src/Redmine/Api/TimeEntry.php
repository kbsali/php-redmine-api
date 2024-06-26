<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing time entries, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class TimeEntry extends AbstractApi
{
    private $timeEntries = [];

    /**
     * List time entries.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of time entries found
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
     * List time entries.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see TimeEntry::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of time entries found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->timeEntries = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->timeEntries;
    }

    /**
     * Get extended information about a time entry (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id the time entry id
     *
     * @return array|false|string information about the time entry as array or false|string on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/time_entries/' . urlencode(strval($id)) . '.json',
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
     * Create a new time entry given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params the new time entry data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return SimpleXMLElement|string
     */
    public function create(array $params = [])
    {
        $defaults = [
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

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
     * @param int $id
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/time_entries/' . $id . '.xml',
            XmlSerializer::createFromArray(['time_entry' => $params])->getEncoded(),
        ));

        return $this->lastResponse->getContent();
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
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/time_entries/' . $id . '.xml',
        ));

        return $this->lastResponse->getContent();
    }
}
