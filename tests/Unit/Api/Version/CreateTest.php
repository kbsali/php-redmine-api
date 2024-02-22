<?php

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Version::create
 */
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreateReturnsCorrectResponse($identifier, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
        $api = new Version($client);

        // Perform the tests
        $return = $api->create($identifier, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with minimal parameters' => [
                5,
                ['name' => 'test'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with status parameter' => [
                5,
                ['name' => 'test', 'status' => 'locked'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><status>locked</status></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with sharing parameter none' => [
                5,
                ['name' => 'test', 'sharing' => 'none'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><sharing>none</sharing></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with sharing parameter descendants' => [
                5,
                ['name' => 'test', 'sharing' => 'descendants'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><sharing>descendants</sharing></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with sharing parameter hierarchy' => [
                5,
                ['name' => 'test', 'sharing' => 'hierarchy'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><sharing>hierarchy</sharing></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with sharing parameter tree' => [
                5,
                ['name' => 'test', 'sharing' => 'tree'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><sharing>tree</sharing></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with sharing parameter system' => [
                5,
                ['name' => 'test', 'sharing' => 'system'],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name><sharing>system</sharing></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with empty sharing parameter null' => [
                5,
                ['name' => 'test', 'sharing' => null],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with empty sharing parameter false' => [
                5,
                ['name' => 'test', 'sharing' => false],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
            'test with empty sharing parameter empty string' => [
                5,
                ['name' => 'test', 'sharing' => ''],
                '/projects/5/versions.xml',
                '<?xml version="1.0" encoding="UTF-8"?><version><name>test</name></version>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><version></version>',
            ],
        ];
    }

    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        // Perform the tests
        $api->create(5);
    }

    public function testCreateThrowsExceptionWithMissingNameInParameters()
    {
        // Test values
        $parameters = [
            'description' => 'Test version description',
        ];

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        // Perform the tests
        $api->create(5, $parameters);
    }

    public function testCreateThrowsExceptionWithInvalidStatus()
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
        $this->expectExceptionMessage('Possible values for status : open, locked, closed');

        // Perform the tests
        $api->create('test', $parameters);
    }
}
