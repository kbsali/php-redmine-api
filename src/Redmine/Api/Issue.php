<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Listing issues, searching, editing and closing your projects issues.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Issues
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Issue extends AbstractApi
{
    use RequestParamsTrait;
    use ResponseValidationTrait;

    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    private ?IssueCategory $issueCategoryApi = null;

    private ?IssueStatus $issueStatusApi = null;

    private ?Project $projectApi = null;

    private ?Tracker $trackerApi = null;

    private ?User $userApi = null;

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see Issue::fromHttpClient()
     *
     * @param HttpClient $client
     */
    public function __construct($client/*, bool $privatelyCalled = false*/)
    {
        $privatelyCalled = (func_num_args() > 1) ? func_get_arg(1) : false;

        if ($privatelyCalled === true) {
            parent::__construct($client);

            return;
        }

        if (static::class !== self::class) {
            $className = (new \ReflectionClass($this))->isAnonymous() ? '' : ' in `' . static::class . '`';
            @trigger_error('Class `' . self::class . '` will declared as final in v3.0.0, stop extending it' . $className . '.', E_USER_DEPRECATED);
        } else {
            @trigger_error('Method `' . __METHOD__ . '()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `' . self::class . '::fromHttpClient()` instead.', E_USER_DEPRECATED);
        }

        parent::__construct($client);
    }

    /**
     * List issues.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues
     * available $params :
     * - offset: skip this number of issues in response (optional)
     * - limit: number of issues per page (optional)
     * - sort: column to sort with. Append :desc to invert the order.
     * - project_id: get issues from the project with the given id, where id is either project id or project identifier
     * - tracker_id: get issues from the tracker with the given id
     * - status_id: get issues with the given status id only. Possible values: open, closed, * to get open and closed issues, status id
     * - assigned_to_id: get issues which are assigned to the given user id
     * - cf_x: get issues with the given value for custom field with an ID of x. (Custom field must have 'used as a filter' checked.)
     * - query_id : id of the previously saved query
     *
     * @param array<mixed> $params the additional parameters (cf available $params above)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of issues found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/issues.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Get extended information about an issue gitven its id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Using-JSON
     * available $params :
     * include: fetch associated data (optional). Possible values: children, attachments, relations, changesets and journals
     *
     * @param int          $id     the issue id
     * @param array<mixed> $params extra associated data
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the issue
     */
    public function show($id, array $params = [])
    {
        if (isset($params['include']) && is_array($params['include'])) {
            $params['include'] = implode(',', $params['include']);
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            PathSerializer::create('/issues/' . urlencode(strval($id)) . '.json', $params)->getPath(),
        ));

        return $this->getJsonArrayOrThrow();
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param array<mixed> $params the new issue data
     *
     * @return string|SimpleXMLElement
     */
    public function create(array $params = [])
    {
        $params = $this->cleanParams($params);
        $params = $this->buildRequestParams([], $params);

        // FIXME: Throw exception on missing mandatory parameters
        // if (!isset($params['subject']) || !isset($params['project_id']) || !isset($params['tracker_id']) || !isset($params['priority_id']) || !isset($params['status_id'])) {
        //     throw new MissingParameterException('Theses parameters are mandatory: `subject`, `project_id|project`, `tracker_id|tracker`, `priority_id|priority`, `status_id|status`');
        // }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/issues.xml',
            XmlSerializer::createFromArray(['issue' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(201);

        if ($body === '') {
            return $body;
        }

        return new SimpleXMLElement($body);
    }

    /**
     * Update issue information's by username, repo and issue number. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * `Param::Clear` unassigns the user (`assigned_to_id`) or the version (`fixed_version_id`), `null` values are not sent.
     * Use `Param::Clear` to clear other fields.
     * An empty string also unassigns the version. Redmine ignores an empty `assigned_to_id` in XML requests,
     * so an empty string also unassigns the user.
     *
     * @param int          $id     the issue number
     * @param array<mixed> $params
     *
     * @return string|SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
        ];
        $params = $this->cleanParams($params, $id);

        // Redmine ignores an empty `assigned_to_id` in XML requests, only JSON requests can unassign an issue
        // @see https://www.redmine.org/issues/33744
        $unassignUserWithJson = array_key_exists('assigned_to_id', $params)
            && (Param::Clear === $params['assigned_to_id'] || '' === $params['assigned_to_id']);

        // An empty string is still allowed to unassign a user or a version
        foreach (['assigned_to_id', 'fixed_version_id'] as $name) {
            if (array_key_exists($name, $params) && '' === $params[$name]) {
                $params[$name] = Param::Clear;
            }
        }

        $sanitizedParams = $this->buildRequestParams($defaults, $params);

        if ($unassignUserWithJson) {
            $request = HttpFactory::makeJsonRequest(
                'PUT',
                '/issues/' . urlencode(strval($id)) . '.json',
                JsonSerializer::createFromArray(['issue' => $sanitizedParams])->getEncoded(),
            );
        } else {
            $request = HttpFactory::makeXmlRequest(
                'PUT',
                '/issues/' . urlencode(strval($id)) . '.xml',
                XmlSerializer::createFromArray(['issue' => $sanitizedParams])->getEncoded(),
            );
        }

        $this->lastResponse = $this->getHttpClient()->request($request);

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * @param int $id
     * @param int $watcherUserId
     *
     * @return SimpleXMLElement|string
     */
    public function addWatcher($id, $watcherUserId)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/issues/' . urlencode(strval($id)) . '/watchers.xml',
            XmlSerializer::createFromArray(['user_id' => urlencode(strval($watcherUserId))])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        if ($body === '') {
            return $body;
        }

        return new SimpleXMLElement($body);
    }

    /**
     * @param int $id
     * @param int $watcherUserId
     *
     * @return string empty string on success
     */
    public function removeWatcher($id, $watcherUserId)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/issues/' . urlencode(strval($id)) . '/watchers/' . urlencode(strval($watcherUserId)) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * @param int    $id
     * @param string $status
     *
     * @return string|SimpleXMLElement
     */
    public function setIssueStatus($id, $status)
    {
        $issueStatusApi = $this->getIssueStatusApi();

        return $this->update($id, [
            'status_id' => $this->lookupId('status', $status, $issueStatusApi->listNames()),
        ]);
    }

    /**
     * @param int    $id
     * @param string $note
     * @param bool   $privateNote
     *
     * @return string|SimpleXMLElement
     */
    public function addNoteToIssue($id, $note, $privateNote = false)
    {
        return $this->update($id, [
            'notes' => $note,
            'private_notes' => $privateNote ?: null,
        ]);
    }

    /**
     * Resolves the id of a literal identifier.
     *
     * @param mixed                   $value the literal identifier given by the caller
     * @param array<int|string,mixed> $names the known names indexed by their id
     *
     * @throws InvalidParameterException if the name is unknown
     *
     * @return int|string the id
     */
    private function lookupId(string $param, $value, array $names): int|string
    {
        $id = array_search($value, $names, true);

        if (false === $id) {
            throw new InvalidParameterException(sprintf(
                'The value "%s" of the parameter "%s" is unknown.',
                is_scalar($value) ? strval($value) : get_debug_type($value),
                $param,
            ));
        }

        return $id;
    }

    /**
     * Transforms literal identifiers to integer ids.
     *
     * @param array<mixed> $params
     * @param int|string|null $issueId the id of the issue to update, its project is used to look up a category name if the params contain no project
     *
     * @return array<mixed>
     */
    private function cleanParams(array $params = [], $issueId = null): array
    {
        // Clearing a field does not need a lookup of the name
        foreach (['project', 'category', 'status', 'tracker', 'assigned_to', 'author'] as $name) {
            if (array_key_exists($name, $params) && Param::Clear === $params[$name]) {
                $params[$name . '_id'] = Param::Clear;
                unset($params[$name]);
            }
        }

        if (isset($params['project'])) {
            $projectApi = $this->getProjectApi();

            // TODO: project names are not unique; there could be collisions
            $params['project_id'] = $this->lookupId(
                'project',
                $params['project'],
                $projectApi->listNames(),
            );
            unset($params['project']);
        }

        $projectIdOfIssue = false;

        if (isset($params['category']) && ! isset($params['project_id']) && null !== $issueId) {
            $issue = $this->show($issueId);

            if (is_array($issue) && isset($issue['issue']['project']['id'])) {
                $params['project_id'] = $issue['issue']['project']['id'];
                $projectIdOfIssue = true;
            }
        }

        if (isset($params['category']) && isset($params['project_id'])) {
            $issueCategoryApi = $this->getIssueCategoryApi();

            $params['category_id'] = $this->lookupId(
                'category',
                $params['category'],
                $issueCategoryApi->listNamesByProject($params['project_id']),
            );
            unset($params['category']);

            // The project of the issue is only needed for the lookup, the project is not changed
            if ($projectIdOfIssue) {
                unset($params['project_id']);
            }
        }

        if (isset($params['status'])) {
            $issueStatusApi = $this->getIssueStatusApi();

            $params['status_id'] = $this->lookupId(
                'status',
                $params['status'],
                $issueStatusApi->listNames(),
            );
            unset($params['status']);
        }

        if (isset($params['tracker'])) {
            $trackerApi = $this->getTrackerApi();

            $params['tracker_id'] = $this->lookupId(
                'tracker',
                $params['tracker'],
                $trackerApi->listNames(),
            );
            unset($params['tracker']);
        }

        if (isset($params['assigned_to'])) {
            $userApi = $this->getUserApi();

            $params['assigned_to_id'] = $this->lookupId(
                'assigned_to',
                $params['assigned_to'],
                $userApi->listLogins(),
            );
            unset($params['assigned_to']);
        }

        if (isset($params['author'])) {
            $userApi = $this->getUserApi();

            $params['author_id'] = $this->lookupId(
                'author',
                $params['author'],
                $userApi->listLogins(),
            );
            unset($params['author']);
        }

        return $params;
    }

    /**
     * Attach a file to an issue. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param int          $id         the issue number
     * @param array<mixed> $attachment ['token' => '...', 'filename' => '...', 'content_type' => '...']
     *
     * @return bool|string
     */
    public function attach($id, array $attachment)
    {
        return $this->attachMany($id, [$attachment]);
    }

    /**
     * Attach files to an issue. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param int          $id          the issue number
     * @param array<mixed> $attachments [
     *                                  ['token' => '...', 'filename' => '...', 'content_type' => '...'],
     *                                  ['token' => '...', 'filename' => '...', 'content_type' => '...']
     *                                  ]
     *
     * @return bool|string
     */
    public function attachMany($id, array $attachments)
    {
        $params = [
            'id' => $id,
            'uploads' => $attachments,
        ];

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'PUT',
            '/issues/' . urlencode(strval($id)) . '.json',
            JsonSerializer::createFromArray(['issue' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    /**
     * Remove a issue by issue number.
     *
     * @param int $id the issue number
     *
     * @return string empty string on success
     */
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/issues/' . urlencode(strval($id)) . '.xml',
        ));

        $body = $this->lastResponse->getContent();

        $this->throwOnUnexpectedStatusCode(204);

        return $body;
    }

    private function getIssueCategoryApi(): IssueCategory
    {
        $this->issueCategoryApi ??= IssueCategory::fromHttpClient($this->getHttpClient());

        $this->passFutureModeTo($this->issueCategoryApi);

        return $this->issueCategoryApi;
    }

    private function getIssueStatusApi(): IssueStatus
    {
        $this->issueStatusApi ??= IssueStatus::fromHttpClient($this->getHttpClient());

        $this->passFutureModeTo($this->issueStatusApi);

        return $this->issueStatusApi;
    }

    /**
     * Internal APIs created from an HTTP client do not know the future mode of this instance.
     */
    private function passFutureModeTo(AbstractApi $api): void
    {
        if (!$this->isFutureModeEnabled()) {
            return;
        }

        // Set the private property without adding a method to AbstractApi
        \Closure::bind(static function (AbstractApi $api): void {
            $api->futureMode = true;
        }, null, AbstractApi::class)($api);
    }

    private function getProjectApi(): Project
    {
        $this->projectApi ??= Project::fromHttpClient($this->getHttpClient());

        $this->passFutureModeTo($this->projectApi);

        return $this->projectApi;
    }

    private function getTrackerApi(): Tracker
    {
        $this->trackerApi ??= Tracker::fromHttpClient($this->getHttpClient());

        $this->passFutureModeTo($this->trackerApi);

        return $this->trackerApi;
    }

    private function getUserApi(): User
    {
        $this->userApi ??= User::fromHttpClient($this->getHttpClient());

        $this->passFutureModeTo($this->userApi);

        return $this->userApi;
    }
}
