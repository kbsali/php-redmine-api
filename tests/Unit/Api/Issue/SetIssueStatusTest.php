<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Issue::class)]
class SetIssueStatusTest extends TestCase
{
    public function testSetIssueStatusReturnsCorrectResponse(): void
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
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->setIssueStatus(5, 'Status Name'));
    }

    public function testSetIssueStatusWithFutureModeThrowsOnFailingStatusLookup(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issue_statuses.json',
                'application/json',
                '',
                500,
                'application/json',
                '{"errors":["Internal error"]}',
            ],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(UnexpectedResponseException::class);

        $api->setIssueStatus(5, 'Status Name');
    }

    public function testSetIssueStatusWithFutureModeThrowsOnUnknownStatus(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issue_statuses.json', 'application/json', '', 200, 'application/json', '{"issue_statuses":[{"name":"Status Name","id":123}]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The value "Missing" of the parameter "status" is unknown');

        $api->setIssueStatus(5, 'Missing');
    }

    public function testSetIssueStatusWithoutFutureModeDropsUnknownStatus(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issue_statuses.json', 'application/json', '', 200, 'application/json', '{"issue_statuses":[{"name":"Status Name","id":123}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->setIssueStatus(5, 'Missing'));
    }
}
