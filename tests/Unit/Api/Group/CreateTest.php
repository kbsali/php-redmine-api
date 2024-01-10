<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;

/**
 * @covers \Redmine\Api\Group::create
 */
class CreateTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreateCallsPost()
    {
        // Test values
        $response = 'API Response';
        $postParameter = [
            'name' => 'Group Name',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                ),
                $this->stringContains('<group><name>Group Name</name></group>')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($response, $api->create($postParameter));
    }

    /**
     * @covers ::create
     */
    public function testCreateThrowsExceptionIfNameIsMissing()
    {
        // Test values
        $postParameter = [];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Group($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        // Perform the tests
        $api->create($postParameter);
    }
}
