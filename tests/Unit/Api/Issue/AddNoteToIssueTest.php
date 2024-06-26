<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
class AddNoteToIssueTest extends TestCase
{
    /**
     * @dataProvider getAddNoteToIssueData
     */
    #[DataProvider('getAddNoteToIssueData')]
    public function testAddNoteToIssueReturnsCorrectResponse($id, $note, $isPrivate, $expectedPath, $expectedBody, $responseCode, $response)
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
            ]
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame('', $api->addNoteToIssue($id, $note, $isPrivate));
    }

    public static function getAddNoteToIssueData(): array
    {
        return [
            'test with public note' => [
                1,
                'public note',
                false,
                '/issues/1.xml',
                '<?xml version="1.0"?><issue><id>1</id><notes>public note</notes></issue>',
                204,
                '',
            ],
            'test with private note' => [
                1,
                'some private comment',
                true,
                '/issues/1.xml',
                '<?xml version="1.0"?><issue><id>1</id><notes>some private comment</notes><private_notes>1</private_notes></issue>',
                204,
                '',
            ],
        ];
    }
}
