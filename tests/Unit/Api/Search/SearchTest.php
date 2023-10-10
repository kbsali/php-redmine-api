<?php

namespace Redmine\Tests\Unit\Api\Search;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Search;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @covers \Redmine\Api\Search::search
 */
class SearchTest extends TestCase
{
    public function testSearchTriggersDeprecationWarning()
    {
        $api = new Search(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Search::search()` is deprecated since v2.4.0, use `Redmine\Api\Search::listByQuery()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->search('query');
    }

    public function testSearchReturnsClientGetResponse()
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
        $this->assertSame($expectedReturn, $api->search('query'));
    }

    public function testSearchReturnsClientGetResponseWithParameters()
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
        $this->assertSame($expectedReturn, $api->search('query', $parameters));
    }
}
