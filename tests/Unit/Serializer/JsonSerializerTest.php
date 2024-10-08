<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\JsonSerializer;

#[CoversClass(JsonSerializer::class)]
class JsonSerializerTest extends TestCase
{
    public static function getEncodedToNormalizedData(): array
    {
        return [
            [
                'null',
                null,
            ],
            [
                'true',
                true,
            ],
            [
                'false',
                false,
            ],
            [
                '0',
                0,
            ],
            [
                '""',
                '',
            ],
            [
                '"foobar"',
                'foobar',
            ],
            [
                '[]',
                [],
            ],
            [
                '{}',
                [],
            ],
            [
                '["foo","bar"]',
                ['foo', 'bar'],
            ],
            [
                '{"foo":"bar"}',
                ['foo' => 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider getEncodedToNormalizedData
     */
    #[DataProvider('getEncodedToNormalizedData')]
    public function testCreateFromStringDecodesToExpectedNormalizedData(string $data, string $expected): void
    {
        $serializer = JsonSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    public static function getInvalidEncodedData(): array
    {
        return [
            [
                'Catched error "Syntax error" while decoding JSON: ',
                '',
            ],
            [
                'Catched error "Syntax error" while decoding JSON: ["foo":"bar"]',
                '["foo":"bar"]',
            ],
        ];
    }

    /**
     * @dataProvider getInvalidEncodedData
     */
    #[DataProvider('getInvalidEncodedData')]
    public function testCreateFromStringWithInvalidStringThrowsException(string $message, string $data): void
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        $serializer = JsonSerializer::createFromString($data);
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
                <<< JSON
                {
                    "issue": {
                        "project_id": 1,
                        "subject": "Example",
                        "priority_id": 4
                    }
                }
                JSON,
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
                <<< JSON
                {
                    "issue": {
                        "project_id": 1,
                        "subject": "Example",
                        "priority_id": 4
                    },
                    "ignored": [
                        "only the first element of the array will be used"
                    ]
                }
                JSON,
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
                <<< JSON
                {
                    "project": {
                        "name": "some name",
                        "identifier": "the_identifier",
                        "custom_fields": [
                            {
                                "id": 123,
                                "name": "cf_name",
                                "field_format": "string",
                                "value": [
                                    1,
                                    2,
                                    3
                                ]
                            }
                        ]
                    }
                }
                JSON,
            ],
        ];
    }

    /**
     * @dataProvider getNormalizedToEncodedData
     */
    #[DataProvider('getNormalizedToEncodedData')]
    public function testCreateFromArrayEncodesToExpectedString(array $data, string $expected): void
    {
        $serializer = JsonSerializer::createFromArray($data);

        $this->assertJsonStringEqualsJsonString($expected, $serializer->__toString());
    }

    public static function getInvalidSerializedData(): array
    {
        return [
            [
                'Could not encode JSON from array: Type is not supported',
                [fopen('php://temp', 'r+')],
            ],
        ];
    }

    /**
     * @dataProvider getInvalidSerializedData
     */
    #[DataProvider('getInvalidSerializedData')]
    public function testCreateFromArrayWithInvalidDataThrowsException(string $message, array $data): void
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        $serializer = JsonSerializer::createFromArray($data);
    }
}
