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
 * @covers \Redmine\Api\AbstractApi::delete
 */
class DeleteTest extends TestCase
{
    public function testDeleteWithHttpClient()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->any())->method('getStatusCode')->willReturn(200);
        $response->expects($this->any())->method('getContentType')->willReturn('application/xml');
        $response->expects($this->any())->method('getContent')->willReturn('<?xml version="1.0"?><issue/>');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))->method('request')->with('DELETE', 'path.xml', '')->willReturn($response);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'delete');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml');

        $this->assertSame('<?xml version="1.0"?><issue/>', $return);
    }

    /**
     * @dataProvider getXmlDecodingFromDeleteMethodData
     */
    public function testXmlDecodingFromDeleteMethod($response, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'delete');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml');

        $this->assertSame($expected, $return);
    }

    public static function getXmlDecodingFromDeleteMethodData(): array
    {
        return [
            'no decode by default' => ['<?xml version="1.0"?><issue/>', '<?xml version="1.0"?><issue/>'],
        ];
    }
}
