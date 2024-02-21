<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Issue::create
 */
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreateReturnsCorrectResponse($parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
        $return = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with minimal parameters' => [
                [
                    'subject' => 'Issue subject',
                    'project_id' => 1,
                    'tracker_id' => 2,
                    'priority_id' => 3,
                    'status_id' => 4,
                ],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue>
                    <subject>Issue subject</subject>
                    <project_id>1</project_id>
                    <priority_id>3</priority_id>
                    <status_id>4</status_id>
                    <tracker_id>2</tracker_id>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }
}
