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
}
