<?php

declare(strict_types=1);

namespace Redmine\Http;

/**
 * Factory for HTTP objects.
 */
final class HttpFactory
{
    public static function makeResponse(int $statusCode, string $contentType, string $content): Response
    {
        return new class ($statusCode, $contentType, $content) implements Response {
            public function __construct(private readonly int $statusCode, private readonly string $contentType, private readonly string $body) {}

            public function getStatusCode(): int
            {
                return $this->statusCode;
            }

            public function getContentType(): string
            {
                return $this->contentType;
            }

            public function getContent(): string
            {
                return $this->body;
            }
        };
    }

    public static function makeRequest(string $method, string $path, string $contentType = '', string $content = ''): Request
    {
        return new class ($method, $path, $contentType, $content) implements Request {
            public function __construct(private readonly string $method, private readonly string $path, private readonly string $contentType, private readonly string $content) {}

            public function getMethod(): string
            {
                return $this->method;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getContentType(): string
            {
                return $this->contentType;
            }

            public function getContent(): string
            {
                return $this->content;
            }
        };
    }

    public static function makeJsonRequest(string $method, string $path, string $content = ''): Request
    {
        return self::makeRequest($method, $path, 'application/json', $content);
    }

    public static function makeXmlRequest(string $method, string $path, string $content = ''): Request
    {
        return self::makeRequest($method, $path, 'application/xml', $content);
    }
}
