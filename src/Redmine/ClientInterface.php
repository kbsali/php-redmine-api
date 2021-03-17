<?php

namespace Redmine;

use Redmine\Api\ApiInterface;

/**
 * client interface.
 */
interface ClientInterface
{
    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return ApiInterface
     */
    public function getApi(string $name): ApiInterface;

    /**
     * HTTP GETs a json $path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function requestGet(string $path): bool;

    /**
     * HTTP POSTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function requestPost(string $path, string $data): bool;

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function requestPut(string $path, string $data): bool;

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function requestDelete(string $path): bool;

    /**
    * Returns status code of the last response.
    *
    * @return int
    */
    public function getLastResponseStatusCode(): int;

    /**
    * Returns content type of the last response.
    *
    * @return string
    */
    public function getLastResponseContentType(): string;

    /**
     * Returns the body of the last response.
     *
     * @return string
     */
    public function getLastResponseBody(): string;
}
