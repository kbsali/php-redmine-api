<?php

declare(strict_types=1);

namespace Redmine\Api;

use Redmine\Client\Client;
use Redmine\Exception;
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
class CustomField extends AbstractApi
{
    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @var null|array<mixed>
     */
    private ?array $customFields = null;

    /**
     * @var null|array<int,string>
     */
    private ?array $customFieldNames = null;

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see CustomField::fromHttpClient()
     *
     * @param Client|HttpClient $client
     */
    public function __construct($client/*, bool $privatelyCalled = false*/)
    {
        $privatelyCalled = (func_num_args() > 1) ? func_get_arg(1) : false;

        if ($privatelyCalled === true) {
            parent::__construct($client);

            return;
        }

        if (static::class !== self::class) {
            $className = (new \ReflectionClass($this))->isAnonymous() ? '' : ' in `' . static::class . '`';
            @trigger_error('Class `' . self::class . '` will declared as final in v3.0.0, stop extending it' . $className . '.', E_USER_DEPRECATED);
        } else {
            @trigger_error('Method `' . __METHOD__ . '()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `' . self::class . '::fromHttpClient()` instead.', E_USER_DEPRECATED);
        }

        parent::__construct($client);
    }

    /**
     * List custom fields.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
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

        $this->customFieldNames = [];

        $list = $this->list();

        if (array_key_exists('custom_fields', $list)) {
            foreach ($list['custom_fields'] as $customField) {
                $this->customFieldNames[(int) $customField['id']] = (string) $customField['name'];
            }
        }

        return $this->customFieldNames;
    }

    /**
     * List custom fields.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see CustomField::list()
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<mixed>|string|false list of custom fields found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . self::class . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->customFields = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() instanceof \Throwable) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->customFields;
    }

    /**
     * Returns an array of custom fields with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see CustomField::listNames()
     *
     * @param bool         $forceUpdate to force the update of the custom fields var
     * @param array<mixed> $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<string,int> list of custom fields (name => id)
     */
    public function listing($forceUpdate = false, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . self::class . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing($forceUpdate, $params);
    }

    /**
     * Get a custom field id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see CustomField::listNames()
     *
     * @param string|int   $name   customer field name
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($name, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . self::class . '::listNames()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing(false, $params);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    private function doListing(bool $forceUpdate, array $params): array
    {
        if ($forceUpdate || $this->customFields === null) {
            $this->customFields = $this->list($params);
        }

        $ret = [];

        foreach ($this->customFields['custom_fields'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}
