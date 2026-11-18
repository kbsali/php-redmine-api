<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use ReflectionMethod;
use SimpleXMLElement;

#[CoversClass(AbstractApi::class)]
class PutTest extends TestCase
{
    public function testPutWithHttpClient(): void
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
            ],
        );

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'put');

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><issue/>', $return->asXML());
    }

    /**
     * @dataProvider getXmlDecodingFromPutMethodData
     */
    #[DataProvider('getXmlDecodingFromPutMethodData')]
    public function testXmlDecodingFromPutMethod(string $response, string $expected): void
    {
        $client = $this->createStub(HttpClient::class);
        $client->method('request')->willReturn(HttpFactory::makeResponse(200, 'application/xml', $response));

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'put');

        // Perform the tests
        $return = $method->invoke($api, 'path.xml', '');

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getXmlDecodingFromPutMethodData(): array
    {
        return [
            'decode by default' => ['<?xml version="1.0"?><issue/>', '<?xml version="1.0"?><issue/>'], // test decode by default
        ];
    }
}
