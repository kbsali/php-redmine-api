<?php

namespace Redmine\Tests;

use Redmine\TestClient;

class TestClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException Exception
     */
    public function testGetNotSupportedByTest()
    {
        $client = new TestClient('http://test.local', 'asdf');
        $client->get('do_not_exist');
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function testDeleteNotSupportedByTest()
    {
        $client = new TestClient('http://test.local', 'asdf');
        $client->delete('do_not_exist');
    }
}
