<?php

namespace Redmine\Client;

use Redmine\Api;
use Redmine\Exception\InvalidApiNameException;

/**
 * client interface.
 */
interface Client
{
    /**
     * @throws InvalidApiNameException if $name is not a valid api name
     */
    public function getApi(string $name): Api;

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     */
    public function startImpersonateUser(string $username): void;

    /**
     * Remove the user impersonate.
     */
    public function stopImpersonateUser(): void;

    /**
     * Create and send a GET request.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     */
    public function requestGet(string $path): bool;

    /**
     * Create and send a POST request.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     */
    public function requestPost(string $path, string $body): bool;

    /**
     * Create and send a PUT request.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     */
    public function requestPut(string $path, string $body): bool;

    /**
     * Create and send a DELETE request.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     */
    public function requestDelete(string $path): bool;

    /**
     * Returns status code of the last response.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` or `\Redmine\Api\AbstractApi::getLastResponse()` instead
     * @see \Redmine\Http\HttpClient::request()
     * @see \Redmine\Api\AbstractApi::getLastResponse()
     */
    public function getLastResponseStatusCode(): int;

    /**
     * Returns content type of the last response.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` or `\Redmine\Api\AbstractApi::getLastResponse()` instead
     * @see \Redmine\Http\HttpClient::request()
     * @see \Redmine\Api\AbstractApi::getLastResponse()
     */
    public function getLastResponseContentType(): string;

    /**
     * Returns the body of the last response.
     *
     * @deprecated v2.8.0 Use `\Redmine\Http\HttpClient::request()` or `\Redmine\Api\AbstractApi::getLastResponse()` instead
     * @see \Redmine\Http\HttpClient::request()
     * @see \Redmine\Api\AbstractApi::getLastResponse()
     */
    public function getLastResponseBody(): string;
}
