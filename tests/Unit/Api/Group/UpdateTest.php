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
    /**
     * @dataProvider getUpdateData
     */
    public function testUpdateReturnsCorrectResponse($id, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response
            ]
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test with name' => [
                1,
                ['name' => 'Group Name'],
                '/groups/1.xml',
                '<?xml version="1.0"?><group><name>Group Name</name></group>',
                204,
                '',
            ],
            'test with user ids' => [
                1,
                ['user_ids' => [1, 2, 3]],
                '/groups/1.xml',
                '<?xml version="1.0"?><group><user_ids type="array"><user_id>1</user_id><user_id>2</user_id><user_id>3</user_id></user_ids></group>',
                204,
                '',
            ],
            'test with name and user ids' => [
                1,
                ['name' => 'Developers', 'user_ids' => [3, 5]],
                '/groups/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <group>
                    <name>Developers</name>
                    <user_ids type="array">
                        <user_id>3</user_id>
                        <user_id>5</user_id>
                    </user_ids>
                </group>
                XML,
                204,
                '',
            ],
            'test with custom fields' => [
                1,
                ['custom_fields' => [['id' => 1, 'value' => 5]]],
                '/groups/1.xml',
                '<?xml version="1.0"?><group><custom_fields type="array"><custom_field id="1"><value>5</value></custom_field></custom_fields></group>',
                204,
                '',
            ],
        ];
    }
}
