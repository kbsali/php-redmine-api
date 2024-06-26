<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
class SetIssueStatusTest extends TestCase
{
    public function testSetIssueStatusReturnsCorrectResponse()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issue_statuses.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_statuses":[{"name":"Status Name","id":123}]}',
            ],
            [
                'PUT',
                '/issues/5.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>5</id><status_id>123</status_id></issue>',
                204,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame('', $api->setIssueStatus(5, 'Status Name'));
    }
}
