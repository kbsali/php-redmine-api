<?php

namespace Redmine\Serializer;

/**
 * PathSerializer.
 *
 * @internal
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

        if (!empty($this->queryParams)) {
            $queryString = '?'.\http_build_query($this->queryParams);

            // @see #154: replace every encoded array (`foo[0]=`, `foo[1]=`, etc with `foo[]=`)
            $queryString = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $queryString);
        }

        return $this->path.$queryString;
    }
}
