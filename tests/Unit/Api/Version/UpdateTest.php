<?php

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Version::update
 * @covers \Redmine\Api\Version::validateStatus
 * @covers \Redmine\Api\Version::validateSharing
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

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test without parameters' => [
                5,
                [],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with name' => [
                5,
                ['name' => 'Test version'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>Test version</name></version>',
                204,
                '',
            ],
            'test with status open' => [
                5,
                ['status' => 'open'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>open</status></version>',
                204,
                '',
            ],
            'test with status locked' => [
                5,
                ['status' => 'locked'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>locked</status></version>',
                204,
                '',
            ],
            'test with status closed' => [
                5,
                ['status' => 'closed'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><status>closed</status></version>',
                204,
                '',
            ],
            'test with status empty string' => [
                5,
                ['status' => ''],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with status false' => [
                5,
                ['status' => false],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with status null' => [
                5,
                ['status' => null],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing none' => [
                5,
                ['sharing' => 'none'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>none</sharing></version>',
                204,
                '',
            ],
            'test with sharing descendants' => [
                5,
                ['sharing' => 'descendants'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>descendants</sharing></version>',
                204,
                '',
            ],
            'test with sharing hierarchy' => [
                5,
                ['sharing' => 'hierarchy'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>hierarchy</sharing></version>',
                204,
                '',
            ],
            'test with sharing tree' => [
                5,
                ['sharing' => 'tree'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>tree</sharing></version>',
                204,
                '',
            ],
            'test with sharing system' => [
                5,
                ['sharing' => 'system'],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><sharing>system</sharing></version>',
                204,
                '',
            ],
            'test with sharing empty string' => [
                5,
                ['sharing' => ''],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing false' => [
                5,
                ['sharing' => false],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
            'test with sharing null' => [
                5,
                ['sharing' => null],
                '/versions/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
                204,
                '',
            ],
        ];
    }

    public function testUpdateWithInvalidStatusThrowsInvalidParameterException()
    {
        // Test values
        $parameters = [
            'name' => 'test',
            'description' => 'Test version description',
            'status' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for status are: open, locked, closed');

        // Perform the tests
        $api->update(5, $parameters);
    }

    public function testUpdateWithInvalidSharingThrowsInvalidParameterException()
    {
        // Test values
        $parameters = [
            'name' => 'test',
            'description' => 'Test version description',
            'sharing' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing are: none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->update(5, $parameters);
    }
}
