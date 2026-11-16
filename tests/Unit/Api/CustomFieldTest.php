<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(CustomField::class)]
class CustomFieldTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\CustomField` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends CustomField {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\CustomField::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\CustomField::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new CustomField($this->createStub(HttpClient::class));
    }

    /**
     * Test getIdByName().
     */
    public function testGetIdByNameMakesGetRequest(): void
    {
        // Test values
        $response = '{"custom_fields":[{"id":5,"name":"CustomField 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('CustomField 1'));
        $this->assertSame(5, $api->getIdByName('CustomField 5'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = CustomField::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\CustomField::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\CustomField::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName('CustomField 5');
    }
}
