<?php

namespace Redmine\Api;

/**
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Queries
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Query extends AbstractApi
{
    private $query = array();

    /**
     * List available queries
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Queries#GET
     *
     * @return array list of queries found
     */
    public function all()
    {
        $this->query = $this->get('/queries.json');

        return $this->query;
    }

}
