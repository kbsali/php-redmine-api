<?php

namespace Redmine\Tests;

use Redmine\TestUrlClient;

class TestUrlClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Redmine\TestUrlClient
     * @test
     */
    public function testGetReturnsPathAndMethod()
    {
        // Test values
        $expectedReturn = array(
            'path' => '/redmine/api/path.json',
            'method' => 'GET',
        );

        // Perform tests
        $client = new TestUrlClient('http://test.local', 'asdf');
        $this->assertSame($expectedReturn, $client->get('/redmine/api/path.json'));
    }

    /**
     * @covers Redmine\TestUrlClient
     * @test
     */
    public function testDeleteReturnsPathAndMethod()
    {
        // Test values
        $expectedReturn = array(
            'path' => '/redmine/api/path.json',
            'method' => 'DELETE',
        );

        // Perform tests
        $client = new TestUrlClient('http://test.local', 'asdf');
        $this->assertSame($expectedReturn, $client->delete('/redmine/api/path.json'));
    }

    /**
     * @covers Redmine\TestUrlClient
     * @test
     */
    public function testPostReturnsPathAndMethod()
    {
        // Test values
        $expectedReturn = array(
            'path' => '/redmine/api/path.json',
            'method' => 'POST',
        );

        // Perform tests
        $client = new TestUrlClient('http://test.local', 'asdf');
        $this->assertSame(
            $expectedReturn,
            $client->post('/redmine/api/path.json', array())
        );
    }

    /**
     * @covers Redmine\TestUrlClient
     * @test
     */
    public function testPutReturnsPathAndMethod()
    {
        // Test values
        $expectedReturn = array(
            'path' => '/redmine/api/path.json',
            'method' => 'PUT',
        );

        // Perform tests
        $client = new TestUrlClient('http://test.local', 'asdf');
        $this->assertSame(
            $expectedReturn,
            $client->put('/redmine/api/path.json', array())
        );
    }
}
