<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient;

class GroupXmlTest extends TestCase
{
    public function testUpdateComplex()
    {
        /** @var \Redmine\Api\Group */
        $api = MockClient::create()->getApi('group');
        $res = $api->update(5, [
            'name' => 'Developers',
            'user_ids' => [3, 5],
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/groups/5.xml', $response['path']);
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
}
