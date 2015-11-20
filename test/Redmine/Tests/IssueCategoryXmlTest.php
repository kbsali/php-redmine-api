<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestClient;

class IssueCategoryXmlTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('Redmine\Api\IssueCategory', $this->client->api('issue_category'));

        $res = $this->client->api('issue_category')->create('aProject');
    }

    public function testCreateComplex()
    {
        $res = $this->client->api('issue_category')->create('otherProject', array(
            'name' => 'test category',
        ));

        $xml = '<?xml version="1.0"?>
<issue_category>
    <name>test category</name>
</issue_category>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $res = $this->client->api('issue_category')->update(1, array(
            'name' => 'new category name',
        ));

        $xml = '<?xml version="1.0"?>
<issue_category>
    <name>new category name</name>
</issue_category>';
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
