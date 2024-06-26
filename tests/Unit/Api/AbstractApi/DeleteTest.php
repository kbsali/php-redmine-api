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

#[CoversClass(AbstractApi::class)]
class DeleteTest extends TestCase
{
    public function testDeleteWithHttpClient()
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
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path.xml');

        $this->assertSame('<?xml version="1.0"?><issue/>', $return);
    }

    /**
     * @dataProvider getXmlDecodingFromDeleteMethodData
     */
    #[DataProvider('getXmlDecodingFromDeleteMethodData')]
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
