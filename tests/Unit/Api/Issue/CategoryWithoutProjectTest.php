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
class CategoryWithoutProjectTest extends TestCase
{
    public function testUpdateWithFutureModeResolvesCategoryWithProjectOfIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 200, 'application/json', '{"issue":{"id":5,"project":{"id":3,"name":"Project Name"}}}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeUsesProjectIdOfTheCallWithoutLoadingTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/projects/1/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><project_id>1</project_id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['project_id' => 1, 'category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeUsesProjectNameOfTheCallWithoutLoadingTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/projects.json?limit=100&offset=0', 'application/json', '', 200, 'application/json', '{"projects":[{"name":"Project Name","id":3}]}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><project_id>3</project_id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['project' => 'Project Name', 'category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeThrowsOnUnknownCategoryOfProjectOfIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 200, 'application/json', '{"issue":{"id":5,"project":{"id":3,"name":"Project Name"}}}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The value "Missing" of the parameter "category" is unknown');

        $api->update(5, ['category' => 'Missing']);
    }

    public function testUpdateWithFutureModeThrowsIfTheIssueCannotBeLoaded(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 404, 'application/json', '{"errors":["Not found"]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(UnexpectedResponseException::class);

        $api->update(5, ['category' => 'Category Name']);
    }

    public function testUpdateWithoutFutureModeDoesNotLoadTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><category>Category Name</category></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(5, ['category' => 'Category Name']));
    }
}
