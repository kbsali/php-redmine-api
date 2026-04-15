<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(Wiki::class)]
class ListByProjectTest extends TestCase
{
    public function testListByProjectWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/index.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Wiki::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject(5));
    }

    public function testListByProjectWithParametersReturnsResponse(): void
    {
        // Test values
        $parameters = [
            'offset' => 10,
            'limit' => 2,
        ];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/project-slug/wiki/index.json?limit=2&offset=10',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Wiki::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByProject('project-slug', $parameters));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     *
     * @param mixed $projectIdentifier
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier): void
    {
        $api = Wiki::fromHttpClient($this->createStub(HttpClient::class));

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\Wiki::listByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listByProject($projectIdentifier);
    }

    public function testListByProjectThrowsException(): void
    {
        $response = '';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/index.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Wiki::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByProject(5);
    }
}
