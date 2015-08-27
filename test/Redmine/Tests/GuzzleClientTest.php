<?php

namespace Redmine\Tests;

use Redmine\GuzzleClient;

class GuzzleClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Redmine\GuzzleClient
     * @test
     */
    public function testDecode()
    {
        if (!class_exists('GuzzleClient')) {
            $this->markTestSkipped('GuzzleClient tests skipped because Guzzle is not installed');
        }

        $client = new GuzzleClient('http://redmine.local', 'asdf123');
        $this->assertEquals('{"foo_bar": 12345}', $client->decode('{"foo_bar": 12345}'));
    }

    /**
     * @covers Redmine\GuzzleClient
     * @test
     */
    public function testGetClient()
    {
        if (!class_exists('GuzzleClient')) {
            $this->markTestSkipped('GuzzleClient tests skipped because Guzzle is not installed');
        }

        $client = new GuzzleClient('http://redmine.local', 'asdf123');
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getClient());
        $client->setClient(null);
        $this->assertNull($client->getClient());
    }
}