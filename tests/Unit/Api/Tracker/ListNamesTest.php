<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Tracker;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Tracker;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Tracker::class)]
class ListNamesTest extends TestCase
{
    /**
     * @dataProvider getListNamesData
     */
    #[DataProvider('getListNamesData')]
    public function testListNamesReturnsCorrectResponse($expectedPath, $responseCode, $response, $expectedResponse)
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
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without trackers' => [
                '/trackers.json',
                201,
                <<<JSON
                {
                    "trackers": []
                }
                JSON,
                [],
            ],
            'test with multiple trackers' => [
                '/trackers.json',
                201,
                <<<JSON
                {
                    "trackers": [
                        {"id": 7, "name": "Tracker 3"},
                        {"id": 8, "name": "Tracker 2"},
                        {"id": 9, "name": "Tracker 1"}
                    ]
                }
                JSON,
                [
                    7 => "Tracker 3",
                    8 => "Tracker 2",
                    9 => "Tracker 1",
                ],
            ],
        ];
    }

    public function testListNamesCallsHttpClientOnlyOnce()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "trackers": [
                        {
                            "id": 1,
                            "name": "Tracker 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame([1 => 'Tracker 1'], $api->listNames());
        $this->assertSame([1 => 'Tracker 1'], $api->listNames());
        $this->assertSame([1 => 'Tracker 1'], $api->listNames());
    }
}
