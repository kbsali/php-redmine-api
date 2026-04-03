<?php

namespace Redmine\Tests\Unit\Api\News;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\News;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(News::class)]
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
                '/projects/5/news.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = News::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject($projectId));
    }

    public function testListByProjectWithParametersReturnsResponse(): void
    {
        // Test values
        $projectId = 5;
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/news.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = News::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject($projectId, $parameters));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     * @param mixed $projectIdentifier
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier): void
    {
        $api = News::fromHttpClient($this->createStub(HttpClient::class));

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\News::listByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listByProject($projectIdentifier);
    }

    public function testListByProjectThrowsException(): void
    {
        $response = '';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/news.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = News::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByProject(5);
    }
}
