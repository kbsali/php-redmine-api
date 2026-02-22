<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(IssueCategory::class)]
class ListByProjectTest extends TestCase
{
    public function testListByProjectWithoutParametersReturnsResponse(): void
    {
        // Test values
        $projectId = 5;
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject($projectId));
    }

    public function testListByProjectWithParametersReturnsResponse(): void
    {
        // Test values
        $projectId = 'project-slug';
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/project-slug/issue_categories.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject($projectId, $parameters));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier): void
    {
        $api = IssueCategory::fromHttpClient($this->createStub(HttpClient::class));

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\IssueCategory::listByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listByProject($projectIdentifier);
    }

    public function testListByProjectThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
        );

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByProject(5);
    }
}
