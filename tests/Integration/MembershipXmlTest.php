<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient;

class MembershipXmlTest extends TestCase
{
    public function testUpdate()
    {
        /** @var \Redmine\Api\Membership */
        $api = MockClient::create()->getApi('membership');
        $res = $api->update(1, [
            'role_ids' => [1, 2],
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/memberships/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <membership>
                <role_ids type="array">
                    <role_id>1</role_id>
                    <role_id>2</role_id>
                </role_ids>
            </membership>
            XML,
            $response['data']
        );
    }
}
