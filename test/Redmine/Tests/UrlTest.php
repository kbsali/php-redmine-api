<?php

namespace Redmine\Tests;

use Redmine\TestUrlClient;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setup()
    {
        $this->client = new TestUrlClient('http://test.local', 'asdf');
    }

    public function testWiki()
    {
        $res = $this->client->api('wiki')->create('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'PUT'));

        $res = $this->client->api('wiki')->update('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'PUT'));

        $res = $this->client->api('wiki')->show('testProject', 'about');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.json', 'method' => 'GET'));

        $res = $this->client->api('wiki')->show('testProject', 'about', 'v1');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about/v1.json', 'method' => 'GET'));

        $res = $this->client->api('wiki')->remove('testProject', 'about');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'DELETE'));
    }
}
