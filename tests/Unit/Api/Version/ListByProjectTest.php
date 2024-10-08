<?php

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\MockClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(Version::class)]
class ListByProjectTest extends TestCase
{
    public function testListByProjectWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject(5));
    }

    public function testListByProjectWithParametersReturnsResponse(): void
    {
        // Test values
        $parameters = [
            'offset' => 10,
            'limit' => 2,
        ];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->any())
            ->method('requestGet')
            ->with('/projects/project-slug/versions.json?limit=2&offset=10')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject('project-slug', $parameters));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier): void
    {
        $api = new Version(MockClient::create());

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\Version::listByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listByProject($projectIdentifier);
    }

    public function testListByProjectThrowsException(): void
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn('');
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByProject(5);
    }
}
