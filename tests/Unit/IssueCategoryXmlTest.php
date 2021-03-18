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
        $api = $this->client->getApi('issue_category');
        $this->assertInstanceOf('Redmine\Api\IssueCategory', $api);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing mandatory parameters');

        $api->create('aProject');
    }

    public function testCreateComplex()
    {
        $api = $this->client->getApi('issue_category');
        $res = $api->create('otherProject', [
            'name' => 'test category',
        ]);
        $res = json_decode($res, true);

        $xml = '<?xml version="1.0"?>
<issue_category>
    <name>test category</name>
</issue_category>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdate()
    {
        $api = $this->client->getApi('issue_category');
        $res = $api->update(1, [
            'name' => 'new category name',
        ]);
        $res = json_decode($res, true);

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
