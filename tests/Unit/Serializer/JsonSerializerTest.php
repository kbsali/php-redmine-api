<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\JsonSerializer;

#[CoversClass(JsonSerializer::class)]
final class JsonSerializerTest extends TestCase
{
    /**
     * @return \Iterator<int|string, mixed>
     */
    public static function getEncodedToNormalizedData(): \Iterator
    {
        yield [
            'null',
            null,
        ];
        yield [
            'true',
            true,
        ];
        yield [
            'false',
            false,
        ];
        yield [
            '0',
            0,
        ];
        yield [
            '""',
            '',
        ];
        yield [
            '"foobar"',
            'foobar',
        ];
        yield [
            '[]',
            [],
        ];
        yield [
            '{}',
            [],
        ];
        yield [
            '["foo","bar"]',
            ['foo', 'bar'],
        ];
        yield [
            '{"foo":"bar"}',
            ['foo' => 'bar'],
        ];
    }

    /**
     * @param bool|int|string|array<mixed>|null $expected
     */
    #[DataProvider('getEncodedToNormalizedData')]
    public function testCreateFromStringDecodesToExpectedNormalizedData(string $data, bool|int|string|array|null $expected): void
    {
        $serializer = JsonSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    /**
     * @return \Iterator<int|string, string[]>
     */
    public static function getInvalidEncodedData(): \Iterator
    {
        yield [
            '',
            'Catched error "Syntax error" while decoding JSON: ',
        ];
        yield [
            '["foo":"bar"]',
            (PHP_VERSION_ID < 80600) ? 'Catched error "Syntax error" while decoding JSON: ["foo":"bar"]' : 'Catched error "Syntax error near location 1:7" while decoding JSON: ["foo":"bar"]',
        ];
    }

    #[DataProvider('getInvalidEncodedData')]
    public function testCreateFromStringWithInvalidStringThrowsException(string $data, string $message): void
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        JsonSerializer::createFromString($data);
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getNormalizedToEncodedData(): \Iterator
    {
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
    }

    /**
     * @param array<mixed> $data
     */
    #[DataProvider('getNormalizedToEncodedData')]
    public function testCreateFromArrayEncodesToExpectedString(array $data, string $expected): void
    {
        $serializer = JsonSerializer::createFromArray($data);

        $this->assertJsonStringEqualsJsonString($expected, $serializer->__toString());
        $this->assertJsonStringEqualsJsonString($expected, $serializer->getEncoded());
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getInvalidSerializedData(): \Iterator
    {
        yield [
            'Could not encode JSON from array: Type is not supported',
            [fopen('php://temp', 'r+')],
        ];
    }

    /**
     * @param array<mixed> $data
     */
    #[DataProvider('getInvalidSerializedData')]
    public function testCreateFromArrayWithInvalidDataThrowsException(string $message, array $data): void
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        JsonSerializer::createFromArray($data);
    }
}
