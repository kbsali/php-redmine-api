<?php

namespace Redmine\Api;

/**
 * Listing Wiki pages
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Wiki extends AbstractApi
{
    private $wikiPages = array();

    /**
     * List wiki pages of given $project
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @return array list of wiki pages found for the given project
     */
    public function all($project)
    {
        $this->wikiPages = $this->get('/projects/'.$project.'/wiki/index.json');

        return $this->wikiPages;
    }

    /**
     *
     * Getting [an old version of ] a wiki page
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-a-wiki-page
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-an-old-version-of-a-wiki-page
     *
     * @param  int|string $project the project name
     * @param  string     $page    the page name
     * @param  int        $version version of the page
     * @return array      information about the issue
     */
    public function show($project, $page, $version = null)
    {
        $path = null === $version
            ? '/projects/'.$project.'/wiki/'.$page.'.json'
            : '/projects/'.$project.'/wiki/'.$page.'/'.$version.'.json';

        return $this->get($path);
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param  int|string        $project the project name
     * @param  string            $page    the page name
     * @param  array             $params  the new issue data
     * @return \SimpleXMLElement
     */
    public function create($project, $page, array $params = array())
    {
        $defaults = array(
            'text'     => null,
            'comments' => null,
            'version'  => null,
        );
        $params = $this->cleanParams($params);
        $params = array_filter(array_merge($defaults, $params));

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><wiki_page></wiki_page>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/projects/'.$project.'/wiki/'.$page.'.xml', $xml->asXML());
    }

    /**
     * Delete a wiki page
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Deleting-a-wiki-page
     *
     * @param  int|string $project the project name
     * @param  string     $page    the page name
     * @return string
     */
    public function remove($project, $page)
    {
        return $this->delete('/projects/'.$project.'/wiki/'.$page.'.xml');
    }
}
