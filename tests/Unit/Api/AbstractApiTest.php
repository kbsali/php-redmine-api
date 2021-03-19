<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use ReflectionMethod;

/**
 * @coversDefaultClass \Redmine\Api\AbstractApi
 */
class AbstractApiTest extends TestCase
{
    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getLastCallFailedData
     */
    public function testLastCallFailedReturnsCorrectBoolean($statusCode, $expectedBoolean)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseStatusCode')->willReturn($statusCode);

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    public function getLastCallFailedData()
    {
        return [
            [100, true],
            [101, true],
            [102, true],
            [103, true],
            [103, true],
            [200, false],
            [201, false],
            [202, true],
            [203, true],
            [204, true],
            [205, true],
            [206, true],
            [207, true],
            [208, true],
            [226, true],
            [300, true],
            [301, true],
            [302, true],
            [303, true],
            [304, true],
            [305, true],
            [306, true],
            [307, true],
            [308, true],
            [400, true],
            [401, true],
            [402, true],
            [403, true],
            [404, true],
            [405, true],
            [406, true],
            [407, true],
            [408, true],
            [409, true],
            [410, true],
            [411, true],
            [412, true],
            [413, true],
            [414, true],
            [415, true],
            [416, true],
            [417, true],
            [421, true],
            [422, true],
            [423, true],
            [424, true],
            [425, true],
            [426, true],
            [428, true],
            [429, true],
            [431, true],
            [451, true],
            [500, true],
            [501, true],
            [502, true],
            [503, true],
            [504, true],
            [505, true],
            [506, true],
            [507, true],
            [508, true],
            [509, true],
            [510, true],
            [511, true],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getDecodingFromGetMethodData
     */
    public function testDecodingFromGetMethod($response, $contentType, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        if (is_bool($decode)) {
            $this->assertSame($expected, $method->invoke($api, 'path', $decode));
        } else {
            $this->assertSame($expected, $method->invoke($api, 'path'));
        }
    }

    public function getDecodingFromGetMethodData()
    {
        return [
            ['{"foo_bar": 12345}', 'application/json', null, ['foo_bar' => 12345]], // test decode by default
            ['{"foo_bar": 12345}', 'application/json', false, '{"foo_bar": 12345}'],
            ['{"foo_bar": 12345}', 'application/json', true, ['foo_bar' => 12345]],
        ];
    }
}
