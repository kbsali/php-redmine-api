<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient;

class IssueXmlTest extends TestCase
{
    public function testUpdateIssue()
    {
        /** @var \Redmine\Api\Issue */
        $api = MockClient::create()->getApi('issue');
        $res = $api->update(1, [
            'subject' => 'test note (xml) 1',
            'notes' => 'test note api',
            'assigned_to_id' => 1,
            'status_id' => 2,
            'priority_id' => 5,
            'due_date' => '2014-05-13',

            // not testable because this will trigger a status name to id resolving
            // 'status' => 'Resolved',
        ]);
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/issues/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <issue>
                <id>1</id>
                <subject>test note (xml) 1</subject>
                <notes>test note api</notes>
                <priority_id>5</priority_id>
                <status_id>2</status_id>
                <assigned_to_id>1</assigned_to_id>
                <due_date>2014-05-13</due_date>
            </issue>
            XML,
            $response['data']
        );
    }

    public function testAddNoteToIssue()
    {
        /** @var \Redmine\Api\Issue */
        $api = MockClient::create()->getApi('issue');
        $res = $api->addNoteToIssue(1, 'some comment');
        $response = json_decode($res, true);

        $this->assertEquals('PUT', $response['method']);
        $this->assertEquals('/issues/1.xml', $response['path']);
        $this->assertXmlStringEqualsXmlString(
            <<< XML
            <?xml version="1.0"?>
            <issue>
                <id>1</id>
                <notes>some comment</notes>
            </issue>
            XML,
            $response['data']
        );
    }
}
