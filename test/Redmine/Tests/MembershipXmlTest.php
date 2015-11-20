<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestClient;

class MembershipXmlTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Redmine\Api\Membership', $this->client->api('membership'));

        $res = $this->client->api('membership')->create('aProject');
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('membership')->create('otherProject', array(
            'user_id' => 1,
            'role_ids' => array(1, 2),
        ));

        $xml = '<?xml version="1.0"?>
<membership>
    <user_id>1</user_id>
    <role_ids type="array">
        <role_id>1</role_id>
        <role_id>2</role_id>
    </role_ids>
</membership>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $res = $this->client->api('membership')->update(1, array(
            'role_ids' => array(1, 2),
        ));

        $xml = '<?xml version="1.0"?>
<membership>
    <role_ids type="array">
        <role_id>1</role_id>
        <role_id>2</role_id>
    </role_ids>
</membership>';
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
