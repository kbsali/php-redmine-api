<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

/**
 * The lookups of literal names in `Issue::create()` and `Issue::update()` use internal API instances.
 * These instances must follow the future mode of the `Issue` instance.
 */
#[CoversClass(Issue::class)]
class FutureModeInternalLookupsTest extends TestCase
{
    /**
     * @param array<mixed> $params
     */
    #[DataProvider('getFailingLookups')]
    public function testCreateWithFutureModeThrowsOnFailingLookup(array $params, string $lookupPath): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $lookupPath,
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

        $api->create($params);
    }

    /**
     * @return array<string,array{array<mixed>,string}>
     */
    public static function getFailingLookups(): array
    {
        return [
            'project' => [['project' => 'Project Name'], '/projects.json?limit=100&offset=0'],
            'category' => [['category' => 'Category Name', 'project_id' => 1], '/projects/1/issue_categories.json'],
            'status' => [['status' => 'Status Name'], '/issue_statuses.json'],
            'tracker' => [['tracker' => 'Tracker Name'], '/trackers.json'],
            'assigned_to' => [['assigned_to' => 'login'], '/users.json?limit=100&offset=0'],
            'author' => [['author' => 'login'], '/users.json?limit=100&offset=0'],
        ];
    }

    public function testFutureModeEnabledAfterFirstUseAppliesToCachedLookupApi(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/1/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_categories":[{"id":7,"name":"Category Name"}]}',
            ],
            [
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><project_id>1</project_id><category_id>7</category_id></issue>',
                201,
                'application/xml',
                '<?xml version="1.0"?><issue><id>5</id></issue>',
            ],
            [
                'GET',
                '/projects/2/issue_categories.json',
                'application/json',
                '',
                500,
                'application/json',
                '{"errors":["Internal error"]}',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $api->create(['category' => 'Category Name', 'project_id' => 1]);

        FutureMode::enable($api);

        $this->expectException(UnexpectedResponseException::class);

        $api->create(['category' => 'Category Name', 'project_id' => 2]);
    }

    public function testCreateWithoutFutureModeIgnoresFailingLookup(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                500,
                'application/json',
                '{"errors":["Internal error"]}',
            ],
            [
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue/>',
                201,
                'application/xml',
                '<?xml version="1.0"?><issue><id>5</id></issue>',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertInstanceOf(\SimpleXMLElement::class, $api->create(['tracker' => 'Tracker Name']));
    }
}
