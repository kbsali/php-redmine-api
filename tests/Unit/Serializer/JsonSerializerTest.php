<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\JsonSerializer;

class JsonSerializerTest extends TestCase
{
    public function getEncodedToNormalizedData()
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
     * @test
     *
     * @dataProvider getEncodedToNormalizedData
     */
    public function createFromStringDecodesToExpectedNormalizedData(string $data, $expected)
    {
        $serializer = JsonSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    public function getInvalidEncodedData()
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
     * @test
     *
     * @dataProvider getInvalidEncodedData
     */
    public function createFromStringWithInvalidStringThrowsException(string $message, string $data)
    {
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage($message);

        $serializer = JsonSerializer::createFromString($data);
    }

    public function getNormalizedToEncodedData()
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
     * @test
     *
     * @dataProvider getNormalizedToEncodedData
     */
    public function createFromArrayDecodesToExpectedString(array $data, $expected)
    {
        $serializer = JsonSerializer::createFromArray($data);

        // decode the result, so we encode again with JSON_PRETTY_PRINT to compare the formated output
        $encoded = json_encode(
            json_decode($serializer->getEncoded(), true, 512, \JSON_THROW_ON_ERROR),
            \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT
        );

        $this->assertSame($expected, $encoded);
    }

    public function getInvalidSerializedData()
    {
        yield [
            'Could not encode JSON from array: Type is not supported',
            [fopen('php://temp', 'r+')],
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

        $serializer = JsonSerializer::createFromArray($data);
    }
}
