<?php

declare(strict_types=1);

namespace Redmine\Http;

/**
 * Factory for HTTP objects.
 *
 * @internal
 */
final class HttpFactory
{
    public static function makeResponse(int $statusCode, string $contentType, string $content): Response
    {
        return new class ($statusCode, $contentType, $content) implements Response {
            private $statusCode;
            private $contentType;
            private $body;

            public function __construct(int $statusCode, string $contentType, string $body)
            {
                $this->statusCode = $statusCode;
                $this->contentType = $contentType;
                $this->body = $body;
            }

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
            private $method;
            private $path;
            private $contentType;
            private $content;

            public function __construct(string $method, string $path, string $contentType, string $content)
            {
                $this->method = $method;
                $this->path = $path;
                $this->contentType = $contentType;
                $this->content = $content;
            }

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
        return static::makeRequest($method, $path, 'application/json', $content);
    }

    public static function makeXmlRequest(string $method, string $path, string $content = ''): Request
    {
        return static::makeRequest($method, $path, 'application/xml', $content);
    }
}
