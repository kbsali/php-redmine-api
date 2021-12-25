<?php

namespace Redmine\Tests\Integration;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;
use SimpleXMLElement;

class GroupXmlTest extends TestCase
{
    /**
     * @var MockClient
     */
    private $client;

    public function setup(): void
    {
        $this->client = new MockClient('http://test.local', 'asdf');
    }

    public function testCreateBlank()
    {
        $api = $this->client->getApi('group');
        $this->assertInstanceOf('Redmine\Api\Group', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        $api->create();
    }

    public function testCreateComplex()
    {
        $res = $this->client->getApi('group')->create([
            'name' => 'Developers',
            'user_ids' => [3, 5],
        ]);
        $res = json_decode($res, true);

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

    public function testUpdateNotImplemented()
    {
        $api = $this->client->getApi('group');
        $this->assertInstanceOf('Redmine\Api\Group', $api);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not implemented');

        $api->update(1);
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
