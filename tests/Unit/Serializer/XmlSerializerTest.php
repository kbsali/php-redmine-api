<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\XmlSerializer;

/**
 * @coversDefaultClass \Redmine\Serializer\XmlSerializer
 */
class XmlSerializerTest extends TestCase
{
    /**
     * @dataProvider getEncodedToNormalizedData
     */
    #[DataProvider('getEncodedToNormalizedData')]
    public function testCreateFromStringDecodesToExpectedNormalizedData(string $data, $expected)
    {
        $serializer = XmlSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    public static function getEncodedToNormalizedData(): array
    {
        return [
            'test with single tag' => [
                '<a/>',
                [],
            ],
            'test with closed tag' => [
                '<?xml version="1.0"?><issue/>',
                [],
            ],
            'test with open and close tag' => [
                '<?xml version="1.0"?><issue></issue>',
                [],
            ],
            'test with integer' => [
                '<?xml version="1.0"?><issue>1</issue>',
                ['1'],
            ],
            'test with array' => [
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
     * @dataProvider getInvalidEncodedData
     */
    #[DataProvider('getInvalidEncodedData')]
    public function testCreateFromStringWithInvalidStringThrowsException(string $message, string $data)
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        XmlSerializer::createFromString($data);
    }

    public static function getInvalidEncodedData(): array
    {
        return [
            'empty string' => [
                'Catched errors: "" while decoding XML: ',
                '',
            ],
            'wrong start tag' => [
                'Catched errors: "Start tag expected, \'<\' not found' . "\n" . '" while decoding XML: <?xml version="1.0" encoding="UTF-8"?>',
                '<?xml version="1.0" encoding="UTF-8"?>',
            ],
            'invalid element name as start tag' => [
                'Catched errors: "StartTag: invalid element name' . "\n" . '", "Extra content at the end of the document' . "\n" . '" while decoding XML: <?xml version="1.0" encoding="UTF-8"?><>',
                '<?xml version="1.0" encoding="UTF-8"?><>',
            ],
            'Premature end of data' => [
                'Catched errors: "Premature end of data in tag a line 1' . "\n" . '" while decoding XML: <?xml version="1.0" encoding="UTF-8"?><a>',
                '<?xml version="1.0" encoding="UTF-8"?><a>',
            ],
            'invalid element name as start tag 2' => [
                'Catched errors: "StartTag: invalid element name' . "\n" . '", "Extra content at the end of the document' . "\n" . '" while decoding XML: <?xml version="1.0" encoding="UTF-8"?></>',
                '<?xml version="1.0" encoding="UTF-8"?></>',
            ],
        ];
    }

    /**
     * @dataProvider getNormalizedToEncodedData
     */
    #[DataProvider('getNormalizedToEncodedData')]
    public function testCreateFromArrayEncodesToExpectedString(array $data, $expected)
    {
        $serializer = XmlSerializer::createFromArray($data);

        $this->assertXmlStringEqualsXmlString($expected, $serializer->__toString());
    }

    public static function getNormalizedToEncodedData(): array
    {
        return [
            'test with simple string' => [
                [
                    'key' => 'value',
                ],
                <<< XML
                <?xml version="1.0"?>
                <key>value</key>
                XML,
            ],
            'test with simple array' => [
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
            'test with ignored elements' => [
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
            'test with custom fields with single value' => [
                [
                    'project' => [
                        'name' => 'some name',
                        'identifier' => 'the_identifier',
                        'custom_fields' => [
                            [
                                'id' => 123,
                                'name' => 'cf_name',
                                'field_format' => 'string',
                                'value' => 1,
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
                        <custom_field name="cf_name" field_format="string" id="123">
                            <value>1</value>
                        </custom_field>
                    </custom_fields>
                </project>
                XML,
            ],
            'test with custom fields with array value' => [
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
            'test with uploads' => [
                [
                    'issue' => [
                        'uploads' => [
                            [
                                'token' => 'asdfasdfasdfasdf',
                                'filename' => 'MyFile.pdf',
                                'description' => 'MyFile is better then YourFile...',
                                'content_type' => 'application/pdf',
                            ],
                        ],
                    ],
                ],
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <uploads type="array">
                        <upload>
                            <token>asdfasdfasdfasdf</token>
                            <filename>MyFile.pdf</filename>
                            <description>MyFile is better then YourFile...</description>
                            <content_type>application/pdf</content_type>
                        </upload>
                    </uploads>
                </issue>
                XML,
            ],
        ];
    }

    /**
     * @dataProvider getInvalidSerializedData
     */
    #[DataProvider('getInvalidSerializedData')]
    public function testCreateFromArrayWithInvalidDataThrowsException(string $message, array $data)
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        XmlSerializer::createFromArray($data);
    }

    public static function getInvalidSerializedData(): array
    {
        return[
            'invalid element name as start tag' => [
                'Could not create XML from array: "StartTag: invalid element name' . "\n" . '", "Extra content at the end of the document' . "\n" . '"',
                ['0' => ['foobar']],
            ]
        ];
    }
}
