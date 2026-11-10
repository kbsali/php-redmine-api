<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Param;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Version::class)]
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

        // Update the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->update($id, $parameters));
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

        // Update the object under test
        $api = Version::fromHttpClient($client);

        FutureMode::enable($api);

        // Perform the tests
        $this->assertSame($response, $api->update($id, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getUpdateData(): array
    {
        return [
            'test without parameters' => [
                5,
                [],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with name' => [
                5,
                ['name' => 'Test version'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>Test version</name></version>',
                204,
                '',
            ],
            'test with status open' => [
                5,
                ['status' => 'open'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>open</status></version>',
                204,
                '',
            ],
            'test with status locked' => [
                5,
                ['status' => 'locked'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>locked</status></version>',
                204,
                '',
            ],
            'test with status closed' => [
                5,
                ['status' => 'closed'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>closed</status></version>',
                204,
                '',
            ],
            'test with status null' => [
                5,
                ['status' => null],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing none' => [
                5,
                ['sharing' => 'none'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>none</sharing></version>',
                204,
                '',
            ],
            'test with sharing descendants' => [
                5,
                ['sharing' => 'descendants'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>descendants</sharing></version>',
                204,
                '',
            ],
            'test with sharing hierarchy' => [
                5,
                ['sharing' => 'hierarchy'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>hierarchy</sharing></version>',
                204,
                '',
            ],
            'test with sharing tree' => [
                5,
                ['sharing' => 'tree'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>tree</sharing></version>',
                204,
                '',
            ],
            'test with sharing system' => [
                5,
                ['sharing' => 'system'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>system</sharing></version>',
                204,
                '',
            ],
            'test with sharing null' => [
                5,
                ['sharing' => null],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
        ];
    }

    public function testUpdateWithInvalidStatusThrowsInvalidParameterException(): void
    {
        // Test values
        $parameters = [
            'name' => 'test',
            'description' => 'Test version description',
            'status' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Version::fromHttpClient($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for status are: open, locked, closed');

        // Perform the tests
        $api->update(5, $parameters);
    }

    public function testUpdateWithInvalidSharingThrowsInvalidParameterException(): void
    {
        // Test values
        $parameters = [
            'name' => 'test',
            'description' => 'Test version description',
            'sharing' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Version::fromHttpClient($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing are: none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->update(5, $parameters);
    }

    public function testUpdateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/versions/5.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                500,
                '',
                '',
            ],
        );

        $api = Version::fromHttpClient($client);

        $this->assertSame('', $api->update(5, []));
    }

    public function testUpdateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/versions/5.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                500,
                '',
                '',
            ],
        );

        $api = Version::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        FutureMode::enable($api);

        $api->update(5, []);
    }

    /**
     * @return array<string,array<mixed>>
     */
    public static function getEmptyValueData(): array
    {
        return [
            'test with status empty string' => [
                5,
                ['status' => ''],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with status false' => [
                5,
                ['status' => false],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing empty string' => [
                5,
                ['sharing' => ''],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing false' => [
                5,
                ['sharing' => false],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
        ];
    }

    /**
     * @param array<mixed> $parameters
     */
    #[DataProvider('getEmptyValueData')]
    public function testUpdateDropsEmptyValues(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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

        $api = Version::fromHttpClient($client);

        $api->update($id, $parameters);
    }

    /**
     * @param array<mixed> $parameters
     */
    #[DataProvider('getEmptyValueData')]
    public function testUpdateWithFutureModeThrowsOnEmptyValues(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->never())->method('request');

        $api = Version::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(\InvalidArgumentException::class);

        $api->update($id, $parameters);
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

        $api = Version::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"description"');

        $api->update(1, ['description' => $value]);
    }

    public function testUpdateWithParamClearSendsEmptyValue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/versions/1.xml',
                'application/xml',
                '<?xml version="1.0"?><version><description></description></version>',
                204,
                '',
                '',
            ],
        );

        $api = Version::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['description' => Param::Clear]));
    }
}
