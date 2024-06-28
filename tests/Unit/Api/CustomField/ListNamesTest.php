<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\CustomField;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(CustomField::class)]
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
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without custom fields' => [
                '/custom_fields.json',
                201,
                <<<JSON
                {
                    "custom_fields": []
                }
                JSON,
                [],
            ],
            'test with multiple custom fields' => [
                '/custom_fields.json',
                201,
                <<<JSON
                {
                    "custom_fields": [
                        {"id": 7, "name": "CustomField 3"},
                        {"id": 8, "name": "CustomField 2"},
                        {"id": 9, "name": "CustomField 1"}
                    ]
                }
                JSON,
                [
                    7 => "CustomField 3",
                    8 => "CustomField 2",
                    9 => "CustomField 1",
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
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "custom_fields": [
                        {
                            "id": 1,
                            "name": "CustomField 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame([1 => 'CustomField 1'], $api->listNames());
        $this->assertSame([1 => 'CustomField 1'], $api->listNames());
        $this->assertSame([1 => 'CustomField 1'], $api->listNames());
    }
}
