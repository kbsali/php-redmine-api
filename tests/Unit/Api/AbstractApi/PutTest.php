<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use ReflectionMethod;
use SimpleXMLElement;

#[CoversClass(AbstractApi::class)]
class PutTest extends TestCase
{
    public function testPutWithHttpClient()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                'path.xml',
                'application/xml',
                '',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue/>',
            ]
        );

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'put');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><issue/>', $return->asXML());
    }

    /**
     * @dataProvider getXmlDecodingFromPutMethodData
     */
    #[DataProvider('getXmlDecodingFromPutMethodData')]
    public function testXmlDecodingFromPutMethod($response, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'put');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
    }

    public static function getXmlDecodingFromPutMethodData(): array
    {
        return [
            'decode by default' => ['<?xml version="1.0"?><issue/>', '<?xml version="1.0"?><issue/>'], // test decode by default
        ];
    }
}
