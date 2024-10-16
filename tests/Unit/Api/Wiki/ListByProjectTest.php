<?php

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\MockClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(Wiki::class)]
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
            ->with('/projects/5/wiki/index.json')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Wiki($client);

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
            ->with('/projects/project-slug/wiki/index.json?limit=2&offset=10')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject('project-slug', $parameters));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier): void
    {
        $api = new Wiki(MockClient::create());

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\Wiki::listByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listByProject($projectIdentifier);
    }

    public function testListByProjectThrowsException(): void
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/projects/5/wiki/index.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn('');
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Wiki($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByProject(5);
    }
}
