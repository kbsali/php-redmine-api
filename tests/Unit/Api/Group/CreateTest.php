<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Group::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($parameters, $expectedPath, $expectedBody, $responseCode, $response): void
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
                $response,
            ],
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with minimal parameters' => [
                [
                    'name' => 'Group Name',
                ],
                '/groups.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <group>
                    <name>Group Name</name>
                </group>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><group></group>',
            ],
            'test with user ids' => [
                [
                    'name' => 'Group Name',
                    'user_ids' => [1, 2, 3],
                ],
                '/groups.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <group>
                    <name>Group Name</name>
                    <user_ids type="array">
                        <user_id>1</user_id>
                        <user_id>2</user_id>
                        <user_id>3</user_id>
                    </user_ids>
                </group>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><group></group>',
            ],
            'test with custom fields' => [
                [
                    'name' => 'Group Name',
                    'custom_fields' => [
                        ['id' => 1, 'value' => 5],
                    ],
                ],
                '/groups.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <group>
                    <name>Group Name</name>
                    <custom_fields type="array">
                        <custom_field id="1">
                            <value>5</value>
                        </custom_field>
                    </custom_fields>
                </group>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><group></group>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/groups.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><group><name>Group Name</name></group>',
                500,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $return = $api->create(['name' => 'Group Name']);

        $this->assertSame('', $return);
    }

    public function testCreateThrowsExceptionIfNameIsMissing(): void
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
