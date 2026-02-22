<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Client\Client;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Attachment::class)]
class AttachmentTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Attachment` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Attachment {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Attachment::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Attachment::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Attachment($this->createStub(HttpClient::class));
    }

    public function testLastCallFailedWithoutPreviousRequestReturnsTrue(): void
    {
        $api = Attachment::fromHttpClient($this->createStub(HttpClient::class));

        // Perform the tests
        $this->assertTrue($api->lastCallFailed());
    }

    /**
     * Test lastCallFailed().
     *
     * @dataProvider responseCodeProvider
     */
    #[DataProvider('responseCodeProvider')]
    public function testLastCallFailedReturnsCorrectValue(int $responseCode, bool $hasFailed): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/attachments/1.json',
                'application/json',
                '',
                $responseCode,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);
        $api->show(1);

        // Perform the tests
        $this->assertSame($hasFailed, $api->lastCallFailed());
    }

    /**
     * Data provider for response code and expected state.
     *
     * @return array[]
     */
    public static function responseCodeProvider(): array
    {
        return [
            [199, true],
            [200, false],
            [201, false],
            [202, true],
            [400, true],
            [403, true],
            [404, true],
            [500, true],
        ];
    }
}
