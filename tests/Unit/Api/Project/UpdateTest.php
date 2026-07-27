<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getUpdateData(): array
    {
        return [
            'test with title' => [
                1,
                ['name' => 'Test Project'],
                '/projects/1.xml',
                '<?xml version="1.0"?><project><id>1</id><name>Test Project</name></project>',
                204,
                '',
            ],
        ];
    }

    public function testUpdateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/1.xml',
                'application/xml',
                '<?xml version="1.0"?><project><id>1</id></project>',
                500,
                '',
                '',
            ],
        );

        $api = Project::fromHttpClient($client);

        $this->assertSame('', $api->update(1, []));
    }

    public function testUpdateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/projects/1.xml',
                'application/xml',
                '<?xml version="1.0"?><project><id>1</id></project>',
                500,
                '',
                '',
            ],
        );

        $api = Project::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->update(1, []);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}
