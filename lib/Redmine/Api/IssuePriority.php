<?php

namespace Redmine\Api;

/**
 * Listing issue priorities
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationsissue_prioritiesformat
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssuePriority extends AbstractApi
{
    private $issuePriorities = array();

    /**
     * List issue priorities
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationsissue_prioritiesformat
     *
     * @return array list of issue priorities found
     */
    public function all()
    {
        $this->issuePriorities = $this->get('/enumerations/issue_priorities.json');

        return $this->issuePriorities;
    }
}
