<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\XmlSerializer;

class XmlSerializerTest extends TestCase
{
    public function getNormalizedAndEncodedData()
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
                <<< END
                <?xml version="1.0" encoding="UTF-8"?>
                <issues type="array" count="1640">
                  <issue>
                    <id>4326</id>
                  </issue>
                  <issue>
                    <id>4325</id>
                  </issue>
                </issues>
                END,
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
     * @dataProvider getNormalizedAndEncodedData
     */
    public function createFromStringDecodesToExpectedNormalizedData(string $data, $expected)
    {
        $serializer = XmlSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
    }

    public function getInvalidEncodedData()
    {
        return [
            [''],
            ['<?xml version="1.0" encoding="UTF-8"?>'],
            ['<?xml version="1.0" encoding="UTF-8"?><>'],
            ['<?xml version="1.0" encoding="UTF-8"?><a>'],
            ['<?xml version="1.0" encoding="UTF-8"?></>'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getInvalidEncodedData
     */
    public function createFromStringWithInvalidStringThrowsException(string $data)
    {
        $this->expectException(SerializerException::class);

        $serializer = XmlSerializer::createFromString($data);
    }
}
