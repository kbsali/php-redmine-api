<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Wiki::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse($id, $page, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $page, $parameters));
    }

    public static function getUpdateData(): array
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
                204,
                '',
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
                204,
                '',
            ],
        ];
    }
}
