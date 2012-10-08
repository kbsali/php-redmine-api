<?php

namespace Redmine\Api;

/**
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_News
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class News extends AbstractApi
{
    private $news = array();

    /**
     * List news (if no $project is given, it will return ALL the news)
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_News#GET
     *
     * @param  string|int $project project id or literal identifier [optional]
     * @return array      list of news found
     */
    public function all($project = null)
    {
        if (null === $project) {
            $this->news = $this->get('/news.json');
        } else {
            $this->news = $this->get('/projects/'.$project.'/news.json');
        }

        return $this->news;
    }

}
