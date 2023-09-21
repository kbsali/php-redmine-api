<?php

namespace Redmine\Tests\Integration;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;
use SimpleXMLElement;

class MembershipXmlTest extends TestCase
{
    public function testCreateBlank()
    {
        /** @var \Redmine\Api\Membership */
        $api = MockClient::create()->getApi('membership');
        $this->assertInstanceOf('Redmine\Api\Membership', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `user_id`, `role_ids`');

        $api->create('aProject');
    }

    public function testCreateComplex()
    {
        /** @var \Redmine\Api\Membership */
        $api = MockClient::create()->getApi('membership');
        $res = $api->create('otherProject', [
            'user_id' => 1,
            'role_ids' => [1, 2],
        ]);
        $res = json_decode($res, true);

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
        /** @var \Redmine\Api\Membership */
        $api = MockClient::create()->getApi('membership');
        $res = $api->update(1, [
            'role_ids' => [1, 2],
        ]);
        $res = json_decode($res, true);

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
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML((new SimpleXMLElement($xml))->asXML());

        return $dom->saveXML();
    }
}
