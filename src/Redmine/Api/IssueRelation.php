<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
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
            $this->relations = $this->retrieveData('/issues/'.strval($issueId).'/relations.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }

        return $this->relations;
    }

    /**
     * List relations of the given issue.
     *
     * @deprecated since v2.4.0, use listByIssueId() instead.
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
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::listByIssueId()` instead.', E_USER_DEPRECATED);

        try {
            return $this->listByIssueId($issueId, $params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }
    }

    /**
     * Get extended information about the given relation $id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET-2
     *
     * @param int $id the relation id
     *
     * @return array relation's details
     */
    public function show($id)
    {
        $ret = $this->get('/relations/'.urlencode($id).'.json');
        if (false === $ret) {
            return [];
        }

        return $ret['relation'];
    }

    /**
     * Delete a relation.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#DELETE
     *
     * @param int $id the relation id
     *
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/relations/'.$id.'.xml');
    }

    /**
     * Create a new issue relation.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#POST
     *
     * @param int   $issueId the ID of the issue we are creating the relation on
     * @param array $params  the new issue relation data
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

        $response = $this->post(
            '/issues/'.urlencode($issueId).'/relations.json',
            JsonSerializer::createFromArray(['relation' => $params])->getEncoded()
        );

        return JsonSerializer::createFromString($response)->getNormalized();
    }
}
