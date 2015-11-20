<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestClient;

class ProjectXmlTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setup()
    {
        $this->client = new TestClient('http://test.local', 'asdf');
    }

    /**
     * @expectedException Exception
     */
    public function testCreateBlank()
    {
        $this->assertInstanceOf('Redmine\Api\Project', $this->client->api('project'));

        $res = $this->client->api('project')->create();
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('project')->create(array(
            'name' => 'some name',
            'identifier' => 'the_identifier',
        ));

        $xml = '<?xml version="1.0"?>
<project>
    <name>some name</name>
    <identifier>the_identifier</identifier>
</project>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testCreateComplexWithTrackerIds()
    {
        $res = $this->client->api('project')->create(array(
            'name' => 'some name',
            'identifier' => 'the_identifier',
            'tracker_ids' => array(
                1, 2, 3,
            ),
        ));

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
        $res = $this->client->api('project')->update(1, array(
            'name' => 'different name',
        ));

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
