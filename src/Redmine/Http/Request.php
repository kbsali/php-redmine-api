<?php

declare(strict_types=1);

namespace Redmine\Http;

/**
 * Request interface.
 *
 * The method signatures are defined with the intention that an implementing class
 * can implment this interface and also the PSR-7 `\Psr\Http\Message\RequestInterface`
 */
interface Request
{
    /**
     * Returns the http method.
     */
    public function getMethod(): string;

    /**
     * Returns the path with optional attached query string.
     */
    public function getPath(): string;

    /**
     * Returns content type.
     */
    public function getContentType(): string;

    /**
     * Returns the body content.
     */
    public function getContent(): string;
}
