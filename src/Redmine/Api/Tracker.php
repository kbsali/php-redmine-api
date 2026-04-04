<?php

namespace Redmine\Api;

use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;

/**
 * Listing trackers.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Trackers
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Tracker extends AbstractApi
{
    final public static function fromHttpClient(HttpClient $httpClient): self
    {
        return new self($httpClient, true);
    }

    /**
     * @var null|array<mixed>
     */
    private ?array $trackers = null;

    /**
     * @var null|array<int,string>
     */
    private ?array $trackerNames = null;

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see Group::fromHttpClient()
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
     * List trackers.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Trackers#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array<mixed> list of trackers found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/trackers.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all trackers with id/name pairs.
     *
     * @return array<int,string> list of trackers (id => name)
     */
    final public function listNames(): array
    {
        if ($this->trackerNames !== null) {
            return $this->trackerNames;
        }

        $this->trackerNames = [];
        $list = $this->list();

        if (array_key_exists('trackers', $list)) {
            foreach ($list['trackers'] as $role) {
                $this->trackerNames[(int) $role['id']] = $role['name'];
            }
        }

        return $this->trackerNames;
    }

    /**
     * List trackers.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see Tracker::list()
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Trackers#GET
     *
     * @param array<mixed> $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array<mixed>|string|false list of trackers found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . self::class . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->trackers = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() instanceof \Throwable) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->trackers;
    }

    /**
     * Returns an array of trackers with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Tracker::listNames()
     *
     * @param bool $forceUpdate to force the update of the trackers var
     *
     * @return array<mixed> list of trackers (id => name)
     */
    public function listing($forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . self::class . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing($forceUpdate);
    }

    /**
     * Get a tracker id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Tracker::listNames()
     *
     * @param string|int $name tracker name
     *
     * @return int|false
     */
    public function getIdByName($name)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . self::class . '::listNames()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing(false);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    /**
     * @return array<mixed>
     */
    private function doListing(bool $forceUpdate): array
    {
        if ($forceUpdate || $this->trackers === null) {
            $this->trackers = $this->list();
        }

        $ret = [];

        foreach ($this->trackers['trackers'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}
