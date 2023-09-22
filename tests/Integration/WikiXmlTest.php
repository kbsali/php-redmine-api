<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient;

class WikiXmlTest extends TestCase
{
    public function testCreateComplex()
    {
        /** @var \Redmine\Api\Wiki */
        $api = MockClient::create()->getApi('wiki');
        $res = $api->create('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/projects/testProject/wiki/about.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <wiki_page>
                <text>asdf</text>
                <comments>asdf</comments>
                <version>asdf</version>
            </wiki_page>
            XML,
            $response['data']
        );
    }

    public function testUpdate()
    {
        /** @var \Redmine\Api\Wiki */
        $api = MockClient::create()->getApi('wiki');
        $res = $api->update('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/projects/testProject/wiki/about.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <wiki_page>
                <text>asdf</text>
                <comments>asdf</comments>
                <version>asdf</version>
            </wiki_page>
            XML,
            $response['data']
        );
    }
}
