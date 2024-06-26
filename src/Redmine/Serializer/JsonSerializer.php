<?php

namespace Redmine\Serializer;

use JsonException;
use Redmine\Exception\SerializerException;
use Stringable;

/**
 * JsonSerializer.
 */
final class JsonSerializer implements Stringable
{
    /**
     * @throws SerializerException if $data is not valid JSON
     */
    public static function createFromString(string $data): self
    {
        $serializer = new self();
        $serializer->decode($data);

        return $serializer;
    }

    /**
     * @throws SerializerException if $data could not be serialized to JSON
     */
    public static function createFromArray(array $data): self
    {
        $serializer = new self();
        $serializer->encode($data);

        return $serializer;
    }

    private string $encoded;

    /** @var mixed */
    private $normalized;

    private function __construct()
    {
        // use factory method instead
    }

    /**
     * @return mixed
     */
    public function getNormalized()
    {
        return $this->normalized;
    }

    public function getEncoded(): string
    {
        return $this->encoded;
    }

    public function __toString(): string
    {
        return $this->getEncoded();
    }

    private function decode(string $encoded): void
    {
        $this->encoded = $encoded;

        try {
            $this->normalized = json_decode(
                $encoded,
                true,
                512,
                \JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw new SerializerException('Catched error "' . $e->getMessage() . '" while decoding JSON: ' . $encoded, $e->getCode(), $e);
        }
    }

    private function encode(array $normalized): void
    {
        $this->normalized = $normalized;

        try {
            $this->encoded = json_encode(
                $normalized,
                \JSON_THROW_ON_ERROR,
                512,
            );
        } catch (JsonException $e) {
            throw new SerializerException(
                'Could not encode JSON from array: ' . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }
}
