<?php

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Group::addUser
 */
class AddUserTest extends TestCase
{
    /**
     * @dataProvider getAddUserData
     */
    public function testAddUserReturnsCorrectResponse($groupId, $userId, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                'application/xml',
                $response
            ]
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->addUser($groupId, $userId);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getAddUserData(): array
    {
        return [
            'test with integers' => [
                25,
                5,
                '/groups/25/users.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <user_id>5</user_id>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }

    public function testAddUserReturnsEmptyString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/groups/1/users.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><user_id>2</user_id>',
                500,
                '',
                ''
            ]
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->addUser(1, 2);

        $this->assertSame('', $return);
    }
}
