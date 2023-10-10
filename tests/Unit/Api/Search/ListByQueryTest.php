<?php

namespace Redmine\Tests\Unit\Api\Search;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Search;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @covers \Redmine\Api\Search::listByQuery
 */
class ListByQueryTest extends TestCase
{
    public function testListByQueryWithoutParametersReturnsResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/search.json?limit=25&offset=0&q=query')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Search($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByQuery('query'));
    }

    public function testListByQueryWithParametersReturnsResponse()
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->any())
            ->method('requestGet')
            ->with('/search.json?limit=25&offset=0&0=not-used&q=query')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Search($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByQuery('query', $parameters));
    }
}
