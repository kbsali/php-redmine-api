<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Param;
use Redmine\Api\User;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(User::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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
                $response,
            ],
        );

        // Create the object under test
        $api = User::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @dataProvider getUpdateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateWithFutureModeReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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
                $response,
            ],
        );

        // Create the object under test
        $api = User::fromHttpClient($client);

        FutureMode::enable($api);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
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

    public function testUpdateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/users/5.xml',
                'application/xml',
                '<?xml version="1.0"?><user><id>5</id></user>',
                500,
                '',
                '',
            ],
        );

        $api = User::fromHttpClient($client);

        $this->assertSame('', $api->update(5, []));
    }

    public function testUpdateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/users/5.xml',
                'application/xml',
                '<?xml version="1.0"?><user><id>5</id></user>',
                500,
                '',
                '',
            ],
        );

        $api = User::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        FutureMode::enable($api);

        $api->update(5, []);
    }

    /**
     * @return array<string,array{mixed}>
     */
    public static function getEmptyValues(): array
    {
        return [
            'empty string' => [''],
            'empty array' => [[]],
            'false' => [false],
        ];
    }

    #[DataProvider('getEmptyValues')]
    public function testUpdateWithFutureModeThrowsOnEmptyValue(mixed $value): void
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->never())->method('request');

        $api = User::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"firstname"');

        $api->update(1, ['firstname' => $value]);
    }

    public function testUpdateWithParamClearSendsEmptyValue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/users/1.xml',
                'application/xml',
                '<?xml version="1.0"?><user><id>1</id><firstname></firstname></user>',
                204,
                '',
                '',
            ],
        );

        $api = User::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['firstname' => Param::Clear]));
    }
}
