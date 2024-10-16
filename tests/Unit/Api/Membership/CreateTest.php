<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Membership::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($identifier, $parameters, $expectedPath, $expectedBody, $responseCode, $response): void
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
        $api = new Membership($client);

        // Perform the tests
        $return = $api->create($identifier, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test with one role_id' => [
                5,
                ['user_id' => 4, 'role_ids' => [2]],
                '/projects/5/memberships.xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids type="array"><role_id>2</role_id></role_ids></membership>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><membership></membership>',
            ],
            'test with multiple role_ids' => [
                5,
                ['user_id' => 4, 'role_ids' => [5, 6]],
                '/projects/5/memberships.xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids type="array"><role_id>5</role_id><role_id>6</role_id></role_ids></membership>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><membership></membership>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/projects/5/memberships.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><user_id>4</user_id><role_ids>2</role_ids></membership>',
                500,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $return = $api->create(5, ['user_id' => 4, 'role_ids' => 2]);

        $this->assertSame('', $return);
    }

    public function testCreateThrowsExceptionWithEmptyParameters(): void
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `user_id`, `role_ids`');

        // Perform the tests
        $api->create(5);
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     */
    #[DataProvider('incompleteCreateParameterProvider')]
    public function testCreateThrowsExceptionIfMandatoyParametersAreMissing($parameters): void
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `user_id`, `role_ids`');

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
     */
    public static function incompleteCreateParameterProvider(): array
    {
        return [
            'missing all mandatory parameters' => [
                [],
            ],
            'missing `user_id` parameter' => [
                [
                    'role_ids' => [2],
                ],
            ],
            'missing `role_ids` parameter' => [
                [
                    'user_id' => 4,
                ],
            ],
        ];
    }
}
