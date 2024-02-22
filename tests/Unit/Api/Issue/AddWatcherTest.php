<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Issue::addWatcher
 */
class AddWatcherTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreateReturnsCorrectResponse($issueId, $watcherUserId, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                'application/xml',
                $response
            ]
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $return = $api->addWatcher($issueId, $watcherUserId);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test without parameters' => [
                25,
                5,
                '/issues/25/watchers.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <user_id>5</user_id>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }
}
