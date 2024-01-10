<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Group::update
 */
class UpdateTest extends TestCase
{
    public function testUpdateWithNameUpdatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/groups/1.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><name>Group Name</name></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->update(1, ['name' => 'Group Name']);

        $this->assertSame('', $return);
    }

    public function testUpdateWithUserIdsUpdatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/groups/1.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><user_ids type="array"><user_id>1</user_id><user_id>2</user_id><user_id>3</user_id></user_ids></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->update(1, ['user_ids' => [1, 2, 3]]);

        $this->assertSame('', $return);
    }

    public function testUpdateWithCustomFieldsUpdatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('PUT', $method);
                $this->assertSame('/groups/1.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><custom_fields type="array"><custom_field id="1"><value>5</value></custom_field></custom_fields></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->update(1, [
            'custom_fields' => [
                ['id' => 1, 'value' => 5],
            ],
        ]);

        $this->assertSame('', $return);
    }
}
