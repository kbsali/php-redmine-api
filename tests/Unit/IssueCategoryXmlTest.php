<?php

namespace Redmine\Tests\Unit;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient as TestClient;
use SimpleXMLElement;

class IssueCategoryXmlTest extends TestCase
{
    /**
     * @var TestClient
     */
    private $client;

    public function setup(): void
    {
        $this->client = new TestClient('http://test.local', 'asdf');
    }

    public function testCreateBlank()
    {
        $this->expectException(Exception::class);
        $api = $this->client->issue_category;
        $this->assertInstanceOf('Redmine\Api\IssueCategory', $api);

        $api->create('aProject');
    }

    public function testCreateComplex()
    {
        $api = $this->client->issue_category;
        $res = $api->create('otherProject', [
            'name' => 'test category',
        ]);

        $xml = '<?xml version="1.0"?>
<issue_category>
    <name>test category</name>
</issue_category>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $api = $this->client->issue_category;
        $res = $api->update(1, [
            'name' => 'new category name',
        ]);

        $xml = '<?xml version="1.0"?>
<issue_category>
    <name>new category name</name>
</issue_category>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    private function formatXml($xml)
    {
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML((new SimpleXMLElement($xml))->asXML());

        return $dom->saveXML();
    }
}
