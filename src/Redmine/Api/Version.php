<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpFactory;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

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

    private $versionNames = [];

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
                __METHOD__,
            ));
        }

        try {
            return $this->retrieveData('/projects/' . strval($projectIdentifier) . '/versions.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all versions by a project with id/name pairs.
     *
     * @param string|int $projectIdentifier project id or literal identifier
     *
     * @throws InvalidParameterException if $projectIdentifier is not of type int or string
     *
     * @return array<int,string> list of version names (id => name)
     */
    final public function listNamesByProject($projectIdentifier): array
    {
        if (! is_int($projectIdentifier) && ! is_string($projectIdentifier)) {
            throw new InvalidParameterException(sprintf(
                '%s(): Argument #1 ($projectIdentifier) must be of type int or string',
                __METHOD__,
            ));
        }

        if (array_key_exists($projectIdentifier, $this->versionNames)) {
            return $this->versionNames[$projectIdentifier];
        }

        $this->versionNames[$projectIdentifier] = [];

        $list = $this->listByProject($projectIdentifier);

        if (array_key_exists('versions', $list)) {
            foreach ($list['versions'] as $version) {
                $this->versionNames[$projectIdentifier][(int) $version['id']] = $version['name'];
            }
        }

        return $this->versionNames[$projectIdentifier];
    }

    /**
     * List versions.
     *
     * @deprecated v2.4.0 Use listByProject() instead.
     * @see Version::listByProject()
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
            if ($this->getLastResponse()->getContent() === '') {
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
     * Returns an array of name/id pairs (or id/name if not $reverse) of versions for $project.
     *
     * @deprecated v2.7.0 Use listNamesByProject() instead.
     * @see Version::listNamesByProject()
     *
     * @param string|int $project     project id or literal identifier
     * @param bool       $forceUpdate to force the update of the projects var
     * @param bool       $reverse     to return an array indexed by name rather than id
     * @param array      $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of versions (id => version name)
     */
    public function listing($project, $forceUpdate = false, $reverse = true, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNamesByProject()` instead.', E_USER_DEPRECATED);

        return $this->doListing($project, $forceUpdate, $reverse, $params);
    }

    /**
     * Get an version id given its name and related project.
     *
     * @deprecated v2.7.0 Use listNamesByProject() instead.
     * @see Version::listNamesByProject()
     *
     * @param string|int $project project id or literal identifier
     * @param string     $name The version name
     * @param array      $params  optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($project, $name, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNamesByProject()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing($project, false, true, $params);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * Get extended information about a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#GET-2
     *
     * @param int $id the version id
     *
     * @return array|false|string information about the version as array of false|string on error
     */
    public function show($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/versions/' . urlencode(strval($id)) . '.json',
        ));

        $body = $this->lastResponse->getContent();

        if ('' === $body) {
            return false;
        }

        try {
            return JsonSerializer::createFromString($body)->getNormalized();
        } catch (SerializerException $e) {
            return 'Error decoding body as JSON: ' . $e->getPrevious()->getMessage();
        }
    }

    /**
     * Create a new version for $project given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#POST
     *
     * @param string|int $project project id or literal identifier
     * @param array      $params  the new version data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return SimpleXMLElement|string
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

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'POST',
            '/projects/' . $project . '/versions.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded(),
        ));

        $body = $this->lastResponse->getContent();

        if ('' !== $body) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Update version's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#PUT
     *
     * @param int $id the version id
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

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'PUT',
            '/versions/' . $id . '.xml',
            XmlSerializer::createFromArray(['version' => $params])->getEncoded(),
        ));

        return $this->lastResponse->getContent();
    }

    private function validateStatus(array $params = [])
    {
        $arrStatus = [
            'open',
            'locked',
            'closed',
        ];
        if (isset($params['status']) && !in_array($params['status'], $arrStatus)) {
            throw new InvalidParameterException('Possible values for status are: ' . implode(', ', $arrStatus));
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
            throw new InvalidParameterException('Possible values for sharing are: ' . implode(', ', array_keys($arrSharing)));
        }
    }

    /**
     * Delete a version.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Versions#DELETE
     *
     * @param int $id id of the version
     *
     * @return string empty string on success
     */
    public function remove($id)
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeXmlRequest(
            'DELETE',
            '/versions/' . $id . '.xml',
        ));

        return $this->lastResponse->getContent();
    }

    private function doListing($project, bool $forceUpdate, bool $reverse, array $params)
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
}
