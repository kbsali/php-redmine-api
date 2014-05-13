<?php

namespace Redmine\Tests;

use Redmine\TestClient;

class WikiXmlTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Redmine\Api\Wiki', $this->client->api('wiki'));
        $res = $this->client->api('wiki')->create();
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('wiki')->create('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));

        $xml = '<?xml version="1.0"?>
<wiki_page>
    <text>asdf</text>
    <comments>asdf</comments>
    <version>asdf</version>
</wiki_page>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res));
    }

    public function testUpdate()
    {
        $res = $this->client->api('wiki')->update('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));

        $xml = '<?xml version="1.0"?>
<wiki_page>
    <text>asdf</text>
    <comments>asdf</comments>
    <version>asdf</version>
</wiki_page>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res));
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
