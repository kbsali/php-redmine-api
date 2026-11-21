<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * Listing issue statuses.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class IssueStatus extends AbstractApi
{
    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var null|array<int,string>
     */
    private ?array $issueStatusNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List issue statuses.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of issue statuses found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/issue_statuses.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all issue statuses with id/name pairs.
     *
     * @return array<int,string> list of issue statuses (id => name)
     */
    final public function listNames(): array
    {
        if ($this->issueStatusNames !== null) {
            return $this->issueStatusNames;
        }

        $names = [];

        $list = $this->list();

        if (array_key_exists('issue_statuses', $list)) {
            foreach ($list['issue_statuses'] as $issueStatus) {
                $names[(int) $issueStatus['id']] = (string) $issueStatus['name'];
            }
        }

        $this->issueStatusNames = $names;

        return $this->issueStatusNames;
    }

}
