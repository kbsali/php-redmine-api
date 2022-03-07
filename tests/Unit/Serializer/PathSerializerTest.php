<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Redmine\Serializer\PathSerializer;

class PathSerializerTest extends TestCase
{
    public function getPathData()
    {
        return [
            [
                'foo.json',
                [],
                'foo.json',
            ],
            [
                'foo.xml?format=xml',
                [],
                'foo.xml?format=xml',
            ],
            [
                'foo.xml',
                [
                    'format' => 'xml',
                ],
                'foo.xml?format=xml',
            ],
            // Test for #154: fix http_build_query encoding array values with numeric keys
            [
                '/time_entries.json',
                [
                    'f' => ['spent_on'],
                    'op' => ['spent_on' => '><'],
                    'v' => [
                        'spent_on' => [
                            '2016-01-18',
                            '2016-01-22',
                        ],
                    ],
                ],
                '/time_entries.json?f%5B%5D=spent_on&op%5Bspent_on%5D=%3E%3C&v%5Bspent_on%5D%5B%5D=2016-01-18&v%5Bspent_on%5D%5B%5D=2016-01-22',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getPathData
     */
    public function getPathShouldReturnExpectedString(string $path, array $params, string $expected)
    {
        $serializer = PathSerializer::create($path, $params);

        $this->assertSame($expected, $serializer->getPath());
    }
}
