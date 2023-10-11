<?php

namespace Redmine\Api;

use Redmine\Exception\InvalidParameterException;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;

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
     * List wiki pages of a given project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_WikiPages#Getting-the-pages-list-of-a-wiki
     *
     * @param int|string $projectIdentifier project id or slug
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of wiki pages found for the given project
     */
    final public function listByProject($projectIdentifier, array $params = []): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__
            ));
        }

        $this->wikiPages = $this->retrieveData('/projects/'.strval($projectIdentifier).'/wiki/index.json', $params);

        return $this->wikiPages;
    }

    /**
     * List wiki pages of given $project.
     *
     * @deprecated since v2.4.0, use listByProject() instead.
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
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::listByProject()` instead.', E_USER_DEPRECATED);

        return $this->listByProject(strval($project), $params);
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
        $params = [
            'include' => 'attachments',
        ];

        if (null === $version) {
            $path = '/projects/'.$project.'/wiki/'.$page.'.json';
        } else {
            $path = '/projects/'.$project.'/wiki/'.$page.'/'.$version.'.json';
        }

        return $this->get(
            PathSerializer::create($path, $params)->getPath()
        );
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

        return $this->put(
            '/projects/'.$project.'/wiki/'.$page.'.xml',
            XmlSerializer::createFromArray(['wiki_page' => $params])->getEncoded()
        );
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
