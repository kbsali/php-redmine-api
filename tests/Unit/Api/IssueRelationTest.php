<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(IssueRelation::class)]
class IssueRelationTest extends TestCase
{
    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = new IssueRelation(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\IssueRelation::all()` is deprecated since v2.4.0, use `Redmine\Api\IssueRelation::listByIssueId()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->all(5);
    }

    /**
     * Test all().
     *
     * @dataProvider getAllData
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponseWithProject($response, $responseType, $expectedResponse): void
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/issues/5/relations.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all(5));
    }

    public static function getAllData(): array
    {
        return [
            'array response' => ['["API Response"]', 'application/json', ['API Response']],
            'string response' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'false response' => ['', 'application/json', false],
        ];
    }

    /**
     * Test all().
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject(): void
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5/relations.json'),
                    $this->stringContains('not-used'),
                ),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5, $parameters));
    }
}
