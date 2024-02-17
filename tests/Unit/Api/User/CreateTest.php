<?php

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\User::create
 */
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreateReturnsCorrectResponse($parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
        $api = new User($client);

        // Perform the tests
        $return = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with minimal parameters' => [
                ['login' => 'user', 'lastname' => 'last', 'firstname' => 'first', 'mail' => 'mail@example.com'],
                '/users.xml',
                '<?xml version="1.0" encoding="UTF-8"?><user><login>user</login><lastname>last</lastname><firstname>first</firstname><mail>mail@example.com</mail></user>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><user></user>',
            ],
            'test with all parameters' => [
                [
                    'login' => 'user',
                    'lastname' => 'last',
                    'firstname' => 'first',
                    'mail' => 'mail@example.com',
                    'password' => 'secret',
                    'custom_fields' => [
                        ['id' => 5, 'value' => 'Value 5'],
                        ['id' => 13, 'value' => 'Value 13', 'name' => 'CF Name'],
                    ],
                ],
                '/users.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <user>
                    <login>user</login>
                    <password>secret</password>
                    <lastname>last</lastname>
                    <firstname>first</firstname>
                    <mail>mail@example.com</mail>
                    <custom_fields type="array">
                        <custom_field id="5">
                            <value>Value 5</value>
                        </custom_field>
                        <custom_field id="13" name="CF Name">
                            <value>Value 13</value>
                        </custom_field>
                    </custom_fields>
                </user>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><user></user>',
            ],
        ];
    }

    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new User($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `login`, `lastname`, `firstname`, `mail`');

        // Perform the tests
        $this->assertSame($response, $api->create());
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     */
    public function testCreateThrowsExceptionIfValueIsMissingInParameters($parameters)
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new User($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `login`, `lastname`, `firstname`, `mail`');

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
     */
    public static function incompleteCreateParameterProvider(): array
    {
        return [
            // Missing Login
            [
                [
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'firstname' => 'Firstname',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing last name
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'firstname' => 'Firstname',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing first name
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing email
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'firstname' => 'Firstname',
                ],
            ],
        ];
    }
}
