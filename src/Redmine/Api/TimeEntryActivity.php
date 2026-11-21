<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * Listing time entry activities.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationstime_entry_activitiesformat
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class TimeEntryActivity extends AbstractApi
{
    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var null|array<string>
     */
    private ?array $timeEntryActivityNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List time entry activities.
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of time entry activities found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/enumerations/time_entry_activities.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all time entry activities with id/name pairs.
     *
     * @return array<int,string> list of time entry activities (id => name)
     */
    final public function listNames(): array
    {
        if ($this->timeEntryActivityNames !== null) {
            return $this->timeEntryActivityNames;
        }

        $names = [];
        $list = $this->list();

        if (array_key_exists('time_entry_activities', $list)) {
            foreach ($list['time_entry_activities'] as $activity) {
                $names[(int) $activity['id']] = $activity['name'];
            }
        }

        $this->timeEntryActivityNames = $names;

        return $this->timeEntryActivityNames;
    }

}
