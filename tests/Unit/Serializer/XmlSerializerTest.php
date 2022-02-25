<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\SerializerException;
use Redmine\Serializer\XmlSerializer;

class XmlSerializerTest extends TestCase
{
    public function getEncodedToNormalizedData()
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
     * @dataProvider getEncodedToNormalizedData
     */
    public function createFromStringDecodesToExpectedNormalizedData(string $data, $expected)
    {
        $serializer = XmlSerializer::createFromString($data);

        $this->assertSame($expected, $serializer->getNormalized());
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
                <<< END
                <?xml version="1.0"?>
                <issue>
                  <project_id>1</project_id>
                  <subject>Example</subject>
                  <priority_id>4</priority_id>
                </issue>

                END,
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

        $this->assertSame($expected, $dom->saveXML());
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
