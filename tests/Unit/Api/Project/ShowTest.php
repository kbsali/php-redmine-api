<?php

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($identifier, array $params, $expectedPath, $response, $expectedReturn)
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
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($identifier, $params));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string id' => [
                'test',
                [],
                '/projects/test.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'test include parameter as array' => [
                'test',
                [
                    'include' => ['resource1', 'resource2'],
                ],
                '/projects/test.json?include=resource1%2Cresource2',
                '["API Response"]',
                ['API Response'],
            ],
            'test include parameter as string will be ignored' => [
                'test',
                [
                    'include' => 'resource1,resource2',
                ],
                '/projects/test.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with parameters' => [
                5,
                [
                    'parameter1',
                    'parameter2',
                    'enabled_modules',
                ],
                '/projects/5.json?0=parameter1&1=parameter2&2=enabled_modules&include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'string',
                'Error decoding body as JSON: Syntax error',
            ],
            'false response' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '',
                false,
            ],
        ];
    }
}
