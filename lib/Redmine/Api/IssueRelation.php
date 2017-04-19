<?php

namespace Redmine\Api;

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
     * @return array list of relations found
     */
    public function all($issueId, array $params = [])
    {
        $this->relations = $this->retrieveAll('/issues/'.urlencode($issueId).'/relations.json', $params);

        return $this->relations;
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
        if (null === $ret) {
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

        $params = json_encode(['relation' => $params]);

        return json_decode($this->post('/issues/'.urlencode($issueId).'/relations.json', $params), true);
    }
}
