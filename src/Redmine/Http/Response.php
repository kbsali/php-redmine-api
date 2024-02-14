<?php

declare(strict_types=1);

namespace Redmine\Http;

/**
 * Response interface.
 *
 * The method signatures are defined with the intention that an implementing class
 * can implment this interface and also the PSR-7 `\Psr\Http\Message\ResponseInterface`
 */
interface Response
{
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     */
    public function getStatusCode(): int;

    /**
     * Returns content type.
     */
    public function getContentType(): string;

    /**
     * Returns the body content.
     */
    public function getContent(): string;
}
