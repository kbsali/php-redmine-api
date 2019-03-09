<?php

namespace Redmine\Api;

/**
 * Listing Wiki pages.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Wiki extends AbstractApi
{
    private $wikiPages = [];

    /**
     * List wiki pages of given $project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @param int|string $project project name
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of wiki pages found for the given project
     */
    public function all($project, array $params = [])
    {
        $this->wikiPages = $this->retrieveAll('/projects/'.$project.'/wiki/index.json', $params);

        return $this->wikiPages;
    }

    /**
     * Getting [an old version of] a wiki page.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-a-wiki-page
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-an-old-version-of-a-wiki-page
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param int        $version version of the page
     *
     * @return array information about the wiki page
     */
    public function show($project, $page, $version = null)
    {
        $path = null === $version
            ? '/projects/'.$project.'/wiki/'.$page.'.json?include=attachments'
            : '/projects/'.$project.'/wiki/'.$page.'/'.$version.'.json?include=attachments';

        return $this->get($path);
    }

    /**
     * Create a new wiki page given an array of $params.
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param array      $params  the new wiki page data
     *
     * @return string|false
     */
    public function create($project, $page, array $params = [])
    {
        $defaults = [
            'text' => null,
            'comments' => null,
            'version' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><wiki_page></wiki_page>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/projects/'.$project.'/wiki/'.$page.'.xml', $xml->asXML());
    }

    /**
     * Updates wiki page $page.
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     * @param array      $params  the new wiki page data
     *
     * @return string|false
     */
    public function update($project, $page, array $params = [])
    {
        return $this->create($project, $page, $params);
    }

    /**
     * Delete a wiki page.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Deleting-a-wiki-page
     *
     * @param int|string $project the project name
     * @param string     $page    the page name
     *
     * @return string
     */
    public function remove($project, $page)
    {
        return $this->delete('/projects/'.$project.'/wiki/'.$page.'.xml');
    }
}
