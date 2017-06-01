<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Membership;

/**
 * @coversDefaultClass \Redmine\Api\Membership
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class MembershipTest extends \PHPUnit\Framework\TestCase
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/memberships.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5));
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
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/memberships.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5, $parameters));
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/memberships/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionIfRoleIdsAreMissingInParameters()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, ['user_id' => 4]));
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
        $getResponse = 'API Response';
        $parameters = [
            'user_id' => 1,
            'role_ids' => 1,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
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
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, $parameters));
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
        $getResponse = 'API Response';
        $parameters = [
            'user_id' => 10,
            'role_ids' => [5, 6],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
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
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @expectedException \Exception
     * @test
     */
    public function testUpdateThrowsExceptionIfRoleIdsAreMissingInParameters()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, ['user_id' => 4]));
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
        $getResponse = 'API Response';
        $parameters = [
            'role_ids' => 1,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/memberships/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }
}
