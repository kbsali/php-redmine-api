<?php

namespace Redmine\Tests\Unit\Api\Project;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;

/**
 * @covers \Redmine\Api\Project::archive
 */
class ArchiveTest extends TestCase
{
    public function testArchiveReturnsTrue()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/projects/5/archive.xml', $path);
                $this->assertSame('', $body);

                return $this->createConfiguredMock(Response::class, [
                    'getStatusCode' => 204,
                    'getContentType' => 'application/xml',
                    'getBody' => '',
                ]);
            })
        ;

        $api = new Project($client);

        $this->assertTrue($api->archive(5));
    }

    public function testArchiveWithoutIntOrStringThrowsInvalidArgumentException()
    {
        $client = $this->createMock(HttpClient::class);

        $api = new Project($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\Project::archive(): Argument #1 ($projectIdentifier) must be of type int or string');

        // provide a wrong project identifier
        $api->archive(true);
    }
}
