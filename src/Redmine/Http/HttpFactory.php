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
}
