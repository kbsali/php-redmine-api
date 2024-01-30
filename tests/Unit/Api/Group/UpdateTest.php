<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Group::update
 */
class UpdateTest extends TestCase
{
    public function testUpdateWithNameUpdatesGroup()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/groups/1.xml',
                'application/xml',
                '<?xml version="1.0"?><group><name>Group Name</name></group>',
                200,
                'application/xml',
                ''
            ]
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->update(1, ['name' => 'Group Name']);

        $this->assertSame('', $return);
    }

    public function testUpdateWithUserIdsUpdatesGroup()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/groups/1.xml',
                'application/xml',
                '<?xml version="1.0"?><group><user_ids type="array"><user_id>1</user_id><user_id>2</user_id><user_id>3</user_id></user_ids></group>',
                200,
                'application/xml',
                ''
            ]
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->update(1, ['user_ids' => [1, 2, 3]]);

        $this->assertSame('', $return);
    }

    public function testUpdateWithCustomFieldsUpdatesGroup()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/groups/1.xml',
                'application/xml',
                '<?xml version="1.0"?><group><custom_fields type="array"><custom_field id="1"><value>5</value></custom_field></custom_fields></group>',
                200,
                'application/xml',
                ''
            ]
        );

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
