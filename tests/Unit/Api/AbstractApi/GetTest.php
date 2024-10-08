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
class GetTest extends TestCase
{
    public function testGetWithHttpClient(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                'path.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"foo_bar": 12345}',
            ],
        );

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        $this->assertSame(
            ['foo_bar' => 12345],
            $method->invoke($api, 'path.json'),
        );
    }

    /**
     * @dataProvider getJsonDecodingFromGetMethodData
     */
    #[DataProvider('getJsonDecodingFromGetMethodData')]
    public function testJsonDecodingFromGetMethod($response, $decode, $expected): void
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/json');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        if (is_bool($decode)) {
            $this->assertSame($expected, $method->invoke($api, 'path', $decode));
        } else {
            $this->assertSame($expected, $method->invoke($api, 'path'));
        }
    }

    public static function getJsonDecodingFromGetMethodData(): array
    {
        return [
            'test decode by default' => ['{"foo_bar": 12345}', null, ['foo_bar' => 12345]],
            'test decode by default, JSON decode: false' => ['{"foo_bar": 12345}', false, '{"foo_bar": 12345}'],
            'test decode by default, JSON decode: true' => ['{"foo_bar": 12345}', true, ['foo_bar' => 12345]],
            'Empty body, JSON decode: false' => ['', false, false],
            'Empty body, JSON decode: true' => ['', true, false],
            'test invalid JSON' => ['{"foo_bar":', true, 'Error decoding body as JSON: Syntax error'],
        ];
    }

    /**
     * @dataProvider getXmlDecodingFromGetMethodData
     */
    #[DataProvider('getXmlDecodingFromGetMethodData')]
    public function testXmlDecodingFromGetMethod($response, $decode, $expected): void
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path', $decode);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
    }

    public static function getXmlDecodingFromGetMethodData(): array
    {
        return [
            'decode by default' => ['<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'], // test decode by default
            'decode true' => ['<?xml version="1.0"?><issue/>', true, '<?xml version="1.0"?><issue/>'],
            'decode false' => ['<?xml version="1.0"?><issue/>', false, '<?xml version="1.0"?><issue/>'], // test that xml decoding will be always happen
        ];
    }
}
