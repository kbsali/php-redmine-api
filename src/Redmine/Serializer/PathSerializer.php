<?php

namespace Redmine\Serializer;

/**
 * PathSerializer
 */
final class PathSerializer
{
    public static function create(string $path, array $queryParams = []): self
    {
        $serializer = new self();
        $serializer->path = $path;
        $serializer->queryParams = $queryParams;

        return $serializer;
    }

    private string $path;

    private array $queryParams;

    private function __construct()
    {
        // use factory method instead
    }

    public function getPath(): string
    {
        $queryString = '';

        if (! empty($this->queryParams)) {
            $queryString = '?' . \http_build_query($this->queryParams);
        }

        return $this->path . $queryString;
    }
}
