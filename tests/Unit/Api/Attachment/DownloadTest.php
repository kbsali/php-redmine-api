<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Attachment::class)]
class DownloadTest extends TestCase
{
    /**
     * @dataProvider getDownloadData
     *
     * @param mixed $id
     * @param mixed $expectedReturn
     */
    #[DataProvider('getDownloadData')]
    public function testDownloadReturnsCorrectResponse($id, string $expectedPath, int $responseCode, string $response, $expectedReturn): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                '',
                '',
                $responseCode,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->download($id));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getDownloadData(): array
    {
        return [
            'string response with integer id' => [5, '/attachments/download/5', 200, 'attachment-content', 'attachment-content'],
            'string response with string id' => ['5', '/attachments/download/5', 200, 'attachment-content', 'attachment-content'],
            'false response' => [5, '/attachments/download/5', 404, '', false],
        ];
    }

    /**
     * @dataProvider getDownloadWithFutureModeData
     *
     * @param mixed $id
     */
    #[DataProvider('getDownloadWithFutureModeData')]
    public function testDownloadWithFutureModeReturnsCorrectResponse($id, string $expectedPath, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                '',
                '',
                200,
                'application/octet-stream',
                $response,
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);
        FutureMode::enable($api);

        // Perform the tests
        $this->assertSame($response, $api->download($id));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getDownloadWithFutureModeData(): array
    {
        return [
            'string response with integer id' => [5, '/attachments/download/5', 'attachment-content'],
            'string response with string id' => ['5', '/attachments/download/5', 'attachment-content'],
            'empty response' => [5, '/attachments/download/5', ''],
        ];
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testDownloadWithFutureModeAndUnexpectedResponseThrowsException(int $responseCode, string $responseContentType, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/attachments/download/5',
                '',
                '',
                $responseCode,
                $responseContentType,
                $response,
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);
        FutureMode::enable($api);

        // Perform the tests
        try {
            $api->download(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedResponseData(): array
    {
        return [
            'status code 403' => [403, 'application/json', ''],
            'status code 404' => [404, 'text/html', ''],
            'status code 500' => [500, 'text/html', 'Internal Server Error'],
        ];
    }
}
