<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Membership::class)]
class RemoveMemberTest extends TestCase
{
    /**
     * @dataProvider getRemoveMemberData
     */
    #[DataProvider('getRemoveMemberData')]
    public function testRemoveMemberReturnsCorrectResponse($projectIdentifier, $userId, array $params, $expectedPath, $responseCode, $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/' . $projectIdentifier . '/memberships.json' . (($params !== []) ? '?' . http_build_query($params) : ''),
                'application/json',
                '',
                200,
                'application/json',
                '{"memberships":[{"id":2,"user":{"id":' . $userId . '}}]}',
            ],
            [
                'DELETE',
                $expectedPath,
                'application/xml',
                '',
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->removeMember($projectIdentifier, $userId, $params));
    }

    public static function getRemoveMemberData(): array
    {
        return [
            'test without params' => [
                1,
                5,
                [],
                '/memberships/2.xml',
                204,
                '',
            ],
            'test with params' => [
                1,
                5,
                ['limit' => 100, 'offset' => 0],
                '/memberships/2.xml',
                204,
                '',
            ],
        ];
    }

    public function testRemoveMemberReturnsFalseIfUserIsNotMemberOfProject(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/1/memberships.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"memberships":[{"id":5,"user":{"id":404}}]}',
            ],
        );

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertFalse($api->removeMember(1, 2));
    }

    public function testRemoveMemberReturnsFalseIfMemberlistIsMissing(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/1/memberships.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"error":"this response is invalid"}',
            ],
        );

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertFalse($api->removeMember(1, 2));
    }
}
