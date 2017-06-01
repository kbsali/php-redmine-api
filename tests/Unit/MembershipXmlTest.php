<?php

namespace Redmine\Tests\Unit;

use Redmine\Tests\Fixtures\MockClient as TestClient;

class MembershipXmlTest extends \PHPUnit\Framework\TestCase
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
        $api = $this->client->membership;
        $this->assertInstanceOf('Redmine\Api\Membership', $api);

        $api->create('aProject');
    }

    public function testCreateComplex()
    {
        $api = $this->client->membership;
        $res = $api->create('otherProject', [
            'user_id' => 1,
            'role_ids' => [1, 2],
        ]);

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
        $api = $this->client->membership;
        $res = $api->update(1, [
            'role_ids' => [1, 2],
        ]);

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
