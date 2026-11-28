<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Membership::class)]
final class UpdateTest extends TestCase
{
    /**
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
        $api = Membership::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getUpdateData(): \Iterator
    {
        yield 'test with one role_id' => [
            5,
            ['user_id' => 4, 'role_ids' => [2]],
            '/memberships/5.xml',
            '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids type="array"><role_id>2</role_id></role_ids>
                </membership>',
            204,
            '',
        ];
        yield 'test with multiple role_ids' => [
            5,
            ['user_id' => 4, 'role_ids' => [5, 6]],
            '/memberships/5.xml',
            '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids type="array"><role_id>5</role_id><role_id>6</role_id></role_ids></membership>',
            204,
            '',
        ];
    }

    public function testUpdateThrowsExceptionWithEmptyParameters(): void
    {
        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Membership::fromHttpClient($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `role_ids`');

        // Perform the tests
        $api->update(5);
    }

    /**
     * @param array<mixed> $parameters
     */
    #[DataProvider('incompleteUpdateParameterProvider')]
    public function testUpdateThrowsExceptionIfMandatoyParametersAreMissing(array $parameters): void
    {
        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Membership::fromHttpClient($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `role_ids`');

        // Perform the tests
        $api->update(5, $parameters);
    }

    public function testUpdateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/memberships/5.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids>2</role_ids></membership>',
                500,
                '',
                '',
            ],
        );

        $api = Membership::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);


        $api->update(5, ['user_id' => 4, 'role_ids' => 2]);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return \Iterator<int|string, mixed[]>
     */
    public static function incompleteUpdateParameterProvider(): \Iterator
    {
        yield 'missing all mandatory parameters' => [
            [],
        ];
        yield 'missing `role_ids` parameter' => [
            [
                'user_id' => 4,
            ],
        ];
    }

    /**
     * @return \Iterator<string, array{mixed}>
     */
    public static function getEmptyValues(): \Iterator
    {
        yield 'empty string' => [''];
        yield 'empty array' => [[]];
        yield 'false' => [false];
    }

    #[DataProvider('getEmptyValues')]
    public function testUpdateThrowsOnEmptyValue(mixed $value): void
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->never())->method('request');

        $api = Membership::fromHttpClient($client);


        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"role_ids"');

        $api->update(1, ['role_ids' => $value]);
    }
}
