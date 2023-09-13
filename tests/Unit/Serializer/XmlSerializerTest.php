<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\XmlSerializer;

class XmlSerializerTest extends TestCase
{
    public static function getEncodedToNormalizedData(): array
    {
        return [
            [
                '<a/>',
                [],
            ],
            [
                '<?xml version="1.0"?><issue/>',
                [],
            ],
            [
                '<?xml version="1.0"?><issue></issue>',
                [],
            ],
            [
                '<?xml version="1.0"?><issue>1</issue>',
                ['1'],
            ],
            [
                <<< XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issues type="array" count="1640">
                  <issue>
                    <id>4326</id>
                  </issue>
                  <issue>
                    <id>4325</id>
                  </issue>
                </issues>
                XML,
                [
                    '@attributes' => [
                        'type' => 'array',
                        'count' => '1640',
                    ],
                    'issue' => [
                        [
                            'id' => '4326',
                        ],
                        [
                            'id' => '4325',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getEncodedToNormalizedData
     */
    public function createFromStringDecodesToExpectedNormalizedData(string $data, $expected)
    {
        $serializer = XmlSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    public static function getInvalidEncodedData(): array
    {
        return [
            [
                'Catched error "String could not be parsed as XML" while decoding XML: ',
                '',
            ],
            [
                'Catched error "String could not be parsed as XML" while decoding XML: <?xml version="1.0" encoding="UTF-8"?>',
                '<?xml version="1.0" encoding="UTF-8"?>',
            ],
            [
                'Catched error "String could not be parsed as XML" while decoding XML: <?xml version="1.0" encoding="UTF-8"?><>',
                '<?xml version="1.0" encoding="UTF-8"?><>',
            ],
            [
                'Catched error "String could not be parsed as XML" while decoding XML: <?xml version="1.0" encoding="UTF-8"?><a>',
                '<?xml version="1.0" encoding="UTF-8"?><a>',
            ],
            [
                'Catched error "String could not be parsed as XML" while decoding XML: <?xml version="1.0" encoding="UTF-8"?></>',
                '<?xml version="1.0" encoding="UTF-8"?></>',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getInvalidEncodedData
     */
    public function createFromStringWithInvalidStringThrowsException(string $message, string $data)
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        $serializer = XmlSerializer::createFromString($data);
    }

    public static function getNormalizedToEncodedData(): array
    {
        return [
            [
                [
                    'issue' => [
                        'project_id' => 1,
                        'subject' => 'Example',
                        'priority_id' => 4,
                    ],
                ],
                <<< XML
                <?xml version="1.0"?>
                <issue>
                  <project_id>1</project_id>
                  <subject>Example</subject>
                  <priority_id>4</priority_id>
                </issue>
                XML,
            ],
            [
                [
                    'issue' => [
                        'project_id' => 1,
                        'subject' => 'Example',
                        'priority_id' => 4,
                    ],
                    'ignored' => [
                        'only the first element of the array will be used',
                    ],
                ],
                <<< XML
                <?xml version="1.0"?>
                <issue>
                  <project_id>1</project_id>
                  <subject>Example</subject>
                  <priority_id>4</priority_id>
                </issue>
                XML,
            ],
            [
                [
                    'project' => [
                        'name' => 'some name',
                        'identifier' => 'the_identifier',
                        'custom_fields' => [
                            [
                                'id' => 123,
                                'name' => 'cf_name',
                                'field_format' => 'string',
                                'value' => [1, 2, 3],
                            ],
                        ],
                    ],
                ],
                <<< XML
                <?xml version="1.0"?>
                <project>
                  <name>some name</name>
                  <identifier>the_identifier</identifier>
                  <custom_fields type="array">
                    <custom_field name="cf_name" field_format="string" id="123" multiple="true">
                      <value type="array">
                        <value>1</value>
                        <value>2</value>
                        <value>3</value>
                      </value>
                    </custom_field>
                  </custom_fields>
                </project>
                XML,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getNormalizedToEncodedData
     */
    public function createFromArrayDecodesToExpectedString(array $data, $expected)
    {
        $serializer = XmlSerializer::createFromArray($data);

        // Load the encoded string into a DOMDocument, so we can compare the formated output
        $dom = dom_import_simplexml(new \SimpleXMLElement($serializer->getEncoded()))->ownerDocument;
        $dom->formatOutput = true;

        $this->assertSame($expected, trim($dom->saveXML()));
    }

    public static function getInvalidSerializedData(): array
    {
        return[
            [
                'Could not create XML from array: String could not be parsed as XML',
                ['0' => ['foobar']],
            ]
        ];
    }

    /**
     * @test
     *
     * @dataProvider getInvalidSerializedData
     */
    public function createFromArrayWithInvalidDataThrowsException(string $message, array $data)
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        $serializer = XmlSerializer::createFromArray($data);
    }
}
