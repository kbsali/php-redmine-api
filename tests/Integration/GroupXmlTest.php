<?php

namespace Redmine\Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class GroupXmlTest extends TestCase
{
    public function testCreateBlank()
    {
        /** @var \Redmine\Api\Group */
        $api = MockClient::create()->getApi('group');
        $this->assertInstanceOf('Redmine\Api\Group', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        $api->create();
    }

    public function testCreateComplex()
    {
        /** @var \Redmine\Api\Group */
        $api = MockClient::create()->getApi('group');
        $res = $api->create([
            'name' => 'Developers',
            'user_ids' => [3, 5],
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('POST', $response['method']);
        $this->assertEquals('/groups.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <group>
                <name>Developers</name>
                <user_ids type="array">
                    <user_id>3</user_id>
                    <user_id>5</user_id>
                </user_ids>
            </group>
            XML,
            $response['data']
        );
    }

    public function testUpdateNotImplemented()
    {
        /** @var \Redmine\Api\Group */
        $api = MockClient::create()->getApi('group');
        $this->assertInstanceOf('Redmine\Api\Group', $api);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not implemented');

        $api->update(1);
    }
}
