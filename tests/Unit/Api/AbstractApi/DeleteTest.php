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

#[CoversClass(AbstractApi::class)]
class DeleteTest extends TestCase
{
    public function testDeleteWithHttpClient(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                'path.xml',
                'application/xml',
                '',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue/>',
            ],
        );

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'delete');

        // Perform the tests
        $return = $method->invoke($api, 'path.xml');

        $this->assertSame('<?xml version="1.0"?><issue/>', $return);
    }

    /**
     * @dataProvider getXmlDecodingFromDeleteMethodData
     */
    #[DataProvider('getXmlDecodingFromDeleteMethodData')]
    public function testXmlDecodingFromDeleteMethod(string $response, string $expected): void
    {
        $client = $this->createStub(HttpClient::class);
        $client->method('request')->willReturn(HttpFactory::makeResponse(200, 'application/xml', $response));

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'delete');

        // Perform the tests
        $return = $method->invoke($api, 'path.xml');

        $this->assertSame($expected, $return);
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getXmlDecodingFromDeleteMethodData(): array
    {
        return [
            'no decode by default' => ['<?xml version="1.0"?><issue/>', '<?xml version="1.0"?><issue/>'],
        ];
    }
}
