<?php

namespace Redmine\Api;

use Redmine\Exception;

/**
 * Listing issue priorities.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationsissue_prioritiesformat
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssuePriority extends AbstractApi
{
    private $issuePriorities = [];

    /**
     * List issue priorities.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationsissue_prioritiesformat
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of issue priorities found
     */
    final public function list(array $params = []): array
    {
        $this->issuePriorities = $this->retrieveData('/enumerations/issue_priorities.json', $params);

        return $this->issuePriorities;
    }

    /**
     * List issue priorities.
     *
     * @deprecated since v2.4.0, use list() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationsissue_prioritiesformat
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of issue priorities found
     */
    public function all(array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::list()` instead.', E_USER_DEPRECATED);

        try {
            return $this->list($params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            return $e->getMessage();
        }
    }
}
