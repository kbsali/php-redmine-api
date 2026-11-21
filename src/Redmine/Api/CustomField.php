<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * Listing custom fields.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
final class CustomField extends AbstractApi
{
    public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient);
    }

    /**
     * @var null|array<int,string>
     */
    private ?array $customFieldNames = null;

    private function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient);
    }

    /**
     * List custom fields.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
     *
     * @return array<mixed> list of custom fields found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/custom_fields.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all custom fields with id/name pairs.
     *
     * @return array<int,string> list of custom fields (id => name)
     */
    final public function listNames(): array
    {
        if ($this->customFieldNames !== null) {
            return $this->customFieldNames;
        }

        $names = [];

        $list = $this->list();

        if (array_key_exists('custom_fields', $list)) {
            foreach ($list['custom_fields'] as $customField) {
                $names[(int) $customField['id']] = (string) $customField['name'];
            }
        }

        $this->customFieldNames = $names;

        return $this->customFieldNames;
    }

}
