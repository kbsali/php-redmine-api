<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;

/**
 * Listing roles.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Roles
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class Role extends AbstractApi
{
    use ResponseValidationTrait;

    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var null|array<int,string>
     */
    private ?array $roleNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List roles.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Roles#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of roles found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/roles.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all roles with id/name pairs.
     *
     * @return array<int,string> list of roles (id => name)
     */
    final public function listNames(): array
    {
        if ($this->roleNames !== null) {
            return $this->roleNames;
        }

        $names = [];
        $list = $this->list();

        if (array_key_exists('roles', $list)) {
            foreach ($list['roles'] as $role) {
                $names[(int) $role['id']] = $role['name'];
            }
        }

        $this->roleNames = $names;

        return $this->roleNames;
    }

    /**
     * Returns the list of permissions for a given role (Redmine v2.2.0).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Roles#GET-2
     *
     * @param int $id the role id
     *
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected response
     *
     * @return array<mixed> information about the role
     */
    public function show($id): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeJsonRequest(
            'GET',
            '/roles/' . urlencode(strval($id)) . '.json',
        ));

        return $this->getJsonArrayOrThrow();
    }
}
