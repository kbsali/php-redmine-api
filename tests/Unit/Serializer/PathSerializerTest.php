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
                'foo.json'
            ],
            [
                'foo.xml?format=xml',
                [],
                'foo.xml?format=xml'
            ],
            [
                'foo.xml',
                [
                    'format' => 'xml',
                ],
                'foo.xml?format=xml'
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getPathData
     */
    public function getPathShouldReturn(string $path, array $params, string $expected)
    {
        $serializer = PathSerializer::create($path, $params);

        $this->assertSame($expected, $serializer->getPath());
    }
}
