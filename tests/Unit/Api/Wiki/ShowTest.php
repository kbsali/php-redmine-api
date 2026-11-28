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

#[CoversClass(Wiki::class)]
final class ShowTest extends TestCase
{
    /**
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse(int|string $identifier, string $page, ?int $version, string $expectedPath, string $response, array $expectedReturn): void
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
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getShowData(): \Iterator
    {
        yield 'array response with integer identifier' => [
            5,
            'page',
            null,
            '/projects/5/wiki/page.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
        yield 'array response with string identifier' => [
            'project',
            'page',
            null,
            '/projects/project/wiki/page.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
        yield 'array response with integer identifier and version' => [
            5,
            'page',
            22,
            '/projects/5/wiki/page/22.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
        yield 'array response with string identifier and version' => [
            'project',
            'page',
            22,
            '/projects/project/wiki/page/22.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
        yield 'array response with space in page name' => [
            5,
            'about page',
            null,
            '/projects/5/wiki/about%20page.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
        yield 'array response with plus in page name' => [
            5,
            'about+page',
            null,
            '/projects/5/wiki/about%2Bpage.json?include=attachments',
            '["API Response"]',
            ['API Response'],
        ];
    }

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


        try {
            $api->show(5, 'page');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getUnexpectedResponseData(): \Iterator
    {
        yield 'status code 404 with empty body' => [404, 'application/json', ''];
        yield 'status code 403 with empty body' => [403, 'application/json', ''];
        yield 'status code 500 with HTML body' => [500, 'text/html', '<html><body>Internal Server Error</body></html>'];
        yield 'status code 500 with JSON body' => [500, 'application/json', '["API Response"]'];
        yield 'status code 200 with empty body' => [200, 'application/json', ''];
        yield 'status code 200 with JSON null' => [200, 'application/json', 'null'];
        yield 'status code 200 with JSON integer' => [200, 'application/json', '5'];
        yield 'status code 200 with JSON string' => [200, 'application/json', '"text"'];
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


        try {
            $api->show(5, 'page');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}
