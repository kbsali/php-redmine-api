<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Wiki::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     *
     * @param mixed $identifier
     * @param mixed $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($identifier, string $page, ?int $version, string $expectedPath, string $response, $expectedReturn): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Wiki::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($identifier, $page, $version));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getShowData(): array
    {
        return [
            'array response with integer identifier' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string identifier' => [
                'project',
                'page',
                null,
                '/projects/project/wiki/page.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with integer identifier and version' => [
                5,
                'page',
                22,
                '/projects/5/wiki/page/22.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string identifier and version' => [
                'project',
                'page',
                22,
                '/projects/project/wiki/page/22.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with space in page name' => [
                5,
                'about page',
                null,
                '/projects/5/wiki/about%20page.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with plus in page name' => [
                5,
                'about+page',
                null,
                '/projects/5/wiki/about%2Bpage.json?include=attachments',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                'string',
                (PHP_VERSION_ID < 80600) ? 'Error decoding body as JSON: Syntax error' : 'Error decoding body as JSON: Syntax error near location 1:1',
            ],
            'false response' => [
                5,
                'page',
                null,
                '/projects/5/wiki/page.json?include=attachments',
                '',
                false,
            ],
        ];
    }

    public function testShowWithFutureModeReturnsCorrectResponse(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/page.json?include=attachments',
                'application/json',
                '',
                200,
                'application/json',
                '["API Response"]',
            ],
        );

        $api = Wiki::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame(['API Response'], $api->show(5, 'page'));
    }

    public function testShowWithIncorrectStatusCodeReturnsFalse(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/page.json?include=attachments',
                'application/json',
                '',
                404,
                'application/json',
                '',
            ],
        );

        $api = Wiki::fromHttpClient($client);

        $this->assertFalse($api->show(5, 'page'));
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testShowWithUnexpectedResponseThrowsException(int $responseCode, string $responseContentType, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/page.json?include=attachments',
                'application/json',
                '',
                $responseCode,
                $responseContentType,
                $response,
            ],
        );

        $api = Wiki::fromHttpClient($client);

        FutureMode::enable($api);

        try {
            $api->show(5, 'page');
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
            'status code 404 with empty body' => [404, 'application/json', ''],
            'status code 403 with empty body' => [403, 'application/json', ''],
            'status code 500 with HTML body' => [500, 'text/html', '<html><body>Internal Server Error</body></html>'],
            'status code 500 with JSON body' => [500, 'application/json', '["API Response"]'],
            'status code 200 with empty body' => [200, 'application/json', ''],
            'status code 200 with JSON null' => [200, 'application/json', 'null'],
            'status code 200 with JSON integer' => [200, 'application/json', '5'],
            'status code 200 with JSON string' => [200, 'application/json', '"text"'],
        ];
    }

    public function testShowWithInvalidJsonThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/wiki/page.json?include=attachments',
                'application/json',
                '',
                200,
                'application/json',
                'string',
            ],
        );

        $api = Wiki::fromHttpClient($client);

        FutureMode::enable($api);

        try {
            $api->show(5, 'page');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}
