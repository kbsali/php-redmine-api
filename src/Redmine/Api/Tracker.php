<?php

declare(strict_types=1);

namespace Redmine\Api;

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
     * @var null|array<int,string>
     */
    private ?array $trackerNames = null;

    /**
     * @deprecated v2.9.0 Use fromHttpClient() instead.
     * @see Group::fromHttpClient()
     *
     * @param HttpClient $client
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
     * @throws UnexpectedResponseException if the Redmine server delivers an unexpected status code
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

        $names = [];
        $list = $this->list();

        if (array_key_exists('trackers', $list)) {
            foreach ($list['trackers'] as $role) {
                $names[(int) $role['id']] = $role['name'];
            }
        }

        $this->trackerNames = $names;

        return $this->trackerNames;
    }

}
