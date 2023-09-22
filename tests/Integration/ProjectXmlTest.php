<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class ProjectXmlTest extends TestCase
{
    public function testCreateBlank()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
        $this->assertInstanceOf('Redmine\Api\Project', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

        $api->create();
    }

    public function testCreateComplex()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
        $res = $api->create([
            'name' => 'some name',
            'identifier' => 'the_identifier',
            'custom_fields' => [
                [
                    'id' => 123,
                    'name' => 'cf_name',
                    'field_format' => 'string',
                    'value' => [1, 2, 3],
                ],
            ],
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('POST', $response['method']);
        $this->assertEquals('/projects.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <project>
                <name>some name</name>
                <identifier>the_identifier</identifier>
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
            $response['data']
        );
    }

    public function testCreateComplexWithTrackerIds()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
        $res = $api->create([
            'name' => 'some name',
            'identifier' => 'the_identifier',
            'tracker_ids' => [
                1, 2, 3,
            ],
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('POST', $response['method']);
        $this->assertEquals('/projects.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <project>
                <name>some name</name>
                <identifier>the_identifier</identifier>
                <tracker_ids type="array">
                    <tracker>1</tracker>
                    <tracker>2</tracker>
                    <tracker>3</tracker>
                </tracker_ids>
            </project>
            XML,
            $response['data']
        );
    }

    public function testUpdate()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
        $res = $api->update(1, [
            'name' => 'different name',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/projects/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <project>
                <id>1</id>
                <name>different name</name>
            </project>
            XML,
            $response['data']
        );
    }
}
