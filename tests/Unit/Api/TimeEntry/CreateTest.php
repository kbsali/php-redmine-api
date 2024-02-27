<?php

namespace Redmine\Tests\Unit\Api\TimeEntry;

use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntry;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\TimeEntry::create
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
        $api = new TimeEntry($client);

        // Perform the tests
        $return = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with issue_id' => [
                ['issue_id' => 5, 'hours' => 5.25],
                '/time_entries.xml',
                '<?xml version="1.0" encoding="UTF-8"?><time_entry><issue_id>5</issue_id><hours>5.25</hours></time_entry>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><time_entry></time_entry>',
            ],
            'test with project_id' => [
                ['project_id' => 5, 'hours' => 5.25],
                '/time_entries.xml',
                '<?xml version="1.0" encoding="UTF-8"?><time_entry><project_id>5</project_id><hours>5.25</hours></time_entry>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><time_entry></time_entry>',
            ],
            'test with all parameters' => [
                [
                    'issue_id' => '15',
                    'project_id' => '25',
                    'hours' => '5.25',
                    'comments' => 'some text with xml entities: & < > " \' ',
                    'custom_fields' => [
                        [
                            'id' => 1,
                            'name' => 'Affected version',
                            'value' => '1.0.1',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Resolution',
                            'value' => 'Fixed',
                        ],
                    ],
                ],
                '/time_entries.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <time_entry>
                    <issue_id>15</issue_id>
                    <project_id>25</project_id>
                    <hours>5.25</hours>
                    <comments>some text with xml entities: &amp; &lt; &gt; " ' </comments>
                    <custom_fields type="array">
                        <custom_field id="1" name="Affected version">
                            <value>1.0.1</value>
                        </custom_field>
                        <custom_field id="2" name="Resolution">
                            <value>Fixed</value>
                        </custom_field>
                    </custom_fields>
                </time_entry>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><time_entry></time_entry>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/time_entries.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><time_entry><issue_id>5</issue_id><hours>5.25</hours></time_entry>',
                500,
                '',
                ''
            ]
        );

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $return = $api->create(['issue_id' => 5, 'hours' => 5.25]);

        $this->assertSame('', $return);
    }

    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new TimeEntry($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');

        // Perform the tests
        $this->assertSame($response, $api->create());
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     */
    public function testCreateThrowsExceptionIfValueIsMissingInParameters($parameters)
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new TimeEntry($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
     */
    public static function incompleteCreateParameterProvider(): array
    {
        return [
            'missing all mandatory parameters' => [
                [
                    'id' => '5',
                ],
            ],
            'missing `issue_id` or `project_id` parameters' => [
                [
                    'hours' => '5.25',
                ],
            ],
            'missing `hours` parameter' => [
                [
                    'issue_id' => 5,
                    'project_id' => 5,
                ],
            ],
        ];
    }
}
