<?php

namespace Redmine\Tests\Unit\Api;

use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @coversDefaultClass \Redmine\Api\Membership
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class MembershipTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Membership(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Membership::all()` is deprecated since v2.4.0, use `Redmine\Api\Membership::listByProject()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->all(5);
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @dataProvider getAllData
     * @test
     */
    public function testAllReturnsClientGetResponseWithProject($response, $responseType, $expectedResponse)
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/projects/5/memberships.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all(5));
    }

    public static function getAllData(): array
    {
        return [
            'array response' => ['["API Response"]', 'application/json', ['API Response']],
            'string response' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'false response' => ['', 'application/json', false],
        ];
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject()
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/memberships.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5, $parameters));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveCallsDelete()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/memberships/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
    }

    /**
     * Test removeMember().
     *
     * @covers ::removeMember
     * @test
     */
    public function testRemoveMemberCallsDelete()
    {
        if (version_compare(\PHPUnit\Runner\Version::id(), '10.0.0', '<')) {
            $this->markTestSkipped('This test only runs with PHPUnit 10');
        }

        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringContains('/projects/1/memberships.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with($this->stringContains('/memberships/5.xml'))
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');
        $matcher = $this->exactly(2);
        $client->expects($matcher)
            ->method('getLastResponseBody')
            ->willReturnCallback(function () use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    return '{"memberships":[{"id":5,"user":{"id":2}}]}';
                }
                if ($matcher->numberOfInvocations() === 2) {
                    return $response;
                }
            });

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->removeMember(1, 2));
    }

    /**
     * Test removeMember().
     *
     * @covers ::removeMember
     * @test
     */
    public function testRemoveMemberReturnsFalseIfUserIsNotMemberOfProject()
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringContains('/projects/1/memberships.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseContentType')
            ->willReturn('application/json');
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn('{"memberships":[{"id":5,"user":{"id":404}}]}');

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertFalse($api->removeMember(1, 2));
    }

    /**
     * Test removeMember().
     *
     * @covers ::removeMember
     * @test
     */
    public function testRemoveMemberReturnsFalseIfMemberlistIsMissing()
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringContains('/projects/1/memberships.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseContentType')
            ->willReturn('application/json');
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn('{"error":"this response is invalid"}');

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertFalse($api->removeMember(1, 2));
    }
}
