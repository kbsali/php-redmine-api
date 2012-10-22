<?php

namespace Redmine\Api;

/**
 * Handling issue relations
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssueRelation extends AbstractApi
{
    private $relations = array();

    /**
     * List relations of the given $issueId
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET
     *
     * @param  int   $issueId the issue id
     * @return array list of relations found
     */
    public function all($issueId)
    {
        $this->relations = $this->get('/issues/'.urlencode($issueId).'/relations.json');

        return $this->relations;
    }

    /**
     * Get extended information about the given relation $id
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#GET-2
     *
     * @param  int   $id the relation id
     * @return array relation's details
     */
    public function show($id)
    {
        $ret = $this->get('/relations/'.urlencode($id).'.json');
        if (null === $ret) {
            return array();
        }

        return $ret['relation'];
    }

    /**
     * Delete a relation
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_IssueRelations#DELETE
     *
     * @param  int     $id the relation id
     * @return boolean
     */
    public function remove($id)
    {
        return ' ' !== $this->delete('/relations/'.$id.'.xml');
    }
}
