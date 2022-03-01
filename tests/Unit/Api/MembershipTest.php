<?php

namespace Redmine\Tests\Unit\Api;

use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;

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
     * @test
     */
    public function testAllReturnsClientGetResponseWithProject()
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/memberships.json')
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
        $this->assertSame($expectedReturn, $api->all(5));
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
     * Test create().
     *
     * @covers ::create
     *
     * @test
     */
    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `user_id`, `role_ids`');

        // Perform the tests
        $this->assertSame($response, $api->create(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     *
     * @test
     */
    public function testCreateThrowsExceptionIfRoleIdsAreMissingInParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `user_id`, `role_ids`');

        // Perform the tests
        $this->assertSame($response, $api->create(5, ['user_id' => 4]));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateCallsPost()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'user_id' => 1,
            'role_ids' => 1,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/memberships'),
                    $this->stringEndsWith('.xml')
                ),
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<membership>'),
                    $this->stringEndsWith('</membership>'."\n")
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->create(5, $parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::buildXML
     * @test
     */
    public function testCreateBuildsXml()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'user_id' => 10,
            'role_ids' => [5, 6],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/memberships'),
                    $this->stringEndsWith('.xml')
                ),
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<membership>'),
                    $this->stringEndsWith('</membership>'."\n"),
                    $this->stringContains('<role_ids type="array">'),
                    $this->stringContains('<role_id>5</role_id>'),
                    $this->stringContains('<role_id>6</role_id>'),
                    $this->stringContains('</role_ids>'),
                    $this->stringContains('<user_id>10</user_id>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->create(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     *
     * @test
     */
    public function testUpdateThrowsExceptionIfRoleIdsAreMissingInParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing mandatory parameters');

        // Perform the tests
        $this->assertSame($response, $api->update(5, ['user_id' => 4]));
    }

    /**
     * Test update().
     *
     * @covers ::put
     * @covers ::update
     * @test
     */
    public function testUpdateCallsPut()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'role_ids' => 1,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
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
        $this->assertSame($response, $api->update(5, $parameters));
    }
}
