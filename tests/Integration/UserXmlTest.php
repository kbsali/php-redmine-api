<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class UserXmlTest extends TestCase
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
        $api = $this->client->getApi('user');
        $this->assertInstanceOf('Redmine\Api\User', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `login`, `lastname`, `firstname`, `mail`');

        $api->create();
    }

    public function testCreateComplex()
    {
        $api = $this->client->getApi('user');
        $res = $api->create([
            'login' => 'test',
            'firstname' => 'test',
            'lastname' => 'test',
            'mail' => 'test@example.com',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('POST', $response['method']);
        $this->assertEquals('/users.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <user>
                <login>test</login>
                <lastname>test</lastname>
                <firstname>test</firstname>
                <mail>test@example.com</mail>
            </user>
            XML,
            $response['data']
        );
    }

    public function testUpdate()
    {
        $api = $this->client->getApi('user');
        $res = $api->update(1, [
            'firstname' => 'Raul',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/users/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <user>
                <id>1</id>
                <firstname>Raul</firstname>
            </user>
            XML,
            $response['data']
        );
    }
}
