<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(TimeEntryActivity::class)]
class TimeEntryActivityTest extends TestCase
{
    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new TimeEntryActivity(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\TimeEntryActivity::all()` is deprecated since v2.4.0, use `Redmine\Api\TimeEntryActivity::list()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->all();
    }

    /**
     * Test all().
     *
     * @dataProvider getAllData
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponse($response, $responseType, $expectedResponse)
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/enumerations/time_entry_activities.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all());
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
    public function testAllReturnsClientGetResponseWithParameters()
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
                    $this->stringStartsWith('/enumerations/time_entry_activities.json'),
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
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    public function testListingReturnsNameIdArray()
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->createMock(Client::class);
        $client->expects($this->atLeastOnce())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertSame($expectedReturn, $api->listing());
    }

    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    public function testGetIdByNameMakesGetRequest()
    {
        $response = '{"time_entry_activities":[{"id":2,"name":"TimeEntryActivities 2"}]}';

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertFalse($api->getIdByName('TimeEntryActivities 1'));
        $this->assertSame(2, $api->getIdByName('TimeEntryActivities 2'));
    }
}
