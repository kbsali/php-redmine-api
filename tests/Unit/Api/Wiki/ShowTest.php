<?php

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Wiki::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     *
     * @param mixed $identifier
     * @param mixed $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($identifier, string $page, ?int $version, string $expectedPath, string $response, $expectedReturn): void
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
        $api = Wiki::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($identifier, $page, $version));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer identifier' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string identifier' => [
                'project',
                'page',
                null,
                '/projects/project/wiki/page.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with integer identifier and version' => [
                5,
                'page',
                22,
                '/projects/5/wiki/page/22.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string identifier and version' => [
                'project',
                'page',
                22,
                '/projects/project/wiki/page/22.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                'string',
                /** @phpstan-ignore smaller.alwaysTrue(Remove this line after release of PHP 8.6) */
                (PHP_VERSION_ID < 80600) ? 'Error decoding body as JSON: Syntax error' : 'Error decoding body as JSON: Syntax error near location 1:1',
            ],
            'false response' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                '',
                false,
            ],
        ];
    }
}
