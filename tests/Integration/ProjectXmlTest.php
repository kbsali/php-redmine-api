<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class ProjectXmlTest extends TestCase
{
    public function testUpdate()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
        $res = $api->update(1, [
            'name' => 'different name',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/projects/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <project>
                <id>1</id>
                <name>different name</name>
            </project>
            XML,
            $response['data']
        );
    }
}
