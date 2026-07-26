<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Wiki::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse(int $id, string $page, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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
                $response,
            ],
        );

        // Create the object under test
        $api = Wiki::fromHttpClient($client);

        // Perform the tests
        $return = $api->create($id, $page, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    /**
     * @return array<array<mixed>>
     */
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

    public function testCreateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/5/wiki/test.xml',
                'application/xml',
                '<?xml version="1.0"?><wiki_page/>',
                500,
                '',
                'error',
            ],
        );

        $api = Wiki::fromHttpClient($client);

        $this->assertSame('error', $api->create(5, 'test', []));
    }

    public function testCreateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/5/wiki/test.xml',
                'application/xml',
                '<?xml version="1.0"?><wiki_page/>',
                500,
                '',
                '',
            ],
        );

        $api = Wiki::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->create(5, 'test', []);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}
