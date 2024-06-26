<?php

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Attachment::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse($id, array $params, $expectedPath, $expectedContent, $expectedReturn)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/json',
                $expectedContent,
                204,
                '',
                '',
            ]
        );

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->update($id, $params));
    }

    public static function getUpdateData(): array
    {
        return [
            'test with all params' => [
                5,
                [
                    'filename' => 'renamed.zip',
                    'description' => 'updated',
                ],
                '/attachments/5.json',
                '{"attachment":{"filename":"renamed.zip","description":"updated"}}',
                true,
            ],
        ];
    }

    public function testUpdateThrowsUnexpectedResponseException()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/attachments/5.json',
                'application/json',
                '{"attachment":[]}',
                403,
                '',
                '',
            ]
        );

        $api = new Attachment($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        $api->update(5, []);
    }
}
