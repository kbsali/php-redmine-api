<?php

namespace Redmine\Serializer;

use Exception;
use Redmine\Exception\SerializerException;
use SimpleXMLElement;

/**
 * XmlSerializer
 */
final class XmlSerializer
{
    /**
     * @throws SerializerException if $data is not valid XML
     */
    public static function createFromString(string $data): self
    {
        $serializer = new self();
        $serializer->deserialize($data);

        return $serializer;
    }

    /**
     * @throws SerializerException if $data could not be serialized to XML
     */
    public static function createFromArray(array $data): self
    {
        $serializer = new self();
        $serializer->denormalize($data);

        return $serializer;
    }

    private string $encoded;

    /** @var mixed $normalized */
    private $normalized;

    private SimpleXMLElement $deserialized;

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

    private function deserialize(string $encoded): void
    {
        $this->encoded = $encoded;

        try {
            $this->deserialized = new SimpleXMLElement($encoded);
        } catch (Exception $e) {
            throw new SerializerException(
                'Catched error "' . $e->getMessage() . '" while decoding XML: ' . $encoded,
                $e->getCode(),
                $e
            );
        }

        $this->normalize($this->deserialized);
    }

    private function normalize(SimpleXMLElement $deserialized): void
    {
        try {
            $serialized = json_encode($deserialized, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new SerializerException(
                'Catched error "' . $e->getMessage() . '" while encoding SimpleXMLElement',
                $e->getCode(),
                $e
            );
        }

        $this->normalized = JsonSerializer::createFromString($serialized)->getNormalized();
    }

    private function denormalize(array $normalized): void
    {
        $this->normalized = $normalized;

        $key = array_key_first($this->normalized);

        $this->deserialized = $this->createXmlElement($key, $this->normalized[$key]);

        $this->encoded = $this->deserialized->asXml();
    }

    private function createXmlElement(string $key, array $params): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<?xml version="1.0"?><'.$key.'></'.$key.'>');

        foreach ($params as $k => $v) {
            $xml->$k = $v;
        }

        return $xml;
    }
}
