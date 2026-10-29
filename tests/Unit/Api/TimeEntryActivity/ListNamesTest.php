<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\TimeEntryActivity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(TimeEntryActivity::class)]
class ListNamesTest extends TestCase
{
    /**
     * @dataProvider getListNamesData
     *
     * @param array<mixed> $expectedResponse
     */
    #[DataProvider('getListNamesData')]
    public function testListNamesReturnsCorrectResponse(string $expectedPath, int $responseCode, string $response, array $expectedResponse): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                $responseCode,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = TimeEntryActivity::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getListNamesData(): array
    {
        return [
            'test without time entry activities' => [
                '/enumerations/time_entry_activities.json',
                201,
                <<<JSON
                {
                    "time_entry_activities": []
                }
                JSON,
                [],
            ],
            'test with multiple time entry activities' => [
                '/enumerations/time_entry_activities.json',
                201,
                <<<JSON
                {
                    "time_entry_activities": [
                        {"id": 7, "name": "TimeEntryActivity 3"},
                        {"id": 8, "name": "TimeEntryActivity 2"},
                        {"id": 9, "name": "TimeEntryActivity 1"}
                    ]
                }
                JSON,
                [
                    7 => "TimeEntryActivity 3",
                    8 => "TimeEntryActivity 2",
                    9 => "TimeEntryActivity 1",
                ],
            ],
        ];
    }

    public function testListNamesCallsHttpClientOnlyOnce(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "time_entry_activities": [
                        {
                            "id": 1,
                            "name": "TimeEntryActivity 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = TimeEntryActivity::fromHttpClient($client);

        // Perform the tests
        $firstResult = $api->listNames();
        $secondResult = $api->listNames();
        $thirdResult = $api->listNames();

        $this->assertSame([1 => 'TimeEntryActivity 1'], $firstResult);
        $this->assertSame([1 => 'TimeEntryActivity 1'], $secondResult);
        $this->assertSame([1 => 'TimeEntryActivity 1'], $thirdResult);
    }

    public function testListNamesAfterUnexpectedResponseRequestsDataAgain(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"time_entry_activities":[{"id":1,"name":"Activity 1"}]}',
            ],
        );

        // Create the object under test
        $api = TimeEntryActivity::fromHttpClient($client);

        // Perform the tests
        try {
            $api->listNames();
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame('', $e->getResponse()->getContent());
        }

        $this->assertSame([1 => 'Activity 1'], $api->listNames());
    }
}
