<?php

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Wiki::show
 */
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    public function testShowReturnsCorrectResponse($identifier, $page, $version, $expectedPath, $response, $expectedReturn)
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
                $response
            ]
        );

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($identifier, $page, $version));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer identifier' => [5, 'page', null, '/projects/5/wiki/page.json?include=attachments', '["API Response"]', ['API Response']],
            'array response with string identifier' => ['project', 'page', null, '/projects/project/wiki/page.json?include=attachments', '["API Response"]', ['API Response']],
            'array response with integer identifier and version' => [5, 'page', 22, '/projects/5/wiki/page/22.json?include=attachments', '["API Response"]', ['API Response']],
            'array response with string identifier and version' => ['project', 'page', 22, '/projects/project/wiki/page/22.json?include=attachments', '["API Response"]', ['API Response']],
            'string response' => [5, 'page', null, '/projects/5/wiki/page.json?include=attachments', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, 'page', null, '/projects/5/wiki/page.json?include=attachments', '', false],
        ];
    }
}
