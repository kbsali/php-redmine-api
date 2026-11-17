<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Tracker;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Tracker::class)]
class TrackerTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Tracker` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Tracker {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Tracker::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Tracker::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Tracker($this->createStub(HttpClient::class));
    }

    /**
     * Test getIdByName().
     */
    public function testGetIdByNameMakesGetRequest(): void
    {
        $response = '{"trackers":[{"id":5,"name":"Tracker 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Tracker::fromHttpClient($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('Tracker 1'));
        $this->assertSame(5, $api->getIdByName('Tracker 5'));
    }

    public function testGgetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"trackers":[{"id":1,"name":"Tracker 1"},{"id":5,"name":"Tracker 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Tracker::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Tracker::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\Tracker::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName('Tracker 5');
    }
}
