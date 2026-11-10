<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\InvalidParameterException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;
use SimpleXMLElement;

#[CoversClass(Issue::class)]
class UnknownNameTest extends TestCase
{
    /**
     * @dataProvider getUnknownNames
     *
     * @param array<mixed> $params
     */
    #[DataProvider('getUnknownNames')]
    public function testCreateWithFutureModeThrowsOnUnknownName(array $params, string $lookupPath, string $lookupResponse, string $expectedMessage): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', $lookupPath, 'application/json', '', 200, 'application/json', $lookupResponse],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage($expectedMessage);

        $api->create($params);
    }

    /**
     * @dataProvider getUnknownNames
     *
     * @param array<mixed> $params
     */
    #[DataProvider('getUnknownNames')]
    public function testUpdateWithFutureModeThrowsOnUnknownName(array $params, string $lookupPath, string $lookupResponse, string $expectedMessage): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', $lookupPath, 'application/json', '', 200, 'application/json', $lookupResponse],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage($expectedMessage);

        $api->update(5, $params);
    }

    /**
     * @return array<string,array{array<mixed>,string,string,string}>
     */
    public static function getUnknownNames(): array
    {
        return [
            'project' => [
                ['project' => 'Missing'],
                '/projects.json?limit=100&offset=0',
                '{"projects":[{"name":"Project Name","id":3}]}',
                'The value "Missing" of the parameter "project" is unknown',
            ],
            'category' => [
                ['category' => 'Missing', 'project_id' => 1],
                '/projects/1/issue_categories.json',
                '{"issue_categories":[{"name":"Category Name","id":7}]}',
                'The value "Missing" of the parameter "category" is unknown',
            ],
            'status' => [
                ['status' => 'Missing'],
                '/issue_statuses.json',
                '{"issue_statuses":[{"name":"Status Name","id":123}]}',
                'The value "Missing" of the parameter "status" is unknown',
            ],
            'tracker' => [
                ['tracker' => 'Missing'],
                '/trackers.json',
                '{"trackers":[{"name":"Tracker Name","id":9}]}',
                'The value "Missing" of the parameter "tracker" is unknown',
            ],
            'assigned_to' => [
                ['assigned_to' => 'Missing'],
                '/users.json?limit=100&offset=0',
                '{"users":[{"login":"user_5","id":5}]}',
                'The value "Missing" of the parameter "assigned_to" is unknown',
            ],
            'author' => [
                ['author' => 'Missing'],
                '/users.json?limit=100&offset=0',
                '{"users":[{"login":"user_5","id":5}]}',
                'The value "Missing" of the parameter "author" is unknown',
            ],
        ];
    }

    public function testCreateWithFutureModeReportsUnknownProjectBeforeLookingUpCategory(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/projects.json?limit=100&offset=0', 'application/json', '', 200, 'application/json', '{"projects":[{"name":"Project Name","id":3}]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The value "Missing" of the parameter "project" is unknown');

        $api->create(['project' => 'Missing', 'category' => 'Category Name']);
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

    public function testCreateWithoutFutureModeDropsUnknownName(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/trackers.json', 'application/json', '', 200, 'application/json', '{"trackers":[{"name":"Tracker Name","id":9}]}'],
            ['POST', '/issues.xml', 'application/xml', '<?xml version="1.0"?><issue/>', 201, 'application/xml', '<?xml version="1.0"?><issue><id>5</id></issue>'],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertInstanceOf(SimpleXMLElement::class, $api->create(['tracker' => 'Missing']));
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
