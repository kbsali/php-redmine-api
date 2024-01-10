<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Group::create
 */
class CreateTest extends TestCase
{
    public function testCreateWithNameCreatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('POST', $method);
                $this->assertSame('/groups.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><name>Group Name</name></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '<?xml version="1.0"?><group></group>',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $xmlElement = $api->create(['name' => 'Group Name']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><group></group>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithNameAndUserIdsCreatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('POST', $method);
                $this->assertSame('/groups.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><name>Group Name</name><user_ids type="array"><user_id>1</user_id><user_id>2</user_id><user_id>3</user_id></user_ids></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '<?xml version="1.0"?><group></group>',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $xmlElement = $api->create(['name' => 'Group Name', 'user_ids' => [1, 2, 3]]);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><group></group>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithNameAndCustomFieldsCreatesGroup()
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->willReturnCallback(function (string $method, string $path, string $body = '') {
                $this->assertSame('POST', $method);
                $this->assertSame('/groups.xml', $path);
                $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?><group><name>Group Name</name><custom_fields type="array"><custom_field id="1"><value>5</value></custom_field></custom_fields></group>', $body);

                return $this->createConfiguredMock(
                    Response::class,
                    [
                        'getContentType' => 'application/xml',
                        'getBody' => '<?xml version="1.0"?><group></group>',
                    ]
                );
            });

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $xmlElement = $api->create([
            'name' => 'Group Name',
            'custom_fields' => [
                ['id' => 1, 'value' => 5],
            ],
        ]);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><group></group>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateThrowsExceptionIfNameIsMissing()
    {
        // Test values
        $postParameter = [];

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Group($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        // Perform the tests
        $api->create($postParameter);
    }
}
