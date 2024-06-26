<?php

namespace Redmine\Tests\Unit\Api\Project;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
class UnarchiveTest extends TestCase
{
    public function testUnarchiveReturnsTrue()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/5/unarchive.xml',
                'application/xml',
                '',
                204,
            ]
        );

        $api = new Project($client);

        $this->assertTrue($api->unarchive(5));
    }

    public function testUnarchiveThrowsUnexpectedResponseException()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/5/unarchive.xml',
                'application/xml',
                '',
                403,
            ]
        );

        $api = new Project($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        $api->unarchive(5);
    }

    public function testUnarchiveWithoutIntOrStringThrowsInvalidArgumentException()
    {
        $client = $this->createMock(HttpClient::class);

        $api = new Project($client);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\Project::unarchive(): Argument #1 ($projectIdentifier) must be of type int or string');

        /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
        $api->unarchive(true);
    }
}
