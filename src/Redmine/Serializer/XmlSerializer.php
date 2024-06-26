<?php

namespace Redmine\Serializer;

use JsonException;
use Redmine\Exception\SerializerException;
use SimpleXMLElement;
use Stringable;
use Throwable;

/**
 * XmlSerializer.
 */
final class XmlSerializer implements Stringable
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

    /** @var mixed */
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

    public function __toString(): string
    {
        return $this->getEncoded();
    }

    private function deserialize(string $encoded): void
    {
        $this->encoded = $encoded;

        $prevSetting = libxml_use_internal_errors(true);

        try {
            $this->deserialized = new SimpleXMLElement($encoded);
        } catch (Throwable $e) {
            $errors = [];

            foreach (libxml_get_errors() as $error) {
                $errors[] = $error->message;
            }

            libxml_clear_errors();

            throw new SerializerException(
                'Catched errors: "' . implode('", "', $errors) . '" while decoding XML: ' . $encoded,
                $e->getCode(),
                $e,
            );
        } finally {
            libxml_use_internal_errors($prevSetting);
        }

        $this->normalize($this->deserialized);
    }

    private function normalize(SimpleXMLElement $deserialized): void
    {
        try {
            $serialized = json_encode($deserialized, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new SerializerException('Catched error "' . $e->getMessage() . '" while encoding SimpleXMLElement', $e->getCode(), $e);
        }

        $this->normalized = JsonSerializer::createFromString($serialized)->getNormalized();
    }

    private function denormalize(array $normalized): void
    {
        $this->normalized = $normalized;

        $rootElementName = array_key_first($this->normalized);

        $prevSetting = libxml_use_internal_errors(true);

        try {
            $this->deserialized = $this->createXmlElement($rootElementName, $this->normalized[$rootElementName]);
        } catch (Throwable $e) {
            $errors = [];

            foreach (libxml_get_errors() as $error) {
                $errors[] = $error->message;
            }

            libxml_clear_errors();

            throw new SerializerException(
                'Could not create XML from array: "' . implode('", "', $errors) . '"',
                $e->getCode(),
                $e,
            );
        } finally {
            libxml_use_internal_errors($prevSetting);
        }

        $this->encoded = $this->deserialized->asXml();
    }

    private function createXmlElement(string $rootElementName, $params): SimpleXMLElement
    {
        $value = '';
        if (! is_array($params)) {
            $value = $params;
        }

        $xml = new SimpleXMLElement('<?xml version="1.0"?><' . $rootElementName . '>' . $value . '</' . $rootElementName . '>');

        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $this->addChildToXmlElement($xml, $k, $v);
            }
        }

        return $xml;
    }

    private function addChildToXmlElement(SimpleXMLElement $xml, $k, $v): void
    {
        $specialParams = [
            'enabled_module_names' => 'enabled_module_names',
            'issue_custom_field_ids' => 'issue_custom_field',
            'role_ids' => 'role_id',
            'tracker_ids' => 'tracker',
            'user_ids' => 'user_id',
            'watcher_user_ids' => 'watcher_user_id',
        ];

        if ('custom_fields' === $k && is_array($v)) {
            $this->attachCustomFieldXML($xml, $v, 'custom_fields', 'custom_field');
        } elseif ('uploads' === $k && is_array($v)) {
            $uploadsItem = $xml->addChild('uploads', '');
            $uploadsItem->addAttribute('type', 'array');
            foreach ($v as $upload) {
                $upload_item = $uploadsItem->addChild('upload', '');
                foreach ($upload as $upload_k => $upload_v) {
                    $upload_item->addChild($upload_k, $upload_v);
                }
            }
        } elseif (isset($specialParams[$k]) && is_array($v)) {
            $array = $xml->addChild($k, '');
            $array->addAttribute('type', 'array');
            foreach ($v as $id) {
                $array->addChild($specialParams[$k], $id);
            }
        } elseif (is_array($v)) {
            $array = $xml->addChild($k, '');
            $array->addAttribute('type', 'array');
            foreach ($v as $id) {
                $array->addChild($k, $id);
            }
        } else {
            $xml->$k = $v;
        }
    }

    /**
     * Attaches Custom Fields to XML element.
     *
     * @param SimpleXMLElement $xml    XML Element the custom fields are attached to
     * @param array            $fields array of fields to attach, each field needs name, id and value set
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_api#Working-with-custom-fields
     */
    private function attachCustomFieldXML(SimpleXMLElement $xml, array $fields, string $fieldsName, string $fieldName): void
    {
        $_fields = $xml->addChild($fieldsName);
        $_fields->addAttribute('type', 'array');
        foreach ($fields as $field) {
            $_field = $_fields->addChild($fieldName);

            if (isset($field['name'])) {
                $_field->addAttribute('name', $field['name']);
            }
            if (isset($field['field_format'])) {
                $_field->addAttribute('field_format', $field['field_format']);
            }
            if (isset($field['id'])) {
                $_field->addAttribute('id', $field['id']);
            }
            if (array_key_exists('value', $field) && is_array($field['value'])) {
                $_field->addAttribute('multiple', 'true');
                $_values = $_field->addChild('value');
                if (array_key_exists('token', $field['value'])) {
                    foreach ($field['value'] as $key => $val) {
                        $_values->addChild($key, $val);
                    }
                } else {
                    $_values->addAttribute('type', 'array');
                    foreach ($field['value'] as $val) {
                        $_values->addChild('value', $val);
                    }
                }
            } else {
                $_field->value = $field['value'];
            }
        }
    }
}
