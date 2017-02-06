<?php

namespace Redmine\Api;

/**
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_News
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class News extends AbstractApi
{
    private $news = [];

    /**
     * List news (if no $project is given, it will return ALL the news).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param string|int $project project id or literal identifier [optional]
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of news found
     */
    public function all($project = null, array $params = [])
    {
        $path = null === $project ? '/news.json' : '/projects/'.$project.'/news.json';
        $this->news = $this->retrieveAll($path, $params);

        return $this->news;
    }
}
