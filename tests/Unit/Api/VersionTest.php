<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Http\HttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Version` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Version {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Version::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Version::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Version($this->createStub(HttpClient::class));
    }

    /**
     * Test validateSharing().
     *
     * @dataProvider      invalidSharingProvider
     */
    #[DataProvider('invalidSharingProvider')]
    public function testCreateThrowsExceptionWithInvalidSharing(string $sharingValue): void
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Version::fromHttpClient($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing are: none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Data provider for invalid sharing values.
     *
     * @return array<array<mixed>>
     */
    public static function invalidSharingProvider(): array
    {
        return [
            ['all'],
            ['invalid'],
        ];
    }
}
