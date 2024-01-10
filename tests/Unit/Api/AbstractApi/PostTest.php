<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\AbstractApi::post
 */
class PostTest extends TestCase
{
    public function testPostWithHttpClient()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);
        $response->expects($this->any())->method('getContentType')->willReturn('application/xml');
        $response->expects($this->any())->method('getBody')->willReturn('<?xml version="1.0"?><issue/>');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))->method('request')->with('POST', 'path.xml', '')->willReturn($response);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'post');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><issue/>', $return->asXML());
    }

    /**
     * @dataProvider getXmlDecodingFromPostMethodData
     */
    public function testXmlDecodingFromPostMethod($response, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'post');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
    }

    public static function getXmlDecodingFromPostMethodData(): array
    {
        return [
            'decode by default' => ['<?xml version="1.0"?><issue/>', '<?xml version="1.0"?><issue/>'], // test decode by default
        ];
    }
}
