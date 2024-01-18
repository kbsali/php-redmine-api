<?php

namespace Redmine\Tests\Unit\Api\Project;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;

/**
 * @covers \Redmine\Api\Project::close
 */
class CloseTest extends TestCase
{
    public function testCloseReturnsTrue()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/projects/5/close.xml', $path);
                $this->assertSame('', $body);

                return $this->createConfiguredMock(Response::class, [
                    'getStatusCode' => 204,
                    'getContentType' => 'application/xml',
                    'getBody' => '',
                ]);
            })
        ;

        $api = new Project($client);

        $this->assertTrue($api->close(5));
    }

    public function testCloseThrowsUnexpectedResponseException()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/projects/5/close.xml', $path);
                $this->assertSame('', $body);

                return $this->createConfiguredMock(Response::class, [
                    'getStatusCode' => 403,
                    'getContentType' => 'application/xml',
                    'getBody' => '',
                ]);
            })
        ;

        $api = new Project($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with the status code 403');

        $api->close(5);
    }

    public function testCloseWithoutIntOrStringThrowsInvalidArgumentException()
    {
        $client = $this->createMock(HttpClient::class);

        $api = new Project($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\Project::close(): Argument #1 ($projectIdentifier) must be of type int or string');

        // provide a wrong project identifier
        $api->close(true);
    }
}
