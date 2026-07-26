<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;

/**
 * Handling issue relations.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssueRelation extends AbstractApi
{
    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see IssueRelation::fromHttpClient()
     *
     * @param Client|HttpClient $client
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
     * List relations of the given $issueId.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET
     *
     * @param int          $issueId the issue id
     * @param array<mixed> $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array<mixed> list of relations found
     */
    final public function listByIssueId(int $issueId, array $params = []): array
    {
        try {
            return $this->retrieveData('/issues/' . strval($issueId) . '/relations.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * List relations of the given issue.
     *
     * @deprecated v2.4.0 Use listByIssueId() instead.
     * @see IssueRelation::listByIssueId()
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET
     *
     * @param int          $issueId the issue id
     * @param array<mixed> $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<mixed>|string|false list of relations found or error message or false
     */
    public function all($issueId, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . self::class . '::listByIssueId()` instead.', E_USER_DEPRECATED);

        try {
            $relations = $this->listByIssueId($issueId, $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() instanceof \Throwable) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $relations;
    }

    /**
     * Get extended information about the given relation $id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET-2
     *
     * @param int $id the relation id
     *
     * @return array<mixed> relation's details or empty array on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/relations/' . urlencode(strval($id)) . '.json',
        ));

        $body = $this->lastResponse->getContent();


        if ('' === $body) {
            return [];
        }

        try {
            $data = JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            return [];
        }

        if (! is_array($data) || ! array_key_exists('relation', $data) || ! is_array($data['relation'])) {
            return [];
        }

        return $data['relation'];
    }

    /**
     * Delete a relation.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#DELETE
     *
     * @param int $id the relation id
     *
     * @return string empty string on success
     */
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/relations/' . $id . '.xml',
        ));

        $body = $this->lastResponse->getContent();
        $statusCode = $this->lastResponse->getStatusCode();

        if ($statusCode !== 200 && $statusCode !== 204) {
            if (!Future::isForwardCompatibilityEnabled()) {
                return $body;
            }

            throw UnexpectedResponseException::create($this->lastResponse);
        }

        return $body;
    }

    /**
     * Create a new issue relation.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#POST
     * available $params:
     * - issue_to_id (required): the id of the related issue
     * - relation_type (required to explicit : default "relates"): the type of relation
     *   (in: "relates", "duplicates", "duplicated", "blocks", "blocked", "precedes", "follows", "copied_to", "copied_from")
     * - delay (optional): the delay for a "precedes" or "follows" relation
     *
     * @param int          $issueId the ID of the issue we are creating the relation on
     * @param array<mixed> $params  the new issue relation data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return array<mixed>
     */
    public function create($issueId, array $params = [])
    {
        $defaults = [
            'relation_type' => 'relates',
            'issue_to_id' => null,
            'delay' => null,
        ];

        $params = $this->sanitizeParams($defaults, $params);

        if (!isset($params['issue_to_id'])) {
            throw new MissingParameterException('Theses parameters are mandatory: `issue_to_id`');
        }

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'POST',
            '/issues/' . urlencode(strval($issueId)) . '/relations.json',
            JsonSerializer::createFromArray(['relation' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();
        $statusCode = $this->lastResponse->getStatusCode();

        if ($statusCode !== 201) {
            if (!Future::isForwardCompatibilityEnabled()) {
                return JsonSerializer::createFromString($body)->getNormalized();
            }

            throw UnexpectedResponseException::create($this->lastResponse);
        }

        return JsonSerializer::createFromString($body)->getNormalized();
    }
}
