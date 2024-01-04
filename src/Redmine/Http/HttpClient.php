<?php

declare(strict_types=1);

namespace Redmine\Http;

use Redmine\Exception\ClientException;
use Redmine\Exception\InvalidHttpMethodException;

/**
 * client interface.
 */
interface HttpClient
{
    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const DELETE = 'DELETE';

    /**
     * Create and send a HTTP request and return the response
     *
     * @param self::* $method Valid values are 'GET', 'POST', 'PUT' and 'DELETE'
     * @param string $body must be empty string on 'GET' request
     *
     * @throws InvalidHttpMethodException if $method is not a valid value
     * @throws ClientException If anything goes wrong on creating or sending the request
     */
    public function request(string $method, string $path, string $body = ''): Response;
}
