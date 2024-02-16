<?php

declare(strict_types=1);

namespace Redmine\Http;

use Redmine\Exception\ClientException;

/**
 * Minimalistic HTTP client interface.
 *
 * The interface is designed in such a way that it is prepared for all future Redmine features.
 * Therefore, it does not specify or restrict the HTTP method, the URL, headers or the body.
 *
 * The client is responsible for ensuring that all data is sent in the correct form and
 * that received data is processed correctly.
 */
interface HttpClient
{
    /**
     * Create and send a HTTP request and return the response
     *
     * @throws ClientException If anything goes wrong on creating or sending the request
     */
    public function request(Request $request): Response;
}
