<?php

namespace Redmine\Tests;

use Redmine\CurlClient as Client;

class CurlClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Redmine\CurlClient
     * @test
     */
    public function testGetAndSetCurlOptions()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame(array(), $client->getCurlOptions());
        $this->assertInstanceOf(
            'Redmine\CurlClient',
            $client->setCurlOption(15, 'value')
        );
        $this->assertSame(array(15 => 'value'), $client->getCurlOptions());
    }
}
