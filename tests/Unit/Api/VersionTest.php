<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Version;

/**
 * @coversDefaultClass \Redmine\Api\Version
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class VersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/projects/5/versions.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5));
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $parameters = [
            'offset' => 10,
            'limit' => 2,
        ];
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/versions.json'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5, $parameters));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithNumericIdReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/versions/5.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithStringReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/versions/test.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show('test'));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveWithNumericIdCallsDelete()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with('/versions/5.xml')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveWithStringCallsDelete()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with('/versions/test.xml')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove('test'));
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
        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $api->create(5);
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionWithMissingNameInParameters()
    {
        // Test values
        $parameters = [
            'description' => 'Test version description',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $api->create(5, $parameters);
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::validateStatus
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionWithInvalidStatus()
    {
        // Test values
        $parameters = [
            'description' => 'Test version description',
            'status' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $api->create('test', $parameters);
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
            'name' => 'Test version',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects/5/versions.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, $parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @covers ::validateStatus
     * @test
     */
    public function testCreateWithValidStatusCallsPost()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'status' => 'locked',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects/5/versions.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains('<status>locked</status>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::validateStatus
     * @expectedException \Exception
     * @test
     */
    public function testUpdateThrowsExceptionWithInvalidStatus()
    {
        // Test values
        $parameters = [
            'description' => 'Test version description',
            'status' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $api->update('test', $parameters);
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
            'name' => 'Test version',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/versions/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update('test', $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::put
     * @covers ::validateStatus
     * @test
     */
    public function testUpdateWithValidStatusCallsPut()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'status' => 'locked',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/versions/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains('<status>locked</status>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update('test', $parameters));
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingReturnsNameIdArray()
    {
        // Test values
        $getResponse = [
            'versions' => [
                ['id' => 1, 'name' => 'Version 1'],
                ['id' => 5, 'name' => 'Version 5'],
            ],
        ];
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/projects/5/versions.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingReturnsIdNameIfReverseIsFalseArray()
    {
        // Test values
        $getResponse = [
            'versions' => [
                ['id' => 1, 'name' => 'Version 1'],
                ['id' => 5, 'name' => 'Version 5'],
            ],
        ];
        $expectedReturn = [
            1 => 'Version 1',
            5 => 'Version 5',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/projects/5/versions.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, false, false));
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetOnlyTheFirstTime()
    {
        // Test values
        $getResponse = [
            'versions' => [
                ['id' => 1, 'name' => 'Version 1'],
                ['id' => 5, 'name' => 'Version 5'],
            ],
        ];
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/projects/5/versions.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        // Test values
        $getResponse = [
            'versions' => [
                ['id' => 1, 'name' => 'Version 1'],
                ['id' => 5, 'name' => 'Version 5'],
            ],
        ];
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with('/projects/5/versions.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, true));
        $this->assertSame($expectedReturn, $api->listing(5, true));
    }

    /**
     * Test getIdByName().
     *
     * @covers ::getIdByName
     * @test
     */
    public function testGetIdByNameMakesGetRequest()
    {
        // Test values
        $getResponse = [
            'versions' => [
                ['id' => 5, 'name' => 'Version 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/versions.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName(5, 'Version 1'));
        $this->assertSame(5, $api->getIdByName(5, 'Version 5'));
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::create
     * @covers       ::validateSharing
     * @dataProvider validSharingProvider
     * @test
     *
     * @param string $sharingValue
     * @param string $sharingXmlElement
     */
    public function testCreateWithValidSharing($sharingValue, $sharingXmlElement)
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects/test/versions.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains($sharingXmlElement)
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create('test', $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::create
     * @covers       ::validateSharing
     * @dataProvider validEmptySharingProvider
     * @test
     *
     * @param string $sharingValue
     */
    public function testCreateWithValidEmptySharing($sharingValue)
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects/test/versions.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->logicalNot(
                        $this->stringContains('<sharing')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create('test', $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers            ::create
     * @covers            ::validateSharing
     * @dataProvider      invalidSharingProvider
     * @expectedException \Exception
     * @test
     *
     * @param string $sharingValue
     */
    public function testCreateThrowsExceptionWithInvalidSharing($sharingValue)
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::update
     * @covers       ::validateSharing
     * @dataProvider validSharingProvider
     * @test
     *
     * @param string $sharingValue
     * @param string $sharingXmlElement
     */
    public function testUpdateWithValidSharing($sharingValue, $sharingXmlElement)
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/versions/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains($sharingXmlElement)
                )
            )
            ->willReturn($getResponse);

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update('test', $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::update
     * @covers       ::validateSharing
     * @dataProvider validEmptySharingProvider
     * @test
     *
     * @param string $sharingValue
     */
    public function testUpdateWithValidEmptySharing($sharingValue)
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/versions/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<version>'),
                    $this->stringEndsWith('</version>'."\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->logicalNot(
                        $this->stringContains('<sharing')
                    )
                )
            )
            ->willReturn($getResponse);

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update('test', $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers            ::update
     * @covers            ::validateSharing
     * @dataProvider      invalidSharingProvider
     * @expectedException \Exception
     * @test
     *
     * @param string $sharingValue
     */
    public function testUpdateThrowsExceptionWithInvalidSharing($sharingValue)
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $api->update('test', $parameters);
    }

    /**
     * Data provider for valid sharing values.
     *
     * @return array[]
     */
    public function validSharingProvider()
    {
        return [
            ['none', '<sharing>none</sharing>'],
            ['descendants', '<sharing>descendants</sharing>'],
            ['hierarchy', '<sharing>hierarchy</sharing>'],
            ['tree', '<sharing>tree</sharing>'],
            ['system', '<sharing>system</sharing>'],
        ];
    }

    /**
     * Data provider for valid empty sharing values.
     *
     * @return array[]
     */
    public function validEmptySharingProvider()
    {
        return [
            [null],
            [false],
            [''],
        ];
    }

    /**
     * Data provider for invalid sharing values.
     *
     * @return array[]
     */
    public function invalidSharingProvider()
    {
        return [
            ['all'],
            ['invalid'],
        ];
    }
}
