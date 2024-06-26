<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
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
    private $relations = [];

    /**
     * List relations of the given $issueId.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET
     *
     * @param int   $issueId the issue id
     * @param array $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of relations found
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
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET
     *
     * @param int   $issueId the issue id
     * @param array $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of relations found or error message or false
     */
    public function all($issueId, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByIssueId()` instead.', E_USER_DEPRECATED);

        try {
            $this->relations = $this->listByIssueId($issueId, $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->relations;
    }

    /**
     * Get extended information about the given relation $id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET-2
     *
     * @param int $id the relation id
     *
     * @return array relation's details or empty array on error
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

        return $this->lastResponse->getContent();
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
     * @param int   $issueId the ID of the issue we are creating the relation on
     * @param array $params  the new issue relation data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return array
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

        return JsonSerializer::createFromString($body)->getNormalized();
    }
}
