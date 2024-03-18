<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Wiki::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($id, $page, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                'application/xml',
                $response
            ]
        );

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $return = $api->create($id, $page, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test without params' => [
                5,
                'test',
                [],
                '/projects/5/wiki/test.xml',
                <<< XML
                <?xml version="1.0"?>
                <wiki_page/>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><wiki_page></wiki_page>',
            ],
            'test without special char in page name' => [
                5,
                'about page',
                [],
                '/projects/5/wiki/about+page.xml',
                <<< XML
                <?xml version="1.0"?>
                <wiki_page/>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><wiki_page></wiki_page>',
            ],
            'test with params' => [
                5,
                'test',
                [
                    'title' => 'Test Wikipage with xml entities: & < > " \' ',
                    'comments' => 'Initial Edit with xml entities: & < > " \' ',
                    'text' => 'Some page text with xml entities: & < > " \' ',
                ],
                '/projects/5/wiki/test.xml',
                <<< XML
                <?xml version="1.0"?>
                <wiki_page>
                    <text>Some page text with xml entities: &amp; &lt; &gt; " ' </text>
                    <comments>Initial Edit with xml entities: &amp; &lt; &gt; " ' </comments>
                    <title>Test Wikipage with xml entities: &amp; &lt; &gt; " ' </title>
                </wiki_page>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><wiki_page></wiki_page>',
            ],
        ];
    }
}
