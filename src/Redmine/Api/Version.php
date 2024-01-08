<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Serializer\XmlSerializer;

/**
 * Listing versions, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Versions
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Version extends AbstractApi
{
    private $versions = [];

    /**
     * List versions of a project.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET
     *
     * @param string|int $projectIdentifier project id or literal identifier
     * @param array      $params            optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of versions found
     */
    final public function listByProject($projectIdentifier, array $params = []): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__
            ));
        }

        try {
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/versions.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }
    }

    /**
     * List versions.
     *
     * @deprecated since v2.4.0, use listByProject() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of versions found or error message or false
     */
    public function all($project, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::listByProject()` instead.', E_USER_DEPRECATED);

        try {
            $this->versions = $this->listByProject(strval($project), $params);
        } catch (Exception $e) {
            if ($this->getLastResonse()->getBody() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->versions;
    }

    /**
     * Returns an array of name/id pairs (or id/name if not $reverse) of issue versions for $project.
     *
     * @param string|int $project     project id or literal identifier
     * @param bool       $forceUpdate to force the update of the projects var
     * @param bool       $reverse     to return an array indexed by name rather than id
     * @param array      $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of projects (id => project name)
     */
    public function listing($project, $forceUpdate = false, $reverse = true, array $params = [])
    {
        if (true === $forceUpdate || empty($this->versions)) {
            $this->versions = $this->listByProject($project, $params);
        }
        $ret = [];
        foreach ($this->versions['versions'] as $e) {
            $ret[(int) $e['id']] = $e['name'];
        }

        return $reverse ? array_flip($ret) : $ret;
    }

    /**
     * Get an issue version id given its name and related project.
     *
     * @param string|int $project project id or literal identifier
     * @param string     $name
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($project, $name, array $params = [])
    {
        $arr = $this->listing($project, false, true, $params);
        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * Get extended information about an issue version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET-2
     *
     * @param string $id the issue category id
     *
     * @return array information about the category
     */
    public function show($id)
    {
        return $this->get('/versions/' . urlencode($id) . '.json');
    }

    /**
     * Create a new version for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#POST
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  the new issue category data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return string|false
     */
    public function create($project, array $params = [])
    {
        $defaults = [
            'name' => null,
            'description' => null,
            'status' => null,
            'sharing' => null,
            'due_date' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
            !isset($params['name'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `name`');
        }
        $this->validateStatus($params);
        $this->validateSharing($params);

        return $this->post(
            '/projects/' . $project . '/versions.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded()
        );
    }

    /**
     * Update issue category's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#PUT
     *
     * @param string $id the issue category id
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'name' => null,
            'description' => null,
            'status' => null,
            'sharing' => null,
            'due_date' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);
        $this->validateStatus($params);
        $this->validateSharing($params);

        return $this->put(
            '/versions/' . $id . '.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded()
        );
    }

    private function validateStatus(array $params = [])
    {
        $arrStatus = [
            'open',
            'locked',
            'closed',
        ];
        if (isset($params['status']) && !in_array($params['status'], $arrStatus)) {
            throw new InvalidParameterException('Possible values for status : ' . implode(', ', $arrStatus));
        }
    }

    private function validateSharing(array $params = [])
    {
        $arrSharing = [
            'none' => 'Not shared',
            'descendants' => 'With subprojects',
            'hierarchy' => 'With project hierarchy',
            'tree' => 'With project tree',
            'system' => 'With all projects',
        ];
        if (isset($params['sharing']) && !isset($arrSharing[$params['sharing']])) {
            throw new InvalidParameterException('Possible values for sharing : ' . implode(', ', array_keys($arrSharing)));
        }
    }

    /**
     * Delete a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#DELETE
     *
     * @param int $id id of the version
     *
     * @return string
     */
    public function remove($id)
    {
        return $this->delete('/versions/' . $id . '.xml');
    }
}
