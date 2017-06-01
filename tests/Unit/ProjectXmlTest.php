<?php

namespace Redmine\Tests\Unit;

use Redmine\Tests\Fixtures\MockClient as TestClient;

class ProjectXmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestClient
     */
    private $client;

    public function setup()
    {
        $this->client = new TestClient('http://test.local', 'asdf');
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateBlank()
    {
        $api = $this->client->project;
        $this->assertInstanceOf('Redmine\Api\Project', $api);

        $api->create();
    }

    public function testCreateComplex()
    {
        $api = $this->client->project;
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

        $xml = '<?xml version="1.0"?>
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
</project>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testCreateComplexWithTrackerIds()
    {
        $api = $this->client->project;
        $res = $api->create([
            'name' => 'some name',
            'identifier' => 'the_identifier',
            'tracker_ids' => [
                1, 2, 3,
            ],
        ]);

        $xml = '<?xml version="1.0"?>
<project>
    <name>some name</name>
    <identifier>the_identifier</identifier>
    <tracker_ids type="array">
        <tracker>1</tracker>
        <tracker>2</tracker>
        <tracker>3</tracker>
    </tracker_ids>
</project>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $api = $this->client->project;
        $res = $api->update(1, [
            'name' => 'different name',
        ]);

        $xml = '<?xml version="1.0"?>
<project>
    <id>1</id>
    <name>different name</name>
</project>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    private function formatXml($xml)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML((new \SimpleXMLElement($xml))->asXML());

        return $dom->saveXML();
    }
}
