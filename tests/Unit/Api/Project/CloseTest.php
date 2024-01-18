<?php

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Client\Client;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;

/**
 * @covers \Redmine\Api\Project::close
 */
class CloseTest extends TestCase
{
    public function testCloseReturnsResponse()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('POST', $method);
                $this->assertSame('/projects/5/close.xml', $path);
                $this->assertXmlStringEqualsXmlString('', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '',
                    ]
                );
            })
        ;

        $api = new Project($client);

        $this->assertSame('', $api->close(5));
    }
}
