<?php

namespace Redmine\Tests;

use Redmine\TestClient;

class TestClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Redmine\TestClient
     * @test
     * @expectedException Exception
     */
    public function testGetNotSupportedByTest()
    {
        $client = new TestClient('http://test.local', 'asdf');
        $client->get('do_not_exist');
    }

    /**
     * @covers Redmine\TestClient
     * @test
     * @expectedException Exception
     */
    public function testDeleteNotSupportedByTest()
    {
        $client = new TestClient('http://test.local', 'asdf');
        $client->delete('do_not_exist');
    }

    /**
     * @covers Redmine\TestClient
     * @test
     */
    public function testPostReturnsData()
    {
        // Test values
        $returnData = 'Simple Return Value';

        $client = new TestClient('http://test.local', 'asdf');

        // Perform tests
        $this->assertSame($returnData, $client->post('do_not_exist', $returnData));
    }

    /**
     * @covers Redmine\TestClient
     * @test
     */
    public function testPutReturnsData()
    {
        // Test values
        $returnData = 'Simple Return Value';

        $client = new TestClient('http://test.local', 'asdf');

        // Perform tests
        $this->assertSame($returnData, $client->put('do_not_exist', $returnData));
    }
}
