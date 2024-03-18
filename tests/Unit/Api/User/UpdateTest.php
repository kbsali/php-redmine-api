<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse($id, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response
            ]
        );

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test with firstname' => [
                5,
                [
                    'firstname' => 'Raul',
                ],
                '/users/5.xml',
                <<< XML
                <?xml version="1.0"?>
                <user>
                    <id>5</id>
                    <firstname>Raul</firstname>
                </user>
                XML,
                204,
                '',
            ],
            'test with mail' => [
                5,
                [
                    'mail' => 'user@example.com',
                ],
                '/users/5.xml',
                <<< XML
                <?xml version="1.0"?>
                <user>
                    <id>5</id>
                    <mail>user@example.com</mail>
                </user>
                XML,
                204,
                '',
            ],
            'test with custom fields' => [
                5,
                [
                    'custom_fields' => [
                        [
                            'id' => 5,
                            'value' => 'Value 5',
                        ],
                        [
                            'id' => 13,
                            'value' => 'Value 13',
                            'name' => 'CF Name',
                        ],
                    ],
                ],
                '/users/5.xml',
                <<< XML
                <?xml version="1.0"?>
                <user>
                    <id>5</id>
                    <custom_fields type="array">
                        <custom_field id="5">
                            <value>Value 5</value>
                        </custom_field>
                        <custom_field name="CF Name" id="13">
                            <value>Value 13</value>
                        </custom_field>
                    </custom_fields>
                </user>
                XML,
                204,
                '',
            ],
        ];
    }
}
