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
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedContent, bool $expectedReturn): void
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
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->update($id, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
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

    public function testUpdateThrowsUnexpectedResponseException(): void
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
            ],
        );

        $api = Attachment::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        $api->update(5, []);
    }
}
