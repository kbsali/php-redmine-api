<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
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
                $response,
            ],
        );

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

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
}
