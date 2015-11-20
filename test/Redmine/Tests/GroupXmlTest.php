<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestClient;

class GroupXmlTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Redmine\Api\group', $this->client->api('group'));

        $res = $this->client->api('group')->create();
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('group')->create(array(
            'name' => 'Developers',
            'user_ids' => array(3, 5),
        ));

        $xml = '<?xml version="1.0"?>
<group>
    <name>Developers</name>
    <user_ids>
        <user_id>3</user_id>
        <user_id>5</user_id>
    </user_ids>
</group>
';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    /**
     * @expectedException Exception
     */
    public function testUpdateNotImplemented()
    {
        $this->assertInstanceOf('Redmine\Api\group', $this->client->api('group'));

        $res = $this->client->api('group')->update(1);
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
