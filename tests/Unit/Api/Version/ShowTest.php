<?php

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Version::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     *
     * @param mixed $version
     * @param mixed $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($version, string $expectedPath, string $response, $expectedReturn): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($version));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [
                5,
                '/versions/5.json',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string id' => [
                '5',
                '/versions/5.json',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                '/versions/5.json',
                'string',
                /** @phpstan-ignore smaller.alwaysTrue(Remove this line after release of PHP 8.6) */
                (PHP_VERSION_ID < 80600) ? 'Error decoding body as JSON: Syntax error' : 'Error decoding body as JSON: Syntax error near location 1:1',
            ],
            'false response' => [
                5,
                '/versions/5.json',
                '',
                false,
            ],
        ];
    }
}
