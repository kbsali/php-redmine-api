<?php

namespace Redmine\Tests\Unit\Api\TimeEntry;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntry;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(TimeEntry::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($id, $expectedPath, $response, $expectedReturn)
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
            ]
        );

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($id));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, '/time_entries/5.json', '["API Response"]', ['API Response']],
            'array response with string id' => ['5', '/time_entries/5.json', '["API Response"]', ['API Response']],
            'string response' => [5, '/time_entries/5.json', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, '/time_entries/5.json', '', false],
        ];
    }
}
