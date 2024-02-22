<?php

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Project::create
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
        $api = new Project($client);

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
                    'identifier' => 'test-project',
                    'name' => 'Test Project',
                ],
                '/projects.xml',
                '<?xml version="1.0" encoding="UTF-8"?><project><name>Test Project</name><identifier>test-project</identifier></project>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><project></project>',
            ],
            'test with special chars in parameters' => [
                [
                    'identifier' => 'test-project',
                    'name' => 'Test Project with some xml entities: & < > " \' ',
                    'description' => 'Description with some xml entities: & < > " \' ',
                ],
                '/projects.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <project>
                    <name>Test Project with some xml entities: &amp; &lt; &gt; " ' </name>
                    <identifier>test-project</identifier>
                    <description>Description with some xml entities: &amp; &lt; &gt; " ' </description>
                </project>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><project></project>',
            ],
            'test with all parameters' => [
                [
                    'identifier' => 'test-project',
                    'name' => 'Test Project',
                    'tracker_ids' => [10, 5],
                ],
                '/projects.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <project>
                    <name>Test Project</name>
                    <identifier>test-project</identifier>
                    <tracker_ids type="array">
                        <tracker>10</tracker>
                        <tracker>5</tracker>
                    </tracker_ids>
                </project>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><project></project>',
            ],
            'test with custom fields' => [
                [
                    'identifier' => 'test-project',
                    'name' => 'Test Project',
                    'custom_fields' => [
                        [
                            'id' => 123,
                            'name' => 'cf_name',
                            'field_format' => 'string',
                            'value' => [1, 2, 3],
                        ],
                    ],
                ],
                '/projects.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <project>
                    <name>Test Project</name>
                    <identifier>test-project</identifier>
                    <custom_fields type="array">
                        <custom_field name="cf_name" field_format="string" id="123" multiple="true">
                            <value type="array">
                                <value>1</value>
                                <value>2</value>
                                <value>3</value>
                            </value>
                        </custom_field>
                    </custom_fields>
                </project>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><project></project>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/projects.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><project><name>Test Project</name><identifier>test-project</identifier></project>',
                500,
                '',
                ''
            ]
        );

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $return = $api->create(['identifier' => 'test-project', 'name' => 'Test Project']);

        $this->assertSame('', $return);
    }

    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Project($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

        // Perform the tests
        $api->create();
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     */
    public function testCreateThrowsExceptionIfMandatoyParametersAreMissing($parameters)
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Project($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

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
                    'description' => 'project description',
                ],
            ],
            'missing `identifier` parameters' => [
                [
                    'name' => 'Test Project',
                ],
            ],
            'missing `name` parameter' => [
                [
                    'identifier' => 'test-project',
                ],
            ],
        ];
    }
}
