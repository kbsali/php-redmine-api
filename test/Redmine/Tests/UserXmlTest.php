<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestClient;

class UserXmlTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Redmine\Api\User', $this->client->api('user'));

        $res = $this->client->api('user')->create();
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('user')->create(array(
            'login' => 'test',
            'firstname' => 'test',
            'lastname' => 'test',
            'mail' => 'test@example.com',
        ));

        $xml = '<?xml version="1.0"?>
<user>
    <login>test</login>
    <lastname>test</lastname>
    <firstname>test</firstname>
    <mail>test@example.com</mail>
</user>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $res = $this->client->api('user')->update(1, array(
            'firstname' => 'Raul',
        ));

        $xml = '<?xml version="1.0"?>
<user>
    <id>1</id>
    <firstname>Raul</firstname>
</user>';
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
