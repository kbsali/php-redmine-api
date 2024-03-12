<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Membership::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
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
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test with one role_id' => [
                5,
                ['user_id' => 4, 'role_ids' => [2]],
                '/memberships/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><role_ids type="array"><role_id>2</role_id></role_ids><user_id>4</user_id>
                </membership>',
                204,
                '',
            ],
            'test with multiple role_ids' => [
                5,
                ['user_id' => 4, 'role_ids' => [5, 6]],
                '/memberships/5.xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><role_ids type="array"><role_id>5</role_id><role_id>6</role_id></role_ids><user_id>4</user_id></membership>',
                204,
                '',
            ],
        ];
    }

    public function testUpdateReturnsEmptyString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/memberships/5.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><membership><role_ids>2</role_ids><user_id>4</user_id></membership>',
                500,
                '',
                ''
            ]
        );

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $return = $api->update(5, ['user_id' => 4, 'role_ids' => 2]);

        $this->assertSame('', $return);
    }

    public function testUpdateThrowsExceptionWithEmptyParameters()
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `role_ids`');

        // Perform the tests
        $api->update(5);
    }

    /**
     * @dataProvider incompleteUpdateParameterProvider
     */
    #[DataProvider('incompleteUpdateParameterProvider')]
    public function testUpdateThrowsExceptionIfMandatoyParametersAreMissing($parameters)
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new Membership($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `role_ids`');

        // Perform the tests
        $api->update(5, $parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
     */
    public static function incompleteUpdateParameterProvider(): array
    {
        return [
            'missing all mandatory parameters' => [
                [],
            ],
            'missing `role_ids` parameter' => [
                [
                    'user_id' => 4,
                ],
            ],
        ];
    }
}
